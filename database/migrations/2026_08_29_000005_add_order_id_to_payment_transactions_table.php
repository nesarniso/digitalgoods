<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // The follow-up migration promised in Phase 10's
        // payment_transactions migration, now that orders exist.
        // order_id is nullable and hasMany-from-Order (not a single
        // payment_transaction_id column on orders) since a single order
        // can have multiple payment attempts — a failed card retried
        // successfully should keep both attempts on record.
        Schema::table('payment_transactions', function (Blueprint $table) {
            $table->foreignId('order_id')->nullable()->after('uuid')->constrained('orders')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('payment_transactions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('order_id');
        });
    }
};
