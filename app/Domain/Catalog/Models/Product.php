<?php

namespace App\Domain\Catalog\Models;

use App\Domain\Entrepreneurs\Models\EntrepreneurProduct;
use App\Domain\Digital\Models\ProductDownloadFile;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'uuid', 'vendor_id', 'category_id', 'brand_id',
        'name', 'slug', 'sku', 'barcode', 'product_type', 'download_url', 'unit',
        'description', 'short_description',
        'purchase_price', 'entrepreneur_profit_percent', 'entrepreneur_profit_flat_rate', 'regular_price', 'sale_price', 'tax_rate',
        'weight', 'length', 'width', 'height',
        'minimum_order_quantity', 'maximum_order_quantity',
        'status', 'featured', 'published_at',
        'meta_title', 'meta_description',
        'created_by', 'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'purchase_price' => 'decimal:2',
            'entrepreneur_profit_percent' => 'decimal:2',
            'entrepreneur_profit_flat_rate' => 'decimal:2',
            'regular_price' => 'decimal:2',
            'sale_price' => 'decimal:2',
            'tax_rate' => 'decimal:2',
            'featured' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'product_category');
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'vendor_id');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->whereNull('product_variant_id');
    }

    public function videos(): HasMany
    {
        return $this->hasMany(ProductVideo::class)->orderBy('sort_order');
    }

    public function downloadFiles(): HasMany
    {
        return $this->hasMany(ProductDownloadFile::class)->orderBy('id');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(ProductReview::class)->where('status', 'approved')->latest('id');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    public function entrepreneurProducts(): HasMany
    {
        return $this->hasMany(EntrepreneurProduct::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Effective selling price for a simple product: sale_price when set and
     * lower than regular_price, otherwise regular_price. Variable products
     * price at the variant level — callers should use
     * ProductVariant::effectivePrice() there instead.
     *
     * This is a *display* convenience only. Rule 6 still applies at
     * checkout (Phase 9): the server always recalculates the authoritative
     * price, this accessor is never trusted as-is for an order total.
     */
    public function getEffectivePriceAttribute(): string
    {
        if ($this->sale_price !== null && bccomp((string) $this->sale_price, (string) $this->regular_price, 2) < 0) {
            return (string) $this->sale_price;
        }

        return (string) $this->regular_price;
    }

    public function isVariable(): bool
    {
        if ($this->product_type === 'variable') {
            return true;
        }

        // Digital products can also be sold as variant combinations (for
        // example, subscription duration and screen count). Treat legacy
        // products with variant rows as variable too, so their options never
        // disappear from the storefront.
        return $this->relationLoaded('variants')
            ? $this->variants->isNotEmpty()
            : $this->variants()->exists();
    }
}
