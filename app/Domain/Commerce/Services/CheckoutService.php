<?php

namespace App\Domain\Commerce\Services;

use App\Domain\Commerce\Models\Cart;
use App\Domain\Digital\Models\DigitalLicenseKey;
use App\Domain\Inventory\Models\Inventory;
use App\Domain\Inventory\Models\Warehouse;
use App\Domain\Shipping\Models\ShippingMethod;
use App\Domain\Shipping\Services\ShippingService;
use App\Domain\Settings\Services\SettingService;
use App\Domain\Users\Models\Address;

/**
 * Orchestrates everything built in Phases 5–8 into one authoritative,
 * server-computed checkout summary — the concrete expression of Rule 6
 * ("never trust a client-submitted price") applied to the whole cart, not
 * just individual line items.
 *
 * This is read-only: nothing here reserves stock, redeems a coupon, or
 * creates any persistent record. Phase 11 (Orders) is where "place order"
 * actually happens, and it's expected to call this same method (or one
 * that composes the same pieces) immediately before creating the order —
 * so the number a customer sees on the checkout screen and the number
 * that gets charged can never silently drift apart.
 */
class CheckoutService
{
    public function __construct(
        private readonly CartService $cart,
        private readonly PricingService $pricing,
        private readonly ShippingService $shipping,
        private readonly SettingService $settings,
    ) {}

    /**
     * @return array{
     *     items: array,
     *     subtotal: string,
     *     discount_amount: string,
     *     tax_amount: string,
     *     shipping_amount: string,
     *     grand_total: string,
     *     coupon: ?array,
     *     shipping_method: array{id: int, name: string},
     *     payment_method: string,
     *     issues: array<int, array{type: string, message: string, product_id?: int}>,
     *     ready_to_place_order: bool
     * }
     */
    public function summarize(Cart $cart, ?Address $shippingAddress, ?ShippingMethod $shippingMethod, string $paymentMethod): array
    {
        $issues = [];

        if (! $cart->items()->exists()) {
            $issues[] = ['type' => 'empty_cart', 'message' => 'Your cart is empty.'];
        }

        $items = $cart->items()->with(['product.images', 'variant.attributeValues.attribute'])->get();
        foreach ($items as $item) {
            $item->assertPriceable();
        }

        $issues = array_merge($issues, $this->collectAvailabilityIssues($items));

        $pricing = $this->pricing->cartTotals($cart);

        $isDigitalOnly = $this->cart->isDigitalOnly($cart);
        $shippingAmount = '0.00';

        if (! $isDigitalOnly) {
            if (! $shippingAddress || ! $shippingMethod) {
                $issues[] = ['type' => 'shipping_required', 'message' => 'Delivery details and a shipping method are required for physical products.'];
            } else {
                $totalWeight = $this->cart->totalWeight($items);
                $shippingAmount = $this->shipping->calculate($shippingMethod, $shippingAddress, $pricing['subtotal'], $totalWeight);

                if ($shippingAmount === null) {
                    $issues[] = [
                        'type' => 'shipping_unavailable',
                        'message' => "The selected shipping method ({$shippingMethod->name}) does not serve this address.",
                    ];
                    $shippingAmount = '0.00';
                }

                if (($pricing['coupon']['valid'] ?? false) && ($pricing['coupon']['type'] ?? null) === 'free_shipping') {
                    $shippingAmount = '0.00';
                }

                if (! $shippingMethod->is_active) {
                    $issues[] = ['type' => 'shipping_method_inactive', 'message' => 'The selected shipping method is no longer active.'];
                }
            }
        }

        $paymentIssue = $this->assertPaymentMethodAvailable($paymentMethod, $isDigitalOnly);
        if ($paymentIssue) {
            $issues[] = $paymentIssue;
        }

        $grandTotal = bcadd(
            bcsub(bcadd($pricing['subtotal'], $pricing['tax_amount'], 2), $pricing['discount_amount'], 2),
            $shippingAmount,
            2,
        );

        return [
            'items' => $items->map(fn ($item) => [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'product_variant_id' => $item->product_variant_id,
                'product_type' => $item->product->product_type,
                'image_url' => $item->product->images->first()?->url,
                'variant_label' => $item->variant?->attributeValues
                    ?->map(fn ($value) => "{$value->attribute->name}: {$value->value}")
                    ->implode(', '),
                'name' => $item->product->name,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'line_total' => $item->line_total,
            ])->all(),
            'subtotal' => $pricing['subtotal'],
            'discount_amount' => $pricing['discount_amount'],
            'tax_amount' => $pricing['tax_amount'],
            'shipping_amount' => $shippingAmount,
            'grand_total' => $grandTotal,
            'coupon' => $pricing['coupon'],
            'shipping_method' => $shippingMethod ? ['id' => $shippingMethod->id, 'name' => $shippingMethod->name] : null,
            'payment_method' => $paymentMethod,
            'issues' => $issues,
            'ready_to_place_order' => empty($issues),
        ];
    }

