<?php

namespace App\Domain\Commerce\Services;

use App\Domain\Commerce\Models\Cart;
use App\Domain\Commerce\Models\CartItem;
use App\Domain\Commerce\Models\Coupon;
use Illuminate\Support\Collection;

/**
 * The single place cart/checkout totals get computed. Phase 9 (Checkout)
 * and Phase 11 (Orders) call this rather than recalculating pricing
 * themselves — that's what makes Rule 6 ("never trust a client-submitted
 * price") actually enforceable everywhere instead of per-endpoint.
 *
 * Shipping is intentionally excluded here — that's Phase 8's
 * responsibility and gets added to grand_total at checkout time, not cart
 * display time (shipping cost usually depends on an address the cart
 * doesn't have yet).
 */
class PricingService
{
    public function __construct(
        private readonly TaxService $tax,
        private readonly CouponService $coupons,
    ) {}

    /**
     * @return array{
     *     subtotal: string,
     *     discount_amount: string,
     *     tax_amount: string,
     *     grand_total: string,
     *     coupon: ?array{code: string, valid: bool, reason: ?string}
     * }
     */
    public function cartTotals(Cart $cart): array
    {
        $items = $cart->items()->with(['product', 'variant'])->get();

        foreach ($items as $item) {
            $item->assertPriceable();
        }

        $subtotal = $this->subtotal($items);
        $taxAmount = $this->tax->calculate($items);

        $discountAmount = '0.00';
        $couponInfo = null;

        if ($cart->coupon_id) {
            $coupon = Coupon::find($cart->coupon_id);

            if ($coupon) {
                [$discountAmount, $valid, $reason] = $this->discountForCoupon($coupon, $items, $subtotal, $cart);
                $couponInfo = ['code' => $coupon->code, 'type' => $coupon->type, 'valid' => $valid, 'reason' => $reason];
            }
        }

        $grandTotal = bcsub(bcadd($subtotal, $taxAmount, 2), $discountAmount, 2);
        // A coupon (or an unusually high tax rate on a heavily-discounted
        // cart) should never be able to push the total below zero.
        if (bccomp($grandTotal, '0.00', 2) < 0) {
            $grandTotal = '0.00';
        }

        return [
            'subtotal' => $subtotal,
            'discount_amount' => $discountAmount,
            'tax_amount' => $taxAmount,
            'grand_total' => $grandTotal,
            'coupon' => $couponInfo,
        ];
    }

    /**
     * @param  Collection<int, CartItem>  $items
     */
    private function subtotal(Collection $items): string
    {
        return $items->reduce(fn (string $carry, CartItem $item) => bcadd($carry, $item->line_total, 2), '0.00');
    }

    /**
     * @param  Collection<int, CartItem>  $items
     * @return array{0: string, 1: bool, 2: ?string} [discount, isValid, invalidReason]
     */
    private function discountForCoupon(Coupon $coupon, Collection $items, string $subtotal, Cart $cart): array
    {
        if (! $coupon->isCurrentlyActive()) {
            return ['0.00', false, 'This coupon is no longer active.'];
        }

        if ($coupon->min_order_amount !== null && bccomp($subtotal, (string) $coupon->min_order_amount, 2) < 0) {
            return ['0.00', false, "Minimum order amount for this coupon is {$coupon->min_order_amount}."];
        }

        $eligibleSubtotal = $this->eligibleSubtotal($coupon, $items);

        if (bccomp($eligibleSubtotal, '0.00', 2) <= 0) {
            return ['0.00', false, 'No items in this cart are eligible for this coupon.'];
        }

        $discount = match ($coupon->type) {
            'percentage' => bcmul($eligibleSubtotal, bcdiv((string) $coupon->value, '100', 6), 2),
            'fixed' => '0.00', // overridden immediately below with proper bcmath capping
            'free_shipping' => '0.00', // shipping is waived by CheckoutService after coupon validation
            default => '0.00',
        };

        // bcmath strings don't compare with PHP's min()/max() the way
        // floats do, so fixed-type discount capping is done explicitly
        // with bccomp instead of relying on the match arm above.
        if ($coupon->type === 'fixed') {
            $fixedValue = (string) ($coupon->value ?? '0.00');
            $discount = bccomp($fixedValue, $eligibleSubtotal, 2) > 0 ? $eligibleSubtotal : $fixedValue;
        }

        if ($coupon->max_discount_amount !== null && bccomp($discount, (string) $coupon->max_discount_amount, 2) > 0) {
            $discount = (string) $coupon->max_discount_amount;
        }

        return [$discount, true, null];
    }

    /**
     * Sum of line totals eligible for this coupon: everything, unless the
     * coupon is scoped to specific products/categories, in which case only
     * matching lines count.
     *
     * @param  Collection<int, CartItem>  $items
     */
    private function eligibleSubtotal(Coupon $coupon, Collection $items): string
    {
        if (! $coupon->isScoped()) {
            return $this->subtotal($items);
        }

        $productIds = $coupon->products()->pluck('products.id')->all();
        $categoryIds = $coupon->categories()->pluck('categories.id')->all();

        return $items
            ->filter(fn (CartItem $item) => in_array($item->product_id, $productIds, true)
                || in_array($item->product->category_id, $categoryIds, true))
            ->reduce(fn (string $carry, CartItem $item) => bcadd($carry, $item->line_total, 2), '0.00');
    }
}
