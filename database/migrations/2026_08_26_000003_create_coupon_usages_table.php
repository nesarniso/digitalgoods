<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Written by CouponService::redeem(), which is meant to be called
        // from OrderService once an order is actually confirmed (Phase
        // 11) — not at cart-apply time. Applying a coupon to a cart is
        // just a preview; redemption (incrementing used_count, writing
        // this row) only happens when a real order is placed, so
        // abandoning a cart with a coupon attached never burns a usage
        // slot. order_id is intentionally omitted for now (Orders don't
        // exist until Phase 11) — Phase 11 will add it via a follow-up
        // migration rather than forward-referencing a table that doesn't
        // exist yet.
        Schema::create('coupon_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coupon_id')->constrained('coupons')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('discount_amount', 12, 2);
            $table->timestamp('used_at')->useCurrent();

            $table->index(['coupon_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coupon_usages');
    }
};
