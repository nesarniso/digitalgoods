<?php

namespace App\Domain\Catalog\Models;

use App\Domain\Orders\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductReview extends Model
{
    protected $fillable = ['product_id', 'user_id', 'order_id', 'rating', 'title', 'body', 'status', 'verified_purchase'];

    protected function casts(): array
    {
        return ['rating' => 'integer', 'verified_purchase' => 'boolean'];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function getPublicAuthorNameAttribute(): string
    {
        $name = trim((string) $this->user?->name);
        if ($name === '') {
            return 'Customer';
        }

        $parts = preg_split('/\s+/u', $name, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        if (count($parts) < 2) {
            return $parts[0] ?? 'Customer';
        }

        return $parts[0].' '.mb_substr($parts[count($parts) - 1], 0, 1).'.';
    }
}
