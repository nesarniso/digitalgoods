<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('carts', function (Blueprint $table): void {
            $table->string('checkout_name')->nullable()->after('guest_token');
            $table->string('checkout_phone', 40)->nullable()->after('checkout_name');
            $table->string('checkout_email')->nullable()->after('checkout_phone');
            $table->timestamp('checkout_contacted_at')->nullable()->index()->after('checkout_email');
        });
    }

    public function down(): void
    {
        Schema::table('carts', function (Blueprint $table): void {
            $table->dropColumn(['checkout_name', 'checkout_phone', 'checkout_email', 'checkout_contacted_at']);
        });
    }
};
