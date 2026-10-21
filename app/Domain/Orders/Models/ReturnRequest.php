<?php

namespace App\Domain\Orders\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReturnRequest extends Model
{
    protected $fillable = [
        'uuid', 'request_number', 'order_id', 'order_item_id', 'user_id',
        'quantity', 'reason', 'customer_note', 'admin_note', 'status',
        'requested_at', 'resolved_at',
    ];

    protected function casts(): array
    {
        return ['requested_at' => 'datetime', 'resolved_at' => 'datetime'];
    }

    public function order(): BelongsTo { return $this->belongsTo(Order::class); }
    public function item(): BelongsTo { return $this->belongsTo(OrderItem::class, 'order_item_id'); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}
