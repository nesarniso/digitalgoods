<?php

namespace App\Http\Controllers\Api\V1\Storefront;

use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Models\ProductVariant;
use App\Domain\Commerce\Models\CartItem;
use App\Domain\Commerce\Services\CartService;
use App\Domain\Commerce\Services\CouponService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Storefront\AddCartItemRequest;
use App\Http\Requests\Storefront\ApplyCouponRequest;
use App\Http\Requests\Storefront\UpdateCartItemRequest;
use App\Http\Resources\Commerce\CartResource;
use Illuminate\Http\Request;

/**
 * Works for both guests and authenticated customers without two separate
 * route sets: `$request->user('sanctum')` resolves the user from a Bearer
 * token if one is present, without requiring the `auth:sanctum` middleware
 * (which would reject unauthenticated requests outright). Guests are
 * tracked via the `X-Guest-Cart-Token` header instead of a server session,
 * since this API also serves the Flutter app.
 */
class CartController extends Controller
{
    public function __construct(
        private readonly CartService $cartService,
        private readonly CouponService $coupons,
    ) {}

    public function show(Request $request)
    {
        [$cart, $guestToken] = $this->resolve($request);

        return $this->respond($cart, $guestToken);
    }

    public function addItem(AddCartItemRequest $request)
    {
        [$cart, $guestToken] = $this->resolve($request);

        $product = Product::findOrFail($request->integer('product_id'));
        $variant = $request->filled('product_variant_id')
            ? ProductVariant::findOrFail($request->integer('product_variant_id'))
            : null;

        $this->cartService->addItem($cart, $product, $variant, $request->integer('quantity'));

        return $this->respond($cart->fresh(), $guestToken, 201);
    }

    public function updateItem(UpdateCartItemRequest $request, CartItem $item)
    {
        [$cart, $guestToken] = $this->resolve($request);
        $this->assertOwnsItem($cart, $item);

        $this->cartService->updateQuantity($item, $request->integer('quantity'));

        return $this->respond($cart->fresh(), $guestToken);
    }

    public function removeItem(Request $request, CartItem $item)
    {
        [$cart, $guestToken] = $this->resolve($request);
        $this->assertOwnsItem($cart, $item);

        $this->cartService->removeItem($item);

        return $this->respond($cart->fresh(), $guestToken);
    }

    public function clear(Request $request)
    {
        [$cart, $guestToken] = $this->resolve($request);

        $this->cartService->clear($cart);

        return $this->respond($cart->fresh(), $guestToken);
    }

    public function saveCheckoutContact(Request $request)
    {
        [$cart, $guestToken] = $this->resolve($request);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'string', 'max:40'],
            'email' => ['required', 'email', 'max:255'],
        ]);

        $cart->update([
            'checkout_name' => trim($data['name']),
            'checkout_phone' => trim($data['phone']),
            'checkout_email' => strtolower(trim($data['email'])),
            'checkout_contacted_at' => now(),
            'expires_at' => now()->addDays(30),
        ]);

        return $this->respond($cart->fresh(), $guestToken);
    }

    public function applyCoupon(ApplyCouponRequest $request)
    {
        [$cart, $guestToken] = $this->resolve($request);

        $this->coupons->applyToCart($cart, $request->string('code'), $request->user('sanctum'));

        return $this->respond($cart->fresh(), $guestToken);
    }

    public function removeCoupon(Request $request)
    {
        [$cart, $guestToken] = $this->resolve($request);

        $this->coupons->removeFromCart($cart);

        return $this->respond($cart->fresh(), $guestToken);
    }

    /**
     * @return array{0: \App\Domain\Commerce\Models\Cart, 1: ?string}
     */
    private function resolve(Request $request): array
    {
        $result = $this->cartService->resolveCart(
            $request->user('sanctum') ?: $request->user(),
            $request->header('X-Guest-Cart-Token'),
        );

        return [$result['cart'], $result['guest_token']];
    }

    private function assertOwnsItem($cart, CartItem $item): void
    {
        abort_unless($item->cart_id === $cart->id, 404, 'Cart item not found.');
    }

    private function respond($cart, ?string $newGuestToken, int $status = 200)
    {
        $response = response()->json([
            'success' => true,
            'message' => 'Cart retrieved.',
            'data' => new CartResource($cart->load('items.product.images', 'items.variant.attributeValues.attribute')),
            'meta' => (object) [],
        ], $status);

        // A brand-new guest cart's token is handed back so the client can
        // store it and send it as X-Guest-Cart-Token on every subsequent
        // request — there's no server session to rely on instead.
        if ($newGuestToken) {
            $response->header('X-Guest-Cart-Token', $newGuestToken);
        }

        return $response;
    }
}
