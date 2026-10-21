<?php

namespace App\Domain\Analytics\Models;

use Illuminate\Database\Eloquent\Model;

class AnalyticsEvent extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'visitor_id',
        'user_id',
        'product_id',
        'event_type',
        'path',
        'occurred_at',
    ];

    protected function casts(): array
    {
        return ['occurred_at' => 'datetime'];
    }

    public function product()
    {
        return $this->belongsTo(\App\Domain\Catalog\Models\Product::class);
    }
}
