<?php

namespace App\Domain\Commerce\Services;

use App\Domain\Audit\Services\AuditLogger;
use App\Domain\Commerce\Models\Cart;
use App\Domain\Commerce\Models\Coupon;
use App\Domain\Commerce\Models\CouponUsage;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Sole entry point for coupon mutation and validation. Discount
 * *calculation* against a specific cart lives in PricingService (which
 * calls back into this class for eligibility/scope checks) — this class
 * owns the coupon's own rules, not cart arithmetic.
 */
class CouponService
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
    ) {}

    public function create(array $data, User $actor): Coupon
    {
        $coupon = Coupon::create([
            'uuid' => (string) Str::uuid(),
            'code' => strtoupper($data['code']),
            'type' => $data['type'],
            'value' => $data['value'] ?? null,
            'min_order_amount' => $data['min_order_amount'] ?? null,
            'max_discount_amount' => $data['max_discount_amount'] ?? null,
            'usage_limit' => $data['usage_limit'] ?? null,
            'per_user_limit' => $data['per_user_limit'] ?? null,
            'starts_at' => $data['starts_at'] ?? null,
            'expires_at' => $data['expires_at'] ?? null,
            'is_active' => $data['is_active'] ?? true,
            'description' => $data['description'] ?? null,
        ]);

        if (! empty($data['product_ids'])) {
            $coupon->products()->sync($data['product_ids']);
        }

        if (! empty($data['category_ids'])) {
            $coupon->categories()->sync($data['category_ids']);
        }

        $this->auditLogger->log(
            actor: $actor, action: 'created', module: 'Commerce',
            entityType: 'Coupon', entityId: (string) $coupon->id,
            newValue: ['code' => $coupon->code, 'type' => $coupon->type],
        );

        return $coupon;
    }

    public function update(Coupon $coupon, array $data, User $actor): Coupon
    {
        $before = $coupon->only(['value', 'is_active', 'usage_limit']);

        if (isset($data['code'])) {
            $data['code'] = strtoupper($data['code']);
        }

        $coupon->update($data);

        if (array_key_exists('product_ids', $data)) {
            $coupon->products()->sync($data['product_ids'] ?? []);
        }

        if (array_key_exists('category_ids', $data)) {
            $coupon->categories()->sync($data['category_ids'] ?? []);
        }

        $this->auditLogger->log(
            actor: $actor, action: 'updated', module: 'Commerce',
            entityType: 'Coupon', entityId: (string) $coupon->id,
            oldValue: $before, newValue: $coupon->only(['value', 'is_active', 'usage_limit']),
        );

        return $coupon;
    }

    public function delete(Coupon $coupon, User $actor): void
    {
        $coupon->delete();

        $this->auditLogger->log(
            actor: $actor, action: 'deleted', module: 'Commerce',
            entityType: 'Coupon', entityId: (string) $coupon->id,
        );
    }

    /**
     * Applies (attaches) a coupon to a cart after validating it's usable
     * right now, for this user, against this cart's current contents. This
     * is a preview attach, not a redemption — see the coupon_usages
     * migration note. Re-validated on every totals computation
     * (PricingService) since cart contents can change after attach.
     */
    public function applyToCart(Cart $cart, string $code, ?User $user): Coupon
    {
        $coupon = Coupon::query()->where('code', strtoupper($code))->first();

        if (! $coupon) {
            throw ValidationException::withMessages(['code' => ['Coupon code not found.']]);
        }

        $this->assertUsable($coupon, $user);

        $cart->update(['coupon_id' => $coupon->id]);

        return $coupon;
    }

    public function removeFromCart(Cart $cart): void
    {
        $cart->update(['coupon_id' => null]);
    }

    /**
     * Coupon-level eligibility only (active window, global/per-user usage
     * caps). Does NOT check min_order_amount or product/category scope
     * against a specific cart — that's PricingService's job, since it
     * needs the cart's actual contents to answer that.
     */
    public function assertUsable(Coupon $coupon, ?User $user): void
    {
        if (! $coupon->isCurrentlyActive()) {
            throw ValidationException::withMessages(['code' => ['This coupon is not currently active.']]);
        }

        if ($coupon->usage_limit !== null && $coupon->used_count >= $coupon->usage_limit) {
            throw ValidationException::withMessages(['code' => ['This coupon has reached its usage limit.']]);
        }

        if ($coupon->per_user_limit !== null) {
            if (! $user) {
                throw ValidationException::withMessages(['code' => ['This coupon requires an account to use.']]);
            }

            $usedByUser = CouponUsage::query()
                ->where('coupon_id', $coupon->id)
                ->where('user_id', $user->id)
                ->count();

            if ($usedByUser >= $coupon->per_user_limit) {
                throw ValidationException::withMessages(['code' => ['You have already used this coupon the maximum number of times.']]);
            }
        }
    }

    /**
     * Called by OrderService (Phase 11) once an order is actually
     * confirmed — this is the real redemption, incrementing used_count and
     * writing the audit-style usage record. Never called from cart-apply.
     */
    public function redeem(Coupon $coupon, ?User $user, string $discountAmount): CouponUsage
    {
        return DB::transaction(function () use ($coupon, $user, $discountAmount) {
            $locked = Coupon::query()->whereKey($coupon->id)->lockForUpdate()->firstOrFail();

            if (! $locked->isCurrentlyActive()) {
                throw ValidationException::withMessages(['code' => ['This coupon is no longer available.']]);
            }

            if ($locked->usage_limit !== null && $locked->used_count >= $locked->usage_limit) {
                throw ValidationException::withMessages(['code' => ['This coupon has reached its usage limit.']]);
            }

            if ($locked->per_user_limit !== null && $user) {
                $usedByUser = CouponUsage::query()
                    ->where('coupon_id', $locked->id)
                    ->where('user_id', $user->id)
                    ->lockForUpdate()
                    ->count();

                if ($usedByUser >= $locked->per_user_limit) {
                    throw ValidationException::withMessages(['code' => ['You have already used this coupon the maximum number of times.']]);
                }
            }

            $locked->increment('used_count');

            return CouponUsage::create([
                'coupon_id' => $locked->id,
                'user_id' => $user?->id,
                'discount_amount' => $discountAmount,
                'used_at' => now(),
            ]);
        });
    }
}
