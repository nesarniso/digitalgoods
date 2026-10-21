<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('digital_license_keys', function (Blueprint $table): void {
            $table->foreignId('product_variant_id')
                ->nullable()
                ->after('product_id')
                ->constrained('product_variants')
                ->nullOnDelete();
            $table->index(['product_id', 'product_variant_id', 'status'], 'digital_license_variant_stock_index');
        });
    }

    public function down(): void
    {
        Schema::table('digital_license_keys', function (Blueprint $table): void {
            $table->dropIndex('digital_license_variant_stock_index');
            $table->dropConstrainedForeignId('product_variant_id');
        });
    }
};
