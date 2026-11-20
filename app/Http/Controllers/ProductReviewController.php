<?php

namespace App\Http\Controllers;

use App\Domain\Audit\Services\AuditLogger;
use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Models\ProductReview;
use App\Http\Requests\Storefront\StoreProductReviewRequest;
use App\Notifications\CommerceNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProductReviewController extends Controller
{
    public function store(StoreProductReviewRequest $request, Product $product)
    {
        $review = DB::transaction(function () use ($request, $product): ProductReview {
            $order = $request->user()->orders()
                ->whereKey($request->integer('order_id'))
                ->where('status', 'delivered')
                ->whereHas('items', fn ($query) => $query->where('product_id', $product->id))
                ->lockForUpdate()
                ->first();

            if (! $order) {
                throw ValidationException::withMessages([
                    'order_id' => ['Choose a delivered order that includes this product.'],
                ]);
            }

            if (ProductReview::query()
                ->where('product_id', $product->id)
                ->where('user_id', $request->user()->id)
                ->where('order_id', $order->id)
                ->exists()) {
                throw ValidationException::withMessages([
                    'review' => ['You have already reviewed this purchase.'],
                ]);
            }

            return ProductReview::create([
                ...$request->validated(),
                'product_id' => $product->id,
                'user_id' => $request->user()->id,
                'order_id' => $order->id,
                'status' => 'pending',
                'verified_purchase' => true,
            ]);
        });
        app(AuditLogger::class)->log($request->user(), 'created', 'Reviews', 'ProductReview', (string) $review->id);
        $request->user()->notify(new CommerceNotification(
            'Review submitted',
            'Your review is awaiting moderation.',
            route('products.show', $product->slug),
            'review_submitted',
            ['customer_name' => $request->user()->name, 'site_name' => app(\App\Support\SiteBrand::class)->name()],
        ));

        return back()->with('success', 'Review submitted for moderation.');
    }

    public function moderation()
    {
        return view('admin.modules.reviews', [
            'reviews' => ProductReview::query()->with(['product', 'user', 'order'])->latest('id')->paginate(20),
        ]);
    }

    public function updateStatus(Request $request, ProductReview $review)
    {
        $data = $request->validate(['status' => ['required', 'in:approved,rejected,pending']]);
        $review->update(['status' => $data['status']]);
        app(AuditLogger::class)->log($request->user(), 'status_updated', 'Reviews', 'ProductReview', (string) $review->id, newValue: ['status' => $data['status']]);

        return back()->with('success', 'Review status updated.');
    }
}
