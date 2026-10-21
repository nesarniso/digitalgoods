<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->decimal('cost_price', 12, 2)->nullable()->after('unit_price');
            $table->decimal('applicable_cost', 12, 2)->default(0)->after('cost_price');
            $table->decimal('profit', 12, 2)->default(0)->after('applicable_cost');
            $table->string('profit_status')->default('none')->after('profit');
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn(['cost_price', 'applicable_cost', 'profit', 'profit_status']);
        });
    }
};
