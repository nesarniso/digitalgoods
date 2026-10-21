<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('entrepreneur_profit_flat_rate', 12, 2)->nullable()->after('entrepreneur_profit_percent');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->decimal('entrepreneur_profit_flat_rate', 12, 2)->nullable()->after('entrepreneur_profit_percent');
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn('entrepreneur_profit_flat_rate');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('entrepreneur_profit_flat_rate');
        });
    }
};
