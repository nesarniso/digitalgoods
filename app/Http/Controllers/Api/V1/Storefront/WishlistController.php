<?php

namespace App\Http\Controllers\Api\V1\Storefront;

use App\Domain\Catalog\Models\Product;
use App\Domain\Commerce\Services\WishlistService;
use App\Domain\Settings\Services\FeatureFlagService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Storefront\ProductIdRequest;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    public function __construct(
        private readonly WishlistService $wishlist,
        private readonly FeatureFlagService $flags,
    ) {}

    public function index(Request $request)
    {
        $this->assertEnabled();

        $items = $this->wishlist->list($request->user());

        return response()->json([
            'success' => true,
            'message' => 'Wishlist retrieved.',
            'data' => $items->map(fn ($i) => [
                'id' => $i->id,
                'product_id' => $i->product_id,
                'product_name' => $i->product->name,
                'effective_price' => $i->product->effective_price,
            ]),
            'meta' => (object) [],
        ]);
    }

    public function store(ProductIdRequest $request)
    {
        $this->assertEnabled();

        $product = Product::findOrFail($request->integer('product_id'));
        $this->wishlist->add($request->user(), $product);

        return response()->json([
            'success' => true,
            'message' => 'Added to wishlist.',
            'data' => (object) [],
            'meta' => (object) [],
        ], 201);
    }

    public function destroy(Request $request, Product $product)
    {
        $this->assertEnabled();

        $this->wishlist->remove($request->user(), $product);

        return response()->json([
            'success' => true,
            'message' => 'Removed from wishlist.',
            'data' => (object) [],
            'meta' => (object) [],
        ]);
    }

    private function assertEnabled(): void
    {
        abort_unless($this->flags->isEnabled('wishlist'), 404, 'Wishlist is not enabled.');
    }
}
