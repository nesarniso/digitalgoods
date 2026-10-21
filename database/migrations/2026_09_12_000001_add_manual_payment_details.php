<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_transactions', function (Blueprint $table) {
            $table->string('payer_mobile', 30)->nullable()->after('payer_user_id');
            $table->string('manual_transaction_id', 100)->nullable()->unique()->after('payer_mobile');
            $table->decimal('submitted_amount', 12, 2)->nullable()->after('manual_transaction_id');
            $table->timestamp('paid_at')->nullable()->after('submitted_amount');
            $table->index(['gateway', 'manual_transaction_id']);
        });
    }

    public function down(): void
    {
        Schema::table('payment_transactions', function (Blueprint $table) {
            $table->dropIndex(['gateway', 'manual_transaction_id']);
            $table->dropUnique(['manual_transaction_id']);
            $table->dropColumn(['payer_mobile', 'manual_transaction_id', 'submitted_amount', 'paid_at']);
        });
    }
};
