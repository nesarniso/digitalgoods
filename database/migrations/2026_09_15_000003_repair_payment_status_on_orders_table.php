<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('orders') || Schema::hasColumn('orders', 'payment_status')) {
            return;
        }

        Schema::table('orders', function (Blueprint $table): void {
            $table->string('payment_status', 30)->default('pending')->after('payment_method');
            $table->index('payment_status');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('orders') || ! Schema::hasColumn('orders', 'payment_status')) {
            return;
        }

        Schema::table('orders', function (Blueprint $table): void {
            $table->dropIndex(['payment_status']);
            $table->dropColumn('payment_status');
        });
    }
};
