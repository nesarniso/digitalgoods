<?php

namespace App\Domain\Catalog\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductVariant extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'product_id', 'sku', 'barcode', 'purchase_price', 'price',
        'sale_price', 'weight', 'image_path', 'is_default',
    ];

    protected function casts(): array
    {
        return [
            'purchase_price' => 'decimal:2',
            'price' => 'decimal:2',
            'sale_price' => 'decimal:2',
            'is_default' => 'boolean',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function attributeValues(): BelongsToMany
    {
        return $this->belongsToMany(AttributeValue::class, 'product_variant_attribute_value');
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class);
    }

    public function getEffectivePriceAttribute(): string
    {
        if ($this->sale_price !== null && bccomp((string) $this->sale_price, (string) $this->price, 2) < 0) {
            return (string) $this->sale_price;
        }

        return (string) $this->price;
    }
}
