<?php

namespace App\Http\Controllers;

use App\Domain\Catalog\Models\Attribute;
use App\Domain\Catalog\Models\AttributeValue;
use App\Domain\Catalog\Models\Brand;
use App\Domain\Catalog\Models\Category;
use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Models\ProductVideo;
use App\Domain\Catalog\Services\ProductService;
use App\Domain\Digital\Models\ProductDownloadFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use RuntimeException;
use Throwable;

class AdminProductViewController extends Controller
{
    public function __construct(private readonly ProductService $products) {}

    public function create()
    {
        return view('admin.products.form', ['product' => new Product, 'categories' => Category::query()->orderBy('name')->get(), 'brands' => Brand::query()->where('is_active', true)->orderBy('name')->get(), 'attributes' => Attribute::query()->with('values')->orderBy('name')->get()]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $product = $this->products->create($data, $request->user());
        if (! empty($data['attribute_value_ids'])) {
            $values = AttributeValue::query()
                ->whereIn('id', $data['attribute_value_ids'])
                ->get()
                ->groupBy('attribute_id')
                ->values()
                ->map(fn ($group) => $group->values()->all())
                ->all();

            $combinations = [[]];
            foreach ($values as $attributeValues) {
                $combinations = collect($combinations)->flatMap(
                    fn (array $combination) => collect($attributeValues)->map(
                        fn (AttributeValue $value) => [...$combination, $value->id]
                    )
                )->values()->all();
            }

            foreach ($combinations as $index => $attributeValueIds) {
                $this->products->addVariant($product, [
                    'price' => $product->regular_price,
                    'sale_price' => $product->sale_price,
                    'attribute_value_ids' => $attributeValueIds,
                    'is_default' => $index === 0,
                ], $request->user());
            }
        }
        $files = $request->file('create_images', []);
        foreach ($files as $file) {
            $this->products->addImage(
                $product,
                $file,
                null,
                $request->boolean('create_images_thumbnail'),
                $request->user(),
                $request->input('create_images_alt_text'),
                $request->input('create_images_title'),
            );
        }

        return redirect()->route('admin.catalog')->with('success', 'Product created.');
    }

    public function edit(Request $request, Product $product)
    {
        return view('admin.products.form', [
            'product' => $product->load(['images.variant', 'videos', 'downloadFiles', 'variants.images', 'variants.attributeValues.attribute']),
            'categories' => Category::query()->orderBy('name')->get(),
            'brands' => Brand::query()->where('is_active', true)->orderBy('name')->get(),
            'attributes' => Attribute::query()->with('values')->orderBy('name')->get(),
            'catalogQuery' => $request->only(['q', 'status', 'category_id', 'page']),
        ]);
    }

    public function update(Request $request, Product $product)
    {
        $this->products->update($product, $this->validated($request, $product), $request->user());

        return redirect()->route('admin.catalog', $request->only(['q', 'status', 'category_id', 'page']))->with('success', 'Product updated.');
    }

    public function destroy(Request $request, Product $product)
    {
        abort_unless($request->user()->can('delete', $product), 403);
        $this->products->delete($product, $request->user());

        return redirect()->route('admin.catalog', $request->query())->with('success', 'Product deleted.');
    }

    public function restore(Request $request, int $product)
    {
        $product = Product::withTrashed()->findOrFail($product);
        abort_unless($product->trashed(), 404);
        abort_unless($request->user()->can('restore', $product), 403);

        $this->products->restore($product, $request->user());

        return redirect()->route('admin.catalog', ['archived' => 1])->with('success', 'Product restored.');
    }

    public function addImage(Request $request, Product $product)
    {
        abort_unless($request->user()->can('update', $product), 403);
        $data = $request->validate([
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'images' => ['nullable', 'array', 'min:1'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'product_variant_id' => ['nullable', 'integer', 'exists:product_variants,id'],
            'is_thumbnail' => ['nullable', 'boolean'],
            'alt_text' => ['nullable', 'string', 'max:255'],
            'title' => ['nullable', 'string', 'max:255'],
        ]);
        $variant = $product->variants()->find($data['product_variant_id'] ?? null);
        abort_if(($data['product_variant_id'] ?? null) && ! $variant, 422, 'The selected variant does not belong to this product.');
        $files = $request->file('images', []);
        if ($request->hasFile('image')) {
            $files[] = $request->file('image');
        }
        abort_if($files === [], 422, 'Select at least one image to upload.');
        foreach ($files as $file) {
            $this->products->addImage($product, $file, $variant, $request->boolean('is_thumbnail'), $request->user(), $data['alt_text'] ?? null, $data['title'] ?? null);
        }

        return back()->with('success', count($files).' product image(s) uploaded.');
    }

    public function addVideo(Request $request, Product $product)
    {
        abort_unless($request->user()->can('update', $product), 403);
        $data = $request->validate([
            'video' => ['required', 'file', 'mimetypes:video/mp4,video/webm', 'mimes:mp4,webm', 'max:102400'],
            'title' => ['nullable', 'string', 'max:255'],
        ]);

        $this->products->addVideo($product, $data['video'], $request->user(), $data['title'] ?? null);

        return back()->with('success', 'Product preview video uploaded.');
    }

    public function destroyVideo(Request $request, Product $product, ProductVideo $video)
    {
        abort_unless($request->user()->can('update', $product), 403);
        $this->products->deleteVideo($product, $video, $request->user());

        return back()->with('success', 'Product preview video deleted.');
    }

    public function addDownloadFiles(Request $request, Product $product)
    {
        abort_unless($request->user()->can('update', $product), 403);
        $data = $request->validate([
            'files' => ['required', 'array', 'min:1', 'max:10'],
            'files.*' => ['required', 'file', 'mimes:zip,rar,7z,pdf,mp4,webm,mov,mp3,epub,doc,docx,xls,xlsx,ppt,pptx,txt,psd,ai,jpg,jpeg,png,webp', 'max:1024000'],
        ]);

        $disk = Storage::disk('local');
        $storedPaths = [];

        try {
            DB::transaction(function () use ($data, $product, $request, &$storedPaths): void {
                foreach ($data['files'] as $file) {
                    $path = $file->store('product-downloads/'.$product->id, 'local');
                    if (! $path) {
                        throw new RuntimeException('Unable to store a product download file.');
                    }
                    $storedPaths[] = $path;

                    $product->downloadFiles()->create([
                        'path' => $path,
                        'original_name' => Str::limit(basename($file->getClientOriginalName()), 255, ''),
                        'mime_type' => $file->getMimeType(),
                        'file_size' => (int) $file->getSize(),
                        'created_by' => $request->user()->id,
                    ]);
                }
            });
        } catch (Throwable $exception) {
            foreach ($storedPaths as $path) {
                if ($disk->exists($path) && ! $disk->delete($path)) {
                    throw new RuntimeException("Unable to clean up failed product download upload {$path}.", previous: $exception);
                }
            }

            throw $exception;
        }

        return back()->with('success', count($data['files']).' product download file(s) uploaded.');
    }

    public function destroyDownloadFile(Request $request, Product $product, ProductDownloadFile $file)
    {
        abort_unless($request->user()->can('update', $product), 403);
        abort_unless($file->product_id === $product->id, 404);

        $disk = Storage::disk('local');
        if ($disk->exists($file->path) && ! $disk->delete($file->path)) {
            throw new RuntimeException("Unable to delete product download file {$file->path}.");
        }
        $file->delete();

        return back()->with('success', 'Product download file deleted.');
    }

    public function addVariant(Request $request, Product $product)
    {
        abort_unless($request->user()->can('update', $product), 403);
        $request->merge([
            'attribute_value_ids' => collect($request->input('attribute_value_ids', []))
                ->filter(fn ($value) => filled($value))
                ->values()
                ->all(),
        ]);
        $data = $request->validate([
            'sku' => ['nullable', 'string', 'max:100', 'unique:product_variants,sku'],
            'price' => ['required', 'numeric', 'min:0'],
            'sale_price' => ['nullable', 'numeric', 'min:0'],
            'is_default' => ['nullable', 'boolean'],
            'attribute_value_ids' => ['nullable', 'array'],
            'attribute_value_ids.*' => ['integer', 'exists:attribute_values,id'],
        ]);
        $this->products->addVariant($product, $data, $request->user());

        return back()->with('success', 'Product variant added.');
    }

    public function updateVariant(Request $request, Product $product, \App\Domain\Catalog\Models\ProductVariant $variant)
    {
        abort_unless($request->user()->can('update', $product), 403);
        abort_unless($variant->product_id === $product->id, 404);
        $request->merge([
            'attribute_value_ids' => collect($request->input('attribute_value_ids', []))
                ->filter(fn ($value) => filled($value))
                ->values()
                ->all(),
        ]);
        $data = $request->validate([
            'sku' => ['required', 'string', 'max:100', Rule::unique('product_variants', 'sku')->ignore($variant->id)],
            'barcode' => ['nullable', 'string', 'max:100'],
            'purchase_price' => ['nullable', 'numeric', 'min:0', 'lte:price'],
            'price' => ['required', 'numeric', 'min:0'],
            'sale_price' => ['nullable', 'numeric', 'min:0'],
            'weight' => ['nullable', 'numeric', 'min:0'],
            'is_default' => ['nullable', 'boolean'],
            'attribute_value_ids' => ['nullable', 'array'],
            'attribute_value_ids.*' => ['integer', 'exists:attribute_values,id'],
        ]);
        $this->products->updateVariant($product, $variant, $data, $request->user());

        return back()->with('success', 'Product variant updated.');
    }

    public function destroyVariant(Request $request, Product $product, \App\Domain\Catalog\Models\ProductVariant $variant)
    {
        abort_unless($request->user()->can('update', $product), 403);
        $this->products->deleteVariant($product, $variant, $request->user());

        return back()->with('success', 'Product variant deleted.');
    }

    private function validated(Request $request, ?Product $product = null): array
    {
        $skuRule = 'unique:products,sku'.($product ? ','.$product->id : '');
        $data = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'brand_id' => ['nullable', 'exists:brands,id'],
            'name' => ['required', 'string', 'max:255'],
            'sku' => [$product ? 'sometimes' : 'required', 'string', 'max:100', $skuRule],
            'product_type' => ['required', 'in:simple,variable,digital'],
            'download_url' => ['nullable', 'url:https', 'max:2048'],
            'short_description' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string'],
            'regular_price' => ['required', 'numeric', 'min:0'],
            'purchase_price' => ['nullable', 'numeric', 'min:0', 'lte:regular_price'],
            'entrepreneur_profit_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'entrepreneur_profit_flat_rate' => ['nullable', 'numeric', 'min:0'],
            'sale_price' => ['nullable', 'numeric', 'min:0'],
            'tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'status' => ['required', 'in:draft,pending_review,published,rejected'],
            'featured' => ['nullable', 'boolean'],
            'create_images' => ['nullable', 'array', 'max:12'],
            'create_images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'create_images_alt_text' => ['nullable', 'string', 'max:255'],
            'create_images_title' => ['nullable', 'string', 'max:255'],
            'create_images_thumbnail' => ['nullable', 'boolean'],
            'attribute_value_ids' => ['nullable', 'array'],
            'attribute_value_ids.*' => ['integer', 'exists:attribute_values,id'],
            'slug' => ['nullable', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', 'unique:products,slug'.($product ? ','.$product->id : '')],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:320'],
        ]);
        $data['featured'] = $request->boolean('featured');
        $data['slug'] = filled($data['slug'] ?? null) ? Str::slug($data['slug']) : Str::slug($data['name']);

        return $data;
    }
}
