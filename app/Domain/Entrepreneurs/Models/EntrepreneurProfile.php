<?php

namespace App\Domain\Entrepreneurs\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EntrepreneurProfile extends Model
{
    protected $fillable = [
        'user_id', 'entrepreneur_code', 'status', 'address',
        'division', 'district', 'upazila', 'union_or_ward',
        'referred_by_user_id', 'verified_at',
    ];

    protected function casts(): array
    {
        return ['verified_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function referredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referred_by_user_id');
    }

    public function products(): HasMany
    {
        return $this->hasMany(EntrepreneurProduct::class, 'entrepreneur_id', 'user_id');
    }
}
