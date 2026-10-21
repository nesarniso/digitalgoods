<?php

namespace App\Domain\Inventory\Models;

use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Models\ProductVariant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Inventory extends Model
{
    protected $fillable = [
        'product_id', 'product_variant_id', 'warehouse_id',
        'physical_stock', 'reserved_stock', 'damaged_stock', 'low_stock_threshold',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function movements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    /**
     * available_stock = physical_stock - reserved_stock, per spec section
     * 10. Always derived, never stored — every write path goes through
     * InventoryService under a row lock, so this is safe to compute on
     * read without a race window.
     */
    public function getAvailableStockAttribute(): int
    {
        return max(0, $this->physical_stock - $this->reserved_stock);
    }

    public function isLowStock(): bool
    {
        return $this->available_stock <= $this->low_stock_threshold;
    }

    public function isOutOfStock(): bool
    {
        return $this->available_stock <= 0;
    }
}
