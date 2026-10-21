<?php

namespace App\Domain\Orders\Models;

use App\Domain\Payments\Models\PaymentTransaction;
use App\Domain\Digital\Models\DigitalLicenseKey;
use App\Domain\Shipping\Models\Shipment;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'uuid', 'order_number', 'idempotency_key', 'user_id',
        'entrepreneur_id', 'source', 'attribution_code',
        'customer_email', 'customer_phone', 'status',
        'subtotal', 'discount_amount', 'tax_amount', 'shipping_amount', 'grand_total',
        'coupon_code', 'shipping_method_name', 'payment_method', 'payment_status',
        'payment_review_required', 'payment_review_reason',
        'notes', 'placed_at', 'cancelled_at', 'cancel_reason',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'shipping_amount' => 'decimal:2',
            'grand_total' => 'decimal:2',
            'placed_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'payment_review_required' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function entrepreneur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'entrepreneur_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(OrderAddress::class);
    }

    public function shippingAddress(): HasOne
    {
        return $this->hasOne(OrderAddress::class)->where('type', 'shipping');
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class)->orderBy('created_at');
    }

    public function paymentTransactions(): HasMany
    {
        return $this->hasMany(PaymentTransaction::class);
    }

    public function shipments(): HasMany
    {
        return $this->hasMany(Shipment::class);
    }

    public function latestShipment(): HasOne
    {
        return $this->hasOne(Shipment::class)->latestOfMany();
    }

    public function latestPaymentTransaction(): HasOne
    {
        return $this->hasOne(PaymentTransaction::class)->latestOfMany();
    }

    public function digitalLicenses(): HasMany
    {
        return $this->hasMany(DigitalLicenseKey::class);
    }

    public function isPaid(): bool
    {
        return in_array($this->payment_status, ['paid', 'partially_refunded'], true);
    }

    public function isDigitalOnly(): bool
    {
        return $this->items()
            ->whereHas('product', fn ($query) => $query->where('product_type', 'digital'))
            ->exists()
            && ! $this->items()
                ->whereHas('product', fn ($query) => $query->where('product_type', '!=', 'digital'))
                ->exists();
    }

    public function returnRequests(): HasMany
    {
        return $this->hasMany(ReturnRequest::class);
    }

    public function isCancellable(): bool
    {
        return in_array($this->status, ['pending', 'confirmed', 'processing'], true);
    }
}
