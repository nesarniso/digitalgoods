<?php

namespace App\Domain\Payments\Models;

use App\Domain\Orders\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentTransaction extends Model
{
    protected $fillable = [
        'uuid', 'order_id', 'gateway', 'idempotency_key', 'gateway_reference', 'status',
        'amount', 'refunded_amount', 'currency', 'payer_user_id',
        'payer_mobile', 'manual_transaction_id', 'submitted_amount', 'paid_at',
        'raw_response', 'failure_reason',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'refunded_amount' => 'decimal:2',
            'raw_response' => 'array',
            'submitted_amount' => 'decimal:2',
            'paid_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function payer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'payer_user_id');
    }

    public function isTerminal(): bool
    {
        return in_array($this->status, ['paid', 'failed', 'cancelled', 'refunded'], true);
    }

    public function isSuccessful(): bool
    {
        return in_array($this->status, ['paid', 'partially_refunded'], true);
    }
}
