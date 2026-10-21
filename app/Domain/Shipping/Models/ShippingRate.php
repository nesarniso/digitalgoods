<?php

namespace App\Domain\Shipping\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShippingRate extends Model
{
    protected $fillable = ['shipping_method_id', 'shipping_zone_id', 'base_rate', 'per_kg_rate', 'free_above_amount'];

    protected function casts(): array
    {
        return [
            'base_rate' => 'decimal:2',
            'per_kg_rate' => 'decimal:2',
            'free_above_amount' => 'decimal:2',
        ];
    }

    public function method(): BelongsTo
    {
        return $this->belongsTo(ShippingMethod::class, 'shipping_method_id');
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(ShippingZone::class, 'shipping_zone_id');
    }
}
