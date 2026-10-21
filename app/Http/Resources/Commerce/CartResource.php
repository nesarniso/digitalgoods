<?php

namespace App\Http\Resources\Commerce;

use App\Domain\Commerce\Services\PricingService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $items = $this->items;

        // Pricing (subtotal, discount, tax, grand total) is delegated to
        // PricingService rather than recomputed here — this keeps the
        // "server always recalculates" rule enforced in exactly one place
        // even though a Resource is technically presentation, not
        // mutation. Phase 9 (Checkout) calls the same service directly for
        // the authoritative order total.
        $totals = app(PricingService::class)->cartTotals($this->resource);

        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'items' => $items->map(fn ($item) => [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'product_name' => $item->product->name,
                'product_type' => $item->product->product_type,
                'product_variant_id' => $item->product_variant_id,
                'variant_label' => $item->variant?->attributeValues
                    ?->map(fn ($value) => "{$value->attribute->name}: {$value->value}")
                    ->implode(', '),
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'line_total' => $item->line_total,
            ]),
            'subtotal' => $totals['subtotal'],
            'discount_amount' => $totals['discount_amount'],
            'tax_amount' => $totals['tax_amount'],
            'grand_total' => $totals['grand_total'],
            'coupon' => $totals['coupon'],
            'expires_at' => $this->expires_at,
        ];
    }
}
