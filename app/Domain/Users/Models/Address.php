<?php

namespace App\Domain\Users\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Address extends Model
{
    protected $fillable = [
        'uuid', 'user_id', 'type', 'label', 'name', 'phone',
        'country', 'division', 'district', 'upazila', 'area', 'postal_code',
        'address_line', 'latitude', 'longitude', 'is_default',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'is_default' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
