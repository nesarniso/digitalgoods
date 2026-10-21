<?php

namespace App\Http\Controllers\Api\V1\Storefront;

use App\Domain\Commerce\Services\CartService;
use App\Domain\Commerce\Services\CheckoutService;
use App\Domain\Shipping\Models\ShippingMethod;
use App\Domain\Users\Models\Address;
use App\Http\Controllers\Controller;
use App\Http\Requests\Storefront\CheckoutSummaryRequest;

class CheckoutController extends Controller
{
    public function __construct(
        private readonly CheckoutService $checkout,
        private readonly CartService $cartService,
    ) {}

    /**
     * POST /api/v1/checkout/summary — the "review order" screen's data
     * source. Works for guests (X-Guest-Cart-Token, inline address only)
     * and authenticated customers (either a saved address_id or inline
     * fields). Nothing here is persisted or reserved; calling this
     * endpoint repeatedly with the same input always returns the same
     * answer (modulo stock/coupon/price changing in the meantime).
     */
    public function summary(CheckoutSummaryRequest $request)
    {
        $user = $request->user('sanctum') ?: $request->user();

        $result = $this->cartService->resolveCart($user, $request->header('X-Guest-Cart-Token'));
        $cart = $result['cart'];

        $digitalOnly = $this->cartService->isDigitalOnly($cart);
        $address = $this->resolveAddress($request, $user);
        $shippingMethod = $digitalOnly ? null : ($request->filled('shipping_method_id')
            ? ShippingMethod::findOrFail($request->integer('shipping_method_id'))
            : ShippingMethod::query()->where('is_active', true)->orderBy('sort_order')->firstOrFail());

        $summary = $this->checkout->summarize(
            $cart,
            $address,
            $shippingMethod,
            $request->string('payment_method'),
        );

        $response = response()->json([
            'success' => true,
            'message' => $summary['ready_to_place_order'] ? 'Checkout summary ready.' : 'Checkout summary has issues — see `issues`.',
            'data' => $summary,
            'meta' => (object) [],
        ]);

        if ($result['guest_token']) {
            $response->header('X-Guest-Cart-Token', $result['guest_token']);
        }

        return $response;
    }

    private function resolveAddress(CheckoutSummaryRequest $request, $user): Address
    {
        if ($request->filled('address_id')) {
            $address = Address::findOrFail($request->integer('address_id'));

            abort_unless($user && $address->user_id === $user->id, 404, 'Address not found.');

            return $address;
        }

        // Not persisted — this is exactly the "order_addresses is a
        // separate snapshot from the customer's saved address book"
        // pattern from spec section 76. Works identically for guests and
        // authenticated customers shipping somewhere other than a saved
        // address.
        return new Address([
            'name' => $request->string('name'),
            'phone' => $request->string('phone'),
            'country' => $request->input('country', 'Bangladesh'),
            'division' => $request->input('division'),
            'district' => $request->input('district'),
            'upazila' => $request->input('upazila'),
            'area' => $request->input('area'),
            'postal_code' => $request->input('postal_code'),
            'address_line' => $request->string('address_line'),
        ]);
    }
}
