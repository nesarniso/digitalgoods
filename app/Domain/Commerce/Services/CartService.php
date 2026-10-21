<?php

namespace App\Domain\Commerce\Services;

use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Models\ProductVariant;
use App\Domain\Commerce\Models\Cart;
use App\Domain\Commerce\Models\CartItem;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Sole entry point for cart mutation. Cart line totals are always computed
 * live from Product/ProductVariant pricing (see CartItem accessors) —
 * nothing here ever trusts a client-submitted price.
 */
class CartService
{
    private const CART_LIFETIME_DAYS = 30;

    /**
     * Resolves the cart for the current request: the user's cart if
     * authenticated, otherwise the guest cart matching the given token
     * (creating one — with a fresh token — if none exists yet).
     *
     * @return array{cart: Cart, guest_token: ?string} guest_token is only
     *         populated when a NEW guest cart was created, so the caller
     *         knows to hand it back to the client.
     */
    public function resolveCart(?User $user, ?string $guestToken): array
    {
        if ($user) {
            $cart = Cart::query()->firstOrCreate(
                ['user_id' => $user->id],
                ['uuid' => (string) Str::uuid(), 'expires_at' => now()->addDays(self::CART_LIFETIME_DAYS)],
            );

            return ['cart' => $cart, 'guest_token' => null];
        }

        if ($guestToken) {
            $cart = Cart::query()->where('guest_token', $guestToken)->first();

            if ($cart && (! $cart->expires_at || $cart->expires_at->isFuture())) {
                return ['cart' => $cart, 'guest_token' => null];
            }
        }

        $newToken = (string) Str::uuid();

        $cart = Cart::create([
            'uuid' => (string) Str::uuid(),
            'guest_token' => $newToken,
            'expires_at' => now()->addDays(self::CART_LIFETIME_DAYS),
        ]);

        return ['cart' => $cart, 'guest_token' => $newToken];
    }

    public function addItem(Cart $cart, Product $product, ?ProductVariant $variant, int $quantity): CartItem
    {
        $this->assertPurchasable($product, $variant, $quantity);

        return DB::transaction(function () use ($cart, $product, $variant, $quantity) {
            $existing = CartItem::query()
                ->where('cart_id', $cart->id)
                ->where('product_id', $product->id)
                ->where('product_variant_id', $variant?->id)
                ->lockForUpdate()
                ->first();

            if ($existing) {
                $newQuantity = $existing->quantity + $quantity;
                $this->assertWithinOrderLimits($product, $newQuantity);
                $existing->update(['quantity' => $newQuantity]);
                $item = $existing;
            } else {
                $item = CartItem::create([
                    'cart_id' => $cart->id,
                    'product_id' => $product->id,
                    'product_variant_id' => $variant?->id,
                    'quantity' => $quantity,
                ]);
            }

            $this->touch($cart);

            return $item;
        });
    }

    public function updateQuantity(CartItem $item, int $quantity): CartItem
    {
        $item->loadMissing(['product', 'variant', 'cart']);

        if ($quantity <= 0) {
            $this->removeItem($item);

            return $item;
        }

        $item->assertPriceable();
        $this->assertWithinOrderLimits($item->product, $quantity);
        $item->update(['quantity' => $quantity]);
        $this->touch($item->cart);

        return $item;
    }

    public function removeItem(CartItem $item): void
    {
        $item->loadMissing('cart');
        $cart = $item->cart;
        $item->delete();
        $this->touch($cart);
    }

    public function clear(Cart $cart): void
    {
        $cart->items()->delete();
        $this->touch($cart);
    }

