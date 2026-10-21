<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // No price column here on purpose: cart line totals are always
        // computed live from the current Product/ProductVariant price at
        // read time (Rule 6 — never trust a client-submitted price, and by
        // extension never persist a price the server didn't just compute).
        // The order snapshot (Phase 11) is where a price gets frozen, not
        // the cart.
        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cart_id')->constrained('carts')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('product_variant_id')->nullable()->constrained('product_variants')->cascadeOnDelete();
            $table->unsignedInteger('quantity');
            $table->timestamps();

            // Same NULL-uniqueness caveat as inventories: enforced at the
            // application layer (CartService) inside a transaction, not
            // solely by this index.
            $table->unique(['cart_id', 'product_id', 'product_variant_id'], 'cart_items_cart_product_variant_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cart_items');
    }
};
