<?php

namespace App\Models;

use App\Domain\Commerce\Models\WishlistItem;
use App\Domain\Entrepreneurs\Models\EntrepreneurProduct;
use App\Domain\Entrepreneurs\Models\EntrepreneurProfile;
use App\Domain\Finance\Models\WalletTransaction;
use App\Domain\Finance\Models\Withdrawal;
use App\Domain\Orders\Models\Order;
use App\Domain\Settings\Models\PushSubscription;
use App\Domain\Support\Models\SupportTicket;
use App\Domain\Users\Models\Address;
use App\Domain\Users\Models\CustomerProfile;
use App\Domain\Users\Models\EmployeeProfile;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

/**
 * Base account record. Customer/Vendor/Employee-specific profile data lives
 * in their own domain tables (see architecture doc section 6) — this model
 * intentionally stays thin: identity, auth, and role/permission concerns
 * only. Email verification enabled in Phase 2; phone verification is
 * handled separately via OtpService since Laravel's MustVerifyEmail
 * contract is email-specific. 2FA columns exist (from Phase 1) but TOTP
 * generation/verification is deferred to Phase 23 (Security audit).
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasRoles, Notifiable;

    protected $fillable = [
        'uuid',
        'name',
        'email',
        'phone',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function customerProfile(): HasOne
    {
        return $this->hasOne(CustomerProfile::class);
    }

    public function entrepreneurProfile(): HasOne
    {
        return $this->hasOne(EntrepreneurProfile::class);
    }

    public function entrepreneurProducts(): HasMany
    {
        return $this->hasMany(EntrepreneurProduct::class, 'entrepreneur_id');
    }

    public function walletTransactions(): HasMany
    {
        return $this->hasMany(WalletTransaction::class, 'entrepreneur_id');
    }

    public function withdrawals(): HasMany
    {
        return $this->hasMany(Withdrawal::class, 'entrepreneur_id');
    }

    public function employeeProfile(): HasOne
    {
        return $this->hasOne(EmployeeProfile::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class);
    }

    public function supportTickets(): HasMany
    {
        return $this->hasMany(SupportTicket::class);
    }

    public function wishlistItems(): HasMany
    {
        return $this->hasMany(WishlistItem::class);
    }

    public function pushSubscriptions(): HasMany
    {
        return $this->hasMany(PushSubscription::class);
    }

}
