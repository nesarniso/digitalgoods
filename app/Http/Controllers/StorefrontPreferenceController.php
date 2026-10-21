<?php

namespace App\Http\Controllers;

use App\Domain\Catalog\Models\Product;
use App\Domain\Commerce\Services\CompareService;
use App\Domain\Commerce\Services\WishlistService;
use App\Domain\Settings\Services\FeatureFlagService;
use Illuminate\Http\Request;

class StorefrontPreferenceController extends Controller
{
    public function __construct(
        private readonly WishlistService $wishlist,
        private readonly CompareService $compare,
        private readonly FeatureFlagService $flags,
    ) {}

    public function wishlist(Request $request)
    {
        return view('storefront/account/wishlist', [
            'items' => $this->wishlist->list($request->user()),
        ]);
    }

    public function addWishlist(Request $request, Product $product)
    {
        $this->wishlist->add($request->user(), $product);

        return back()->with('success', 'Product added to your wishlist.');
    }

    public function removeWishlist(Request $request, Product $product)
    {
        $this->wishlist->remove($request->user(), $product);

        return back()->with('success', 'Product removed from your wishlist.');
    }

    public function compare(Request $request)
    {
        $this->assertEnabled('compare');

        return view('storefront/account/compare', [
            'items' => $this->compare->list($request->user()),
        ]);
    }

    public function addCompare(Request $request, Product $product)
    {
        $this->assertEnabled('compare');
        $this->compare->add($request->user(), $product);

        return back()->with('success', 'Product added to compare.');
    }

    public function removeCompare(Request $request, Product $product)
    {
        $this->assertEnabled('compare');
        $this->compare->remove($request->user(), $product);

        return back()->with('success', 'Product removed from compare.');
    }

    private function assertEnabled(string $key): void
    {
        abort_unless($this->flags->isEnabled($key), 404, ucfirst($key).' is not enabled.');
    }
}
