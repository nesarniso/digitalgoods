<?php

namespace App\Http\Resources\Orders;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'order_number' => $this->order_number,
            'status' => $this->status,
            'subtotal' => $this->subtotal,
            'discount_amount' => $this->discount_amount,
            'tax_amount' => $this->tax_amount,
            'shipping_amount' => $this->shipping_amount,
            'grand_total' => $this->grand_total,
            'coupon_code' => $this->coupon_code,
            'shipping_method_name' => $this->shipping_method_name,
            'payment_method' => $this->payment_method,
            'payment_status' => $this->payment_status,
            'customer_email' => $this->customer_email,
            'customer_phone' => $this->customer_phone,
            'notes' => $this->notes,
            'placed_at' => $this->placed_at,
            'cancelled_at' => $this->cancelled_at,
            'cancel_reason' => $this->cancel_reason,
            'items' => $this->whenLoaded('items', fn () => $this->items->map(fn ($item) => [
                'product_id' => $item->product_id,
                'product_variant_id' => $item->product_variant_id,
                'product_name' => $item->product_name,
                'sku' => $item->sku,
                'variant_label' => $item->variant_label,
                'unit_price' => $item->unit_price,
                'quantity' => $item->quantity,
                'line_total' => $item->line_total,
            ])),
            'shipping_address' => $this->whenLoaded('addresses', fn () => $this->addresses->firstWhere('type', 'shipping')),
            'status_history' => $this->whenLoaded('statusHistory', fn () => $this->statusHistory->map(fn ($h) => [
                'status' => $h->status,
                'message' => $h->message,
                'created_at' => $h->created_at,
            ])),
        ];
    }
}
