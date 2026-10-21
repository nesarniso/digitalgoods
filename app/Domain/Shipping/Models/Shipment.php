<?php

namespace App\Domain\Shipping\Models;

use App\Domain\Orders\Models\Order;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Shipment extends Model
{
    protected $fillable = [
        'uuid', 'order_id', 'shipment_number', 'courier', 'tracking_number', 'tracking_url',
        'status', 'delivery_fee', 'notes', 'metadata', 'picked_up_at', 'dispatched_at',
        'delivered_at', 'failed_at', 'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'delivery_fee' => 'decimal:2',
            'metadata' => 'array',
            'picked_up_at' => 'datetime',
            'dispatched_at' => 'datetime',
            'delivered_at' => 'datetime',
            'failed_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function isTerminal(): bool
    {
        return in_array($this->status, ['delivered', 'failed', 'cancelled'], true);
    }

    public function isInTransit(): bool
    {
        return in_array($this->status, ['pending_dispatch', 'picked_up', 'in_transit', 'out_for_delivery'], true);
    }
}
