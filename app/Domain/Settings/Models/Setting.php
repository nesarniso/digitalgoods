<?php

namespace App\Domain\Settings\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Dynamic key/value settings (spec section 66). Grouped so the admin panel
 * (Phase 7) can render per-group settings screens, and `is_public` controls
 * whether a value is safe to expose through the public API/storefront
 * (e.g. store name) versus admin-only (e.g. gateway secrets — though
 * genuine secrets belong in .env, not this table).
 */
class Setting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
        'is_public',
    ];

    protected function casts(): array
    {
        return [
            'is_public' => 'boolean',
        ];
    }
}