    /**
     * Called right after a successful login/registration when the client
     * was carrying a guest cart. Line quantities for items that exist in
     * both carts are summed (capped at each product's max order quantity);
     * items only in the guest cart are moved over. The guest cart is then
     * deleted.
     */
    public function mergeGuestCartIntoUser(?string $guestToken, User $user): void
    {
        if (! $guestToken) {
            return;
        }

        DB::transaction(function () use ($guestToken, $user) {
            $guestCart = Cart::query()
                ->where('guest_token', $guestToken)
                ->lockForUpdate()
                ->first();

            if (! $guestCart) {
                return;
            }

            $userCart = Cart::query()
                ->where('user_id', $user->id)
                ->lockForUpdate()
                ->first();

            if (! $userCart) {
                $userCart = Cart::create([
                    'user_id' => $user->id,
                    'uuid' => (string) Str::uuid(),
                    'expires_at' => now()->addDays(self::CART_LIFETIME_DAYS),
                ]);
            }

            $guestItems = $guestCart->items()->lockForUpdate()->get();

            foreach ($guestItems as $guestItem) {
                $existing = CartItem::query()
                    ->with('product')
                    ->where('cart_id', $userCart->id)
                    ->where('product_id', $guestItem->product_id)
                    ->where('product_variant_id', $guestItem->product_variant_id)
                    ->lockForUpdate()
                    ->first();

                if ($existing) {
                    $maxQty = $existing->product->maximum_order_quantity;
                    $summed = $existing->quantity + $guestItem->quantity;
                    $existing->update(['quantity' => $maxQty ? min($summed, $maxQty) : $summed]);
                } else {
                    CartItem::create([
                        'cart_id' => $userCart->id,
                        'product_id' => $guestItem->product_id,
                        'product_variant_id' => $guestItem->product_variant_id,
                        'quantity' => $guestItem->quantity,
                    ]);
                }
            }

            $guestCart->delete(); // cascades to guest cart_items
            $this->touch($userCart);
        });
    }

    /**
     * @return array{items: \Illuminate\Support\Collection, subtotal: string}
     */
    public function totals(Cart $cart): array
    {
        $items = $cart->items()->with(['product', 'variant'])->get();

        $subtotal = $items->reduce(fn (string $carry, CartItem $item) => bcadd($carry, $item->line_total, 2), '0.00');

        return ['items' => $items, 'subtotal' => $subtotal];
    }

    /**
     * Total shipping weight in kg for a set of cart items — variant weight
     * takes priority over the product's weight, matching how price
     * resolution already works (CartItem::effective_price). Shared by
     * ShippingController (Phase 8) and CheckoutService (Phase 9) so the
     * weight used for a shipping quote and the weight used at checkout
     * time can never silently disagree.
     *
     * @param  \Illuminate\Support\Collection<int, CartItem>  $items
     */
    public function totalWeight(\Illuminate\Support\Collection $items): string
    {
        return $items->reduce(function (string $carry, CartItem $item) {
            $weight = (string) ($item->variant?->weight ?? $item->product->weight ?? '0');

            return bcadd($carry, bcmul($weight, (string) $item->quantity, 3), 3);
        }, '0.000');
    }

    public function isDigitalOnly(Cart $cart): bool
    {
        $items = $cart->items()->with('product')->get();

        return $items->isNotEmpty() && $items->every(
            fn (CartItem $item): bool => $item->product->product_type === 'digital',
        );
    }

    private function assertPurchasable(Product $product, ?ProductVariant $variant, int $quantity): void
    {
        if ($product->status !== 'published') {
            throw ValidationException::withMessages(['product_id' => ['This product is not available for purchase.']]);
        }

        if ($product->isVariable() && $variant === null) {
            throw ValidationException::withMessages(['product_variant_id' => ['This product requires a variant to be selected.']]);
        }

        if (! $product->isVariable() && $variant !== null) {
            throw ValidationException::withMessages(['product_variant_id' => ['The selected product does not have variants.']]);
        }

        if ($variant && $variant->product_id !== $product->id) {
            throw ValidationException::withMessages(['product_variant_id' => ['This variant does not belong to the given product.']]);
        }

        $this->assertWithinOrderLimits($product, $quantity);
    }

    private function assertWithinOrderLimits(Product $product, int $quantity): void
    {
        if ($quantity < $product->minimum_order_quantity) {
            throw ValidationException::withMessages([
                'quantity' => ["Minimum order quantity for this product is {$product->minimum_order_quantity}."],
            ]);
        }

        if ($product->maximum_order_quantity && $quantity > $product->maximum_order_quantity) {
            throw ValidationException::withMessages([
                'quantity' => ["Maximum order quantity for this product is {$product->maximum_order_quantity}."],
            ]);
        }
    }

    private function touch(Cart $cart): void
    {
        $cart->update(['expires_at' => now()->addDays(self::CART_LIFETIME_DAYS)]);
    }
}
