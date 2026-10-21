<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // shipping_zone_id nullable = the catch-all/default rate for this
        // method, used when no more specific zone rate exists for the
        // destination address. Same NULL-uniqueness caveat as inventories/
        // cart_items — enforced at the application layer in
        // ShippingService, not solely by the DB constraint.
        Schema::create('shipping_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipping_method_id')->constrained('shipping_methods')->cascadeOnDelete();
            $table->foreignId('shipping_zone_id')->nullable()->constrained('shipping_zones')->cascadeOnDelete();
            $table->decimal('base_rate', 12, 2)->default(0);
            $table->decimal('per_kg_rate', 12, 2)->nullable(); // weight_based only
            $table->decimal('free_above_amount', 12, 2)->nullable(); // price_based / free threshold

            $table->timestamps();

            $table->unique(['shipping_method_id', 'shipping_zone_id'], 'shipping_rates_method_zone_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipping_rates');
    }
};