    /**
     * Checks each line item is still purchasable right now — a product can
     * go from published to draft, or run out of stock, after it was
     * already added to the cart. Stock is checked against the default
     * warehouse's `available_stock`; a product with no inventory row at
     * all is treated as untracked (not blocked) rather than as
     * zero-stock — inventory tracking is opt-in per product, not
     * mandatory, matching how Phase 5 leaves it.
     *
     * @param  \Illuminate\Support\Collection<int, \App\Domain\Commerce\Models\CartItem>  $items
     * @return array<int, array{type: string, message: string, product_id: int}>
     */
    private function collectAvailabilityIssues(\Illuminate\Support\Collection $items): array
    {
        $issues = [];
        $defaultWarehouse = Warehouse::query()->where('is_default', true)->first();

        foreach ($items as $item) {
            if ($item->product->status !== 'published') {
                $issues[] = [
                    'type' => 'product_unavailable',
                    'message' => "\"{$item->product->name}\" is no longer available and was not included in the total.",
                    'product_id' => $item->product_id,
                ];

                continue;
            }

            if ($item->product->product_type === 'digital'
                && ! $item->product->download_url
                && ! $item->product->downloadFiles()->exists()) {
                $availableKeys = DigitalLicenseKey::query()
                    ->where('product_id', $item->product_id)
                    ->where('product_variant_id', $item->product_variant_id)
                    ->where('status', 'available')
                    ->count();

                if ($availableKeys < $item->quantity) {
                    $issues[] = [
                        'type' => 'digital_delivery_unavailable',
                        'message' => "Digital delivery is not configured for \"{$item->product->name}\" yet.",
                        'product_id' => $item->product_id,
                    ];
                }
            }

            if (! $defaultWarehouse) {
                continue; // no warehouse configured at all — nothing to check stock against yet
            }

            $inventory = Inventory::query()
                ->where('product_id', $item->product_id)
                ->where('product_variant_id', $item->product_variant_id)
                ->where('warehouse_id', $defaultWarehouse->id)
                ->first();

            if (! $inventory) {
                continue; // untracked — treated as available, not as zero-stock
            }

            if ($inventory->available_stock < $item->quantity) {
                $issues[] = [
                    'type' => 'insufficient_stock',
                    'message' => "Only {$inventory->available_stock} of \"{$item->product->name}\" available (you have {$item->quantity} in cart).",
                    'product_id' => $item->product_id,
                ];
            }
        }

        return $issues;
    }

    private function assertPaymentMethodAvailable(string $method, bool $isDigitalOnly): ?array
    {
        $config = config("payments.methods.{$method}");

        if ($config === null) {
            return ['type' => 'invalid_payment_method', 'message' => "Unknown payment method: {$method}."];
        }

        if (! ($config['enabled'] ?? false)
            || ! $this->settings->boolean("payments.{$method}.enabled", $config['enabled'] ?? false)) {
            return ['type' => 'payment_method_unavailable', 'message' => "{$config['label']} is not currently available."];
        }

        if ($isDigitalOnly && $method === 'cash_on_delivery') {
            return ['type' => 'payment_method_unavailable', 'message' => 'Cash on Delivery is unavailable for digital-only carts.'];
        }

        return null;
    }
}
