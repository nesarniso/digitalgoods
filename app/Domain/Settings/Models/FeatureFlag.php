<?php

namespace App\Domain\Settings\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Toggles optional modules (wishlist, wallet, loyalty, multi_vendor, pos,
 * etc.) per spec section 75. Business_mode (single/multi vendor) is stored
 * here too as `multi_vendor`, defaulting to false per your Phase 0 decision
 * — the schema stays vendor-ready even though the feature starts disabled.
 */
class FeatureFlag extends Model
{
    protected $fillable = [
        'key',
        'enabled',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'enabled' => 'boolean',
        ];
    }
}
