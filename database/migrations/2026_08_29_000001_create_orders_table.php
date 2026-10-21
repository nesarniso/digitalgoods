<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();
            $table->string('order_number')->unique(); // ORD-2026-000001 style, generated from id after insert
            // Client-generated, one per genuine checkout attempt — a
            // double-tap on "Place Order" (network retry, accidental
            // double submit) collapses onto the same order instead of
            // creating two, same idempotency pattern as
            // payment_transactions in Phase 10.
            $table->string('idempotency_key')->unique();

            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('customer_email')->nullable();
            $table->string('customer_phone')->nullable();

            $table->enum('status', [
                'pending', 'confirmed', 'processing', 'packed', 'shipped',
                'out_for_delivery', 'delivered', 'cancelled', 'returned',
                'refunded', 'failed',
            ])->default('pending');

            // Money: DECIMAL(12,2) per Phase 0. All of these are the
            // authoritative, server-computed values from
            // CheckoutService::summarize() at the moment the order was
            // placed — never recalculated from live product prices later
            // (Rule 7: order price snapshot is immutable).
            $table->decimal('subtotal', 12, 2);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('shipping_amount', 12, 2)->default(0);
            $table->decimal('grand_total', 12, 2);

            // Snapshots — the coupon or shipping method could be edited or
            // deleted after the order is placed; the order must keep
            // showing what was actually used at the time.
            $table->string('coupon_code')->nullable();
            $table->string('shipping_method_name')->nullable();
            $table->string('payment_method');

            $table->text('notes')->nullable();
            $table->timestamp('placed_at');
            $table->timestamp('cancelled_at')->nullable();
            $table->string('cancel_reason')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'status']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
