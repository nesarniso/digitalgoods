<?php

namespace App\Http\Controllers;

use App\Domain\Catalog\Models\Product;
use App\Domain\Entrepreneurs\Models\EntrepreneurProduct;
use App\Domain\Finance\Services\ProfitService;
use Illuminate\Http\Request;

class EntrepreneurMarketplaceController extends Controller
{
    public function index(Request $request, ProfitService $profits)
    {
        $products = Product::query()
            ->where('status', 'published')
            ->whereNotNull('purchase_price')
            ->with(['category', 'images'])
            ->when($request->filled('q'), fn ($query) => $query->where('name', 'like', '%'.$request->string('q').'%'))
            ->latest('published_at')
            ->paginate(12)
            ->withQueryString();

        $selectedProductIds = $request->user()->entrepreneurProducts()
            ->where('status', 'active')
            ->pluck('product_id')
            ->all();

        $profitPercent = $profits->percentage();
        $products->getCollection()->each(function ($product) use ($profits) {
            $product->effective_entrepreneur_profit_percent = $profits->percentageForProduct($product);
            $product->estimated_entrepreneur_profit = $product->purchase_price === null
                ? '0.00'
                : $profits->entrepreneurProfit(
                    (string) $product->purchase_price,
                    (string) $product->effective_price,
                    '0',
                    $product->effective_entrepreneur_profit_percent,
                    $profits->flatRateForProduct($product),
                );
        });

        return view('entrepreneur.marketplace', compact('products', 'selectedProductIds', 'profitPercent'));
    }

    public function store(Request $request, Product $product)
    {
        abort_unless($product->status === 'published' && $product->purchase_price !== null, 404);

        $alreadyAdded = EntrepreneurProduct::query()
            ->where('entrepreneur_id', $request->user()->id)
            ->where('product_id', $product->id)
            ->where('status', 'active')
            ->exists();

        if ($alreadyAdded) {
            return back()->with('success', 'This product is already in your products.');
        }

        EntrepreneurProduct::query()->updateOrCreate(
            ['entrepreneur_id' => $request->user()->id, 'product_id' => $product->id],
            ['status' => 'active']
        );

        return back()->with('success', 'Product added to your marketing list.');
    }
}
