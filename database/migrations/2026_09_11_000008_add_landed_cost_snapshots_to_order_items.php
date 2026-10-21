<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->decimal('supplier_cost', 12, 2)->nullable()->after('cost_price');
            $table->decimal('delivery_cost', 12, 2)->default(0)->after('supplier_cost');
            $table->decimal('return_cost', 12, 2)->default(0)->after('delivery_cost');
            $table->decimal('entrepreneur_profit', 12, 2)->nullable()->after('return_cost');
            $table->decimal('entrepreneur_profit_percent', 5, 2)->nullable()->after('entrepreneur_profit');
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn([
                'supplier_cost',
                'delivery_cost',
                'return_cost',
                'entrepreneur_profit',
                'entrepreneur_profit_percent',
            ]);
        });
    }
};
