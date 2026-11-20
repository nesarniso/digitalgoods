<?php

namespace App\Http\Controllers;

use App\Domain\Catalog\Models\ProductImage;
use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Models\ProductVideo;
use App\Domain\Catalog\Services\ProductService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AdminGalleryController extends Controller
{
    public function __construct(private readonly ProductService $products) {}

    public function index(Request $request)
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

    public function videos(Request $request)
    {
        $search = trim((string) $request->query('q', ''));

        return view('admin.video-gallery', [
            'videos' => ProductVideo::query()
                ->with(['product' => fn ($query) => $query->withTrashed()])
                ->when($search !== '', function ($query) use ($search): void {
                    $query->where(function ($query) use ($search): void {
                        $query->where('title', 'like', '%'.$search.'%')
                            ->orWhereHas('product', fn ($productQuery) => $productQuery
                                ->withTrashed()
                                ->where('name', 'like', '%'.$search.'%'));
                    });
                })
                ->latest('id')
                ->paginate(24)
                ->withQueryString(),
            'search' => $search,
        ]);
    }

    public function updateVideo(Request $request, ProductVideo $video)
    {
        $product = Product::withTrashed()->findOrFail($video->product_id);
        abort_unless($request->user()->can('update', $product), 403);

        $data = $request->validate([
            'video' => ['nullable', 'file', 'mimetypes:video/mp4,video/webm', 'mimes:mp4,webm', 'max:102400'],
            'title' => ['nullable', 'string', 'max:255'],
        ]);

        $this->products->replaceVideo($product, $video, $data['video'] ?? null, $request->user(), $data['title'] ?? null);

        return back()->with('success', 'Product video updated.');
    }

    public function destroyVideo(Request $request, ProductVideo $video)
    {
        $product = Product::withTrashed()->findOrFail($video->product_id);
        abort_unless($request->user()->can('update', $product), 403);
        $this->products->deleteVideo($product, $video, $request->user());

        return back()->with('success', 'Product video deleted.');
    }

    public function downloadVideo(Request $request, ProductVideo $video)
    {
        $product = Product::withTrashed()->findOrFail($video->product_id);
        abort_unless($request->user()->can('update', $product), 403);
        abort_unless(Storage::disk('media')->exists($video->path), 404);

        $extension = strtolower(pathinfo($video->path, PATHINFO_EXTENSION));
        $filename = Str::slug($video->title ?: 'product-video').'.'.$extension;

        return Storage::disk('media')->download($video->path, $filename, [
            'Content-Type' => $video->mime_type,
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
