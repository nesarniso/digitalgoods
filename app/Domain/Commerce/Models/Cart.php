<?php

namespace App\Domain\Commerce\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cart extends Model
{
    protected $fillable = [
        'uuid', 'user_id', 'guest_token', 'coupon_id', 'expires_at',
        'checkout_name', 'checkout_phone', 'checkout_email', 'checkout_contacted_at',
    ];

    protected function casts(): array
    {
        return ['expires_at' => 'datetime', 'checkout_contacted_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function isGuest(): bool
    {
        return $this->user_id === null;
    }
}
