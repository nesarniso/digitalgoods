<?php

namespace App\Http\Controllers;

use App\Domain\Catalog\Models\Category;
use App\Domain\Catalog\Models\Attribute;
use App\Domain\Catalog\Models\AttributeValue;
use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use ZipArchive;

class AdminCatalogController extends Controller
{
    public function __invoke(Request $request)
    {
        $this->authorizeCatalog($request);

        $archived = $request->boolean('archived');
        return view('admin.catalog', [
            'products' => Product::query()
                ->when($archived, fn ($query) => $query->onlyTrashed())
                ->with('category')
                ->when($request->filled('q'), fn ($query) => $query->where('name', 'like', '%'.$request->string('q').'%'))
                ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
                ->when($request->filled('category_id'), fn ($query) => $query->where('category_id', $request->integer('category_id')))
                ->latest('id')
                ->paginate(15)
                ->withQueryString(),
            'categories' => Category::query()->with('parent')->orderBy('name')->get(),
            'archived' => $archived,
        ]);
    }

    public function export(Request $request)
    {
        $this->authorizeCatalog($request);

        $archivePath = tempnam(sys_get_temp_dir(), 'catalog-export-');
        $zip = new ZipArchive;
        abort_unless($zip->open($archivePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true, 500, 'Unable to create catalog export.');

        $manifest = [
            'format' => 'ecommerce-core-catalog',
            'version' => 1,
            'categories' => [],
            'products' => [],
        ];
        $media = Storage::disk('media');

        foreach (Category::query()->with('parent')->orderBy('id')->get() as $category) {
            $categoryData = $category->only([
                'name', 'slug', 'description', 'sort_order', 'is_active',
                'is_featured', 'meta_title', 'meta_description',
            ]);
            foreach (['image_path', 'banner_path', 'icon_path'] as $field) {
                $categoryData[$field] = $this->addMediaToArchive($zip, $media, $category->{$field}, 'categories/'.$category->slug);
            }
            $categoryData['parent_slug'] = $category->parent?->slug;
            $manifest['categories'][] = $categoryData;
        }

        foreach (Product::query()->with(['category', 'variants.attributeValues.attribute'])->get() as $product) {
            $productData = $product->only([
                'name', 'slug', 'sku', 'barcode', 'product_type', 'unit',
                'description', 'short_description', 'purchase_price',
                'regular_price', 'sale_price', 'tax_rate', 'weight', 'length',
                'width', 'height', 'minimum_order_quantity',
                'maximum_order_quantity', 'status', 'featured', 'published_at',
                'meta_title', 'meta_description',
            ]);
            $productData['category_slug'] = $product->category?->slug;
            $productData['variants'] = $product->variants->map(fn ($variant) => [
                'sku' => $variant->sku,
                'barcode' => $variant->barcode,
                'purchase_price' => $variant->purchase_price,
                'price' => $variant->price,
                'sale_price' => $variant->sale_price,
                'weight' => $variant->weight,
                'is_default' => $variant->is_default,
                'attribute_values' => $variant->attributeValues->map(fn ($value) => [
                    'attribute' => $value->attribute?->slug,
                    'value' => $value->value,
                ])->values()->all(),
            ])->values()->all();
            $productData['images'] = ProductImage::query()
                ->where('product_id', $product->id)
                ->with('variant')
                ->get()
                ->map(fn ($image) => [
                    'archive_path' => $this->addMediaToArchive($zip, $media, $image->path, 'products/'.$product->slug),
                    'variant_sku' => $image->variant?->sku,
                    'alt_text' => $image->alt_text,
                    'title' => $image->title,
                    'is_thumbnail' => $image->is_thumbnail,
                    'sort_order' => $image->sort_order,
                ])->filter(fn ($image) => $image['archive_path'] !== null)->values()->all();
            $manifest['products'][] = $productData;
        }

        $zip->addFromString('manifest.json', json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
        $zip->close();

        return response()->download($archivePath, 'catalog-export-'.now()->format('Y-m-d-His').'.zip')->deleteFileAfterSend(true);
    }

    public function import(Request $request)
    {
        abort_unless(
            $request->user()->can('product.create') || $request->user()->hasAnyRole(['super_admin', 'admin']),
            403
        );
        $request->validate([
            'catalog' => ['required', 'file', 'mimes:zip', 'max:512000'],
        ]);

        $zip = new ZipArchive;
        abort_unless($zip->open($request->file('catalog')->getRealPath()) === true, 422, 'Invalid catalog export package.');
        $manifestContents = $zip->getFromName('manifest.json');
        abort_if($manifestContents === false, 422, 'Catalog package is missing manifest.json.');
        $manifest = json_decode($manifestContents, true, 512, JSON_THROW_ON_ERROR);
        abort_unless(($manifest['format'] ?? null) === 'ecommerce-core-catalog', 422, 'Unsupported catalog package.');

        $categories = [];
        foreach ($manifest['categories'] ?? [] as $data) {
            $category = Category::firstOrNew(['slug' => $data['slug']]);
            $category->fill(collect($data)->except(['parent_slug', 'image_path', 'banner_path', 'icon_path'])->all());
            $category->parent_id = null;
            $category->uuid ??= (string) Str::uuid();
            $category->save();
            $categories[$category->slug] = $category;
        }

        foreach ($manifest['categories'] ?? [] as $data) {
            if (! empty($data['parent_slug']) && isset($categories[$data['parent_slug']])) {
                $categories[$data['slug']]->update(['parent_id' => $categories[$data['parent_slug']]->id]);
            }
            foreach (['image_path', 'banner_path', 'icon_path'] as $field) {
                if (! empty($data[$field])) {
                    $categories[$data['slug']]->update([$field => $this->extractMedia($zip, $data[$field], 'categories/'.$data['slug'])]);
                }
            }
        }

        $imported = 0;
        foreach ($manifest['products'] ?? [] as $data) {
            $product = Product::firstOrNew(['slug' => $data['slug']]);
            $product->fill(collect($data)->except(['category_slug', 'variants', 'images'])->all());
            $product->category_id = isset($categories[$data['category_slug'] ?? ''])
                ? $categories[$data['category_slug']]->id
                : null;
            $product->uuid ??= (string) Str::uuid();
            $product->save();
            $variants = [];
            foreach ($data['variants'] ?? [] as $variantData) {
                $variant = $product->variants()->firstOrNew(['sku' => $variantData['sku']]);
                $variant->fill(collect($variantData)->except(['attribute_values'])->all());
                $variant->save();
                $variants[$variant->sku] = $variant;
                $attributeValueIds = [];
                foreach ($variantData['attribute_values'] ?? [] as $attributeData) {
                    if (empty($attributeData['attribute']) || ! isset($attributeData['value'])) {
                        continue;
                    }
                    $attribute = Attribute::firstOrCreate(
                        ['slug' => $attributeData['attribute']],
                        ['name' => Str::headline($attributeData['attribute'])],
                    );
                    $value = AttributeValue::firstOrCreate(
                        ['attribute_id' => $attribute->id, 'value' => $attributeData['value']],
                        ['slug' => Str::slug($attributeData['value'])],
                    );
                    $attributeValueIds[] = $value->id;
                }
                $variant->attributeValues()->sync($attributeValueIds);
            }
            foreach ($data['images'] ?? [] as $imageData) {
                $path = $this->extractMedia($zip, $imageData['archive_path'], 'products/'.$product->id);
                if ($path === null || ProductImage::query()->where('product_id', $product->id)->where('path', $path)->exists()) {
                    continue;
                }
                ProductImage::create([
                    'product_id' => $product->id,
                    'product_variant_id' => isset($variants[$imageData['variant_sku'] ?? ''])
                        ? $variants[$imageData['variant_sku']]->id
                        : null,
                    'path' => $path,
                    'alt_text' => $imageData['alt_text'] ?? null,
                    'title' => $imageData['title'] ?? null,
                    'is_thumbnail' => (bool) ($imageData['is_thumbnail'] ?? false),
                    'sort_order' => (int) ($imageData['sort_order'] ?? 0),
                ]);
            }
            $imported++;
        }

        $zip->close();

        return back()->with('success', "{$imported} products imported successfully.");
    }

    private function authorizeCatalog(Request $request): void
    {
        $user = $request->user();

        abort_unless(
            $user->can('product.view') || $user->hasAnyRole(['super_admin', 'admin']),
            403
        );
    }

    private function addMediaToArchive(ZipArchive $zip, $media, ?string $path, string $directory): ?string
    {
        if (! $path || ! $media->exists($path)) {
            return null;
        }
        $archivePath = 'media/'.$directory.'/'.basename($path);
        $zip->addFromString($archivePath, $media->get($path));

        return $archivePath;
    }

    private function extractMedia(ZipArchive $zip, string $archivePath, string $directory): ?string
    {
        if (! str_starts_with($archivePath, 'media/') || str_contains($archivePath, '..')) {
            return null;
        }

        $contents = $zip->getFromName($archivePath);
        if ($contents === false) {
            return null;
        }

        $fileInfo = new \finfo(FILEINFO_MIME_TYPE);
        $detectedMime = $fileInfo->buffer($contents) ?: null;
        $allowedMimeTypes = [
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/webp',
            'image/bmp',
            'image/x-ms-bmp',
        ];

        if (! is_string($detectedMime) || ! in_array($detectedMime, $allowedMimeTypes, true)) {
            return null;
        }

        $path = $directory.'/'.basename($archivePath);
        Storage::disk('media')->put($path, $contents);

        return $path;
    }
}
