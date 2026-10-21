<?php

namespace App\Http\Controllers\Api\V1\Storefront;

use App\Domain\Catalog\Models\Product;
use App\Domain\Commerce\Services\CompareService;
use App\Domain\Settings\Services\FeatureFlagService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Storefront\ProductIdRequest;
use Illuminate\Http\Request;

class CompareController extends Controller
{
    public function __construct(
        private readonly CompareService $compare,
        private readonly FeatureFlagService $flags,
    ) {}

    public function index(Request $request)
    {
        $this->assertEnabled();

        $items = $this->compare->list($request->user());

        return response()->json([
            'success' => true,
            'message' => 'Compare list retrieved.',
            'data' => $items->map(fn ($i) => [
                'id' => $i->id,
                'product_id' => $i->product_id,
                'product_name' => $i->product->name,
                'effective_price' => $i->product->effective_price,
                'brand_id' => $i->product->brand_id,
            ]),
            'meta' => (object) [],
        ]);
    }

    public function store(ProductIdRequest $request)
    {
        $this->assertEnabled();

        $product = Product::findOrFail($request->integer('product_id'));
        $this->compare->add($request->user(), $product);

        return response()->json([
            'success' => true,
            'message' => 'Added to compare list.',
            'data' => (object) [],
            'meta' => (object) [],
        ], 201);
    }

    public function destroy(Request $request, Product $product)
    {
        $this->assertEnabled();

        $this->compare->remove($request->user(), $product);

        return response()->json([
            'success' => true,
            'message' => 'Removed from compare list.',
            'data' => (object) [],
            'meta' => (object) [],
        ]);
    }

    private function assertEnabled(): void
    {
        abort_unless($this->flags->isEnabled('compare'), 404, 'Compare is not enabled.');
    }
}
