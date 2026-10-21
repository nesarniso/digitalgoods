<?php

namespace App\Domain\Commerce\Models;

use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Models\ProductVariant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\ValidationException;

class CartItem extends Model
{
    protected $fillable = ['cart_id', 'product_id', 'product_variant_id', 'quantity'];

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function assertPriceable(): void
    {
        if (! $this->product) {
            throw ValidationException::withMessages([
                'cart' => ['A product in your cart is no longer available. Please refresh your cart.'],
            ]);
        }

        if ($this->product_variant_id !== null && ! $this->variant) {
            throw ValidationException::withMessages([
                'cart' => ['A selected product option in your cart is no longer available. Please refresh your cart.'],
            ]);
        }

        if ($this->variant && $this->variant->product_id !== $this->product_id) {
            throw ValidationException::withMessages([
                'cart' => ['A selected product option is invalid. Please refresh your cart.'],
            ]);
        }

        if ($this->product->isVariable() && ! $this->variant) {
            throw ValidationException::withMessages([
                'cart' => ['Please select a valid option for each variable product in your cart.'],
            ]);
        }
    }

    public function getUnitPriceAttribute(): string
    {
        $this->assertPriceable();

        return $this->variant ? $this->variant->effective_price : $this->product->effective_price;
    }

    public function getLineTotalAttribute(): string
    {
        return bcmul($this->unit_price, (string) $this->quantity, 2);
    }
}
