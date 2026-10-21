<?php

namespace App\Domain\Orders\Models;

use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Models\ProductVariant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id', 'product_id', 'product_variant_id',
        'product_name', 'sku', 'variant_label',
        'unit_price', 'quantity', 'line_total', 'stock_reserved',
        'cost_price', 'supplier_cost', 'delivery_cost', 'return_cost',
        'entrepreneur_profit', 'entrepreneur_profit_percent', 'entrepreneur_profit_flat_rate',
        'applicable_cost', 'profit', 'profit_status',
    ];

    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
            'line_total' => 'decimal:2',
            'cost_price' => 'decimal:2',
            'supplier_cost' => 'decimal:2',
            'delivery_cost' => 'decimal:2',
            'return_cost' => 'decimal:2',
            'entrepreneur_profit' => 'decimal:2',
            'entrepreneur_profit_percent' => 'decimal:2',
            'entrepreneur_profit_flat_rate' => 'decimal:2',
            'applicable_cost' => 'decimal:2',
            'profit' => 'decimal:2',
            'stock_reserved' => 'boolean',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Nullable and for "buy again" navigation ONLY — never used to
     * display this line item's name/price. If the product was deleted,
     * this is null and the snapshot fields still render correctly.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function returnRequests(): HasMany
    {
        return $this->hasMany(ReturnRequest::class, 'order_item_id');
    }
}
