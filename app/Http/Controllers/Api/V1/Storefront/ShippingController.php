<?php

namespace App\Http\Controllers\Api\V1\Storefront;

use App\Domain\Commerce\Services\CartService;
use App\Domain\Shipping\Services\ShippingService;
use App\Domain\Users\Models\Address;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ShippingController extends Controller
{
    public function __construct(
        private readonly ShippingService $shipping,
        private readonly CartService $cartService,
    ) {}

    /**
     * GET /api/v1/shipping/methods?address_id=X — resolves the current
     * cart (guest or authenticated, same as CartController) and returns
     * every active shipping method with its calculated rate for the given
     * address, excluding methods that don't serve that destination at
     * all.
     */
    public function methods(Request $request)
    {
        $address = Address::findOrFail($request->integer('address_id'));

        $user = $request->user('sanctum') ?: $request->user();
        if ($user) {
            abort_unless($address->user_id === $user->id, 404, 'Address not found.');
        }

        $result = $this->cartService->resolveCart($user, $request->header('X-Guest-Cart-Token'));
        $cart = $result['cart'];
        $items = $cart->items()->with(['product', 'variant'])->get();

        $subtotal = $items->reduce(fn (string $carry, $item) => bcadd($carry, $item->line_total, 2), '0.00');
        $totalWeight = $this->cartService->totalWeight($items);

        $rates = $this->shipping->availableRates($address, $subtotal, $totalWeight);

        return response()->json([
            'success' => true,
            'message' => 'Shipping methods retrieved.',
            'data' => $rates->map(fn ($r) => [
                'shipping_method_id' => $r['method']->id,
                'name' => $r['method']->name,
                'type' => $r['method']->type,
                'rate' => $r['rate'],
                'free' => $r['free'],
            ]),
            'meta' => ['cart_weight_kg' => $totalWeight, 'cart_subtotal' => $subtotal],
        ]);
    }
}
