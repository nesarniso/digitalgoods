<?php

namespace App\Http\Controllers;

use App\Domain\Catalog\Models\ProductImage;
use App\Domain\Catalog\Services\ProductService;
use Illuminate\Http\Request;

class AdminGalleryController extends Controller
{
    public function __construct(private readonly ProductService $products) {}

    public function index()
    {
        return view('admin.gallery', [
            'images' => ProductImage::query()
                ->with(['product', 'variant'])
                ->latest('id')
                ->paginate(24)
                ->through(function (ProductImage $image): ProductImage {
                    $image->setAttribute('editor_url', request()->getSchemeAndHttpHost().'/storage/media/'.$image->path);

                    return $image;
                }),
        ]);
    }

    public function update(Request $request, ProductImage $image)
    {
        $product = $image->product;
        abort_unless($product && $request->user()->can('update', $product), 403);

        $data = $request->validate([
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'alt_text' => ['nullable', 'string', 'max:255'],
            'title' => ['nullable', 'string', 'max:255'],
            'is_thumbnail' => ['nullable', 'boolean'],
        ]);

        $this->products->replaceImage(
            $product,
            $image,
            $data['image'],
            $request->user(),
            $data['alt_text'] ?? null,
            $data['title'] ?? null,
            $request->boolean('is_thumbnail'),
        );

        return back()->with('success', 'Image cropped and updated for '.$product->name.'.');
    }

    public function destroy(Request $request, ProductImage $image)
    {
        $product = $image->product;
        abort_unless($product && $request->user()->can('update', $product), 403);
        $this->products->deleteImage($product, $image, $request->user());

        return back()->with('success', 'Image removed from '.$product->name.'.');
    }
}
