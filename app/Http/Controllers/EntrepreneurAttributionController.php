<?php

namespace App\Http\Controllers;

use App\Domain\Entrepreneurs\Services\AttributionService;
use App\Domain\Catalog\Models\Product;
use App\Domain\Entrepreneurs\Models\EntrepreneurLinkClick;
use Illuminate\Http\Request;

class EntrepreneurAttributionController extends Controller
{
    public function __invoke(Request $request, AttributionService $attribution, string $code, string $slug)
    {
        $profile = $attribution->resolve($code);
        $product = Product::query()->where('slug', $slug)->firstOrFail();

        EntrepreneurLinkClick::create([
            'entrepreneur_id' => $profile->user_id,
            'product_id' => $product->id,
            'attribution_code' => $profile->entrepreneur_code,
            'session_key' => $request->hasSession()
                ? hash('sha256', (string) $request->session()->getId())
                : null,
        ]);
        $attribution->remember($request, $profile->entrepreneur_code);

        return redirect()->route('products.show', ['slug' => $slug]);
    }

    public function referral(Request $request, AttributionService $attribution, string $code)
    {
        $attribution->remember($request, $code);

        return redirect()->route('home');
    }
}
