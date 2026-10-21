<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // order_id is intentionally omitted — Orders don't exist until
        // Phase 11, which will add it via a follow-up migration rather
        // than forward-referencing a table that doesn't exist yet (same
        // pattern as coupon_usages in Phase 7). Every payment gets a
        // transaction record here regardless (Rule: "Every payment must
        // have a transaction record"), and Phase 11 links a transaction
        // to its order once orders exist.
        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();
            $table->string('gateway'); // cash_on_delivery, bank_transfer, stripe, ...
            // Idempotency key: the caller (checkout/order flow) generates
            // this once per payment attempt. A duplicate initiate() call
            // with the same key returns the existing transaction instead
            // of creating a second one — this is the concrete mechanism
            // behind "duplicate webhook must not create duplicate
            // payments" (spec section 78).
            $table->string('idempotency_key')->unique();
            // External reference from the gateway (bKash transaction ID,
            // Stripe payment_intent ID, etc.) — null until the gateway
            // assigns one. Nullable + unique-when-present since manual
            // gateways (COD) may never get one.
            $table->string('gateway_reference')->nullable()->unique();
            $table->enum('status', [
                'initiated', 'pending', 'authorized', 'paid',
                'failed', 'cancelled', 'refunded', 'partially_refunded',
            ])->default('initiated');
            $table->decimal('amount', 12, 2);
            $table->decimal('refunded_amount', 12, 2)->default(0);
            $table->string('currency', 3)->default('BDT');
            $table->foreignId('payer_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->json('raw_response')->nullable();
            $table->text('failure_reason')->nullable();
            $table->timestamps();

            $table->index(['gateway', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_transactions');
    }
};
