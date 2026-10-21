<?php

namespace App\Domain\Digital\Models;

use App\Domain\Catalog\Models\Product;
use App\Domain\Orders\Models\Order;
use App\Domain\Orders\Models\OrderItem;
use App\Domain\Catalog\Models\ProductVariant;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Crypt;

class DigitalLicenseKey extends Model
{
    protected $fillable = [
        'product_id', 'product_variant_id', 'reference', 'secret', 'secret_hash', 'instructions',
        'status', 'order_id', 'order_item_id', 'reserved_at', 'assigned_at',
        'viewed_at', 'download_count', 'created_by',
        'email_sent_at', 'email_status', 'email_error', 'revoked_at', 'revoked_by',
    ];

    protected $hidden = ['secret', 'secret_hash'];

    protected function casts(): array
    {
        return [
            'reserved_at' => 'datetime',
            'assigned_at' => 'datetime',
            'viewed_at' => 'datetime',
            'email_sent_at' => 'datetime',
            'revoked_at' => 'datetime',
        ];
    }

    public function setSecretAttribute(string $value): void
    {
        $this->attributes['secret'] = Crypt::encryptString($value);
        $this->attributes['secret_hash'] = hash('sha256', mb_strtolower(trim($value)));
    }

    public function getPlainSecretAttribute(): string
    {
        return Crypt::decryptString($this->attributes['secret']);
    }

    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
    public function variant(): BelongsTo { return $this->belongsTo(ProductVariant::class, 'product_variant_id'); }
    public function order(): BelongsTo { return $this->belongsTo(Order::class); }
    public function orderItem(): BelongsTo { return $this->belongsTo(OrderItem::class); }
    public function createdBy(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function revokedBy(): BelongsTo { return $this->belongsTo(User::class, 'revoked_by'); }
}
