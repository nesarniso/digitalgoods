<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // A variant's price/sale_price/purchase_price/weight are fully
        // independent of the parent product and of sibling variants, per
        // spec section 9 ("একটি variant-এর price/stock অন্য variant-এর
        // সাথে independent হবে"). Stock itself is NOT here — that's owned
        // by Domain/Inventory (Phase 5), keyed on product_variant_id.
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('sku')->unique();
            $table->string('barcode')->nullable()->unique();
            $table->decimal('purchase_price', 12, 2)->nullable();
            $table->decimal('price', 12, 2);
            $table->decimal('sale_price', 12, 2)->nullable();
            $table->decimal('weight', 10, 3)->nullable();
            $table->string('image_path')->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('product_variant_attribute_value', function (Blueprint $table) {
            $table->foreignId('product_variant_id')->constrained('product_variants')->cascadeOnDelete();
            $table->foreignId('attribute_value_id')->constrained('attribute_values')->cascadeOnDelete();
            $table->primary(['product_variant_id', 'attribute_value_id'], 'variant_attr_value_primary');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_variant_attribute_value');
        Schema::dropIfExists('product_variants');
    }
};
