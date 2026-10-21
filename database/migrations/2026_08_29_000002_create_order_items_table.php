<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Immutable snapshot per Rule 7 and the architecture doc's own
        // note from Phase 4: product_id/product_variant_id are nullable
        // and exist ONLY for "buy again" navigation — they are NEVER used
        // to look up current price/name for display. Every display field
        // (name, sku, unit_price) is copied at order-creation time and
        // never touched again, even if the product changes or is deleted.
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->foreignId('product_variant_id')->nullable()->constrained('product_variants')->nullOnDelete();

            $table->string('product_name');
            $table->string('sku');
            $table->string('variant_label')->nullable(); // e.g. "Color: Black, Size: M"

            $table->decimal('unit_price', 12, 2);
            $table->unsignedInteger('quantity');
            $table->decimal('line_total', 12, 2);

            // Recorded at order-placement time — cancellation releases
            // stock ONLY for items where this is true, rather than
            // re-deriving "was this tracked" later (which could give a
            // wrong answer if inventory tracking was added/removed for
            // the product in between placement and cancellation).
            $table->boolean('stock_reserved')->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
