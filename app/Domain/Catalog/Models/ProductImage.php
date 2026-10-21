<?php

namespace App\Domain\Catalog\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductImage extends Model
{
    protected $fillable = ['product_id', 'product_variant_id', 'path', 'alt_text', 'title', 'is_thumbnail', 'sort_order'];

    protected function casts(): array
    {
        return ['is_thumbnail' => 'boolean'];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    /**
     * Public URL via the `media` disk abstraction (config/filesystems.php)
     * — never a hardcoded local path, so switching to S3 later needs no
     * change here.
     */
    public function getUrlAttribute(): string
    {
        return url('/storage/media/'.$this->path);
    }
}
