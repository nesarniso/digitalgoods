<?php

namespace App\Domain\Finance\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WalletTransaction extends Model
{
    protected $fillable = [
        'entrepreneur_id', 'type', 'reference_type', 'reference_id',
        'amount', 'status', 'description',
    ];

    protected function casts(): array
    {
        return ['amount' => 'decimal:2'];
    }

    public function entrepreneur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'entrepreneur_id');
    }
}
