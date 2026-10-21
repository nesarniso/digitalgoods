<?php

namespace App\Domain\Entrepreneurs\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EntrepreneurLinkClick extends Model
{
    protected $fillable = [
        'entrepreneur_id',
        'product_id',
        'attribution_code',
        'session_key',
    ];

    public function entrepreneur(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'entrepreneur_id');
    }
}
