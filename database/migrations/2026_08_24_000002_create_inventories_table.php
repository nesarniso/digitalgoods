<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // available_stock is intentionally NOT a stored column — it's
        // always derived as physical_stock - reserved_stock at the
        // application layer (InventoryService/Inventory model accessor).
        // Storing a derived value invites drift; every write to
        // physical_stock or reserved_stock goes through InventoryService,
        // which keeps them consistent under a row lock.
        Schema::create('inventories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            // Nullable: simple products have no variant row (Phase 4 only
            // creates variants for product_type=variable). NULL here means
            // "the product itself", not "a specific variant".
            $table->foreignId('product_variant_id')->nullable()->constrained('product_variants')->cascadeOnDelete();
            $table->foreignId('warehouse_id')->constrained('warehouses')->cascadeOnDelete();

            $table->unsignedInteger('physical_stock')->default(0);
            $table->unsignedInteger('reserved_stock')->default(0);
            $table->unsignedInteger('damaged_stock')->default(0);
            $table->unsignedInteger('low_stock_threshold')->default(5);

            $table->timestamps();

            // Note: NULL is not treated as equal to itself in a unique
            // index on MySQL/sqlite, so this constraint alone can't fully
            // prevent duplicate rows for simple products (product_variant_id
            // always NULL). Uniqueness for that case is enforced in
            // InventoryService::firstOrCreateInventory() via lockForUpdate
            // inside a transaction instead.
            $table->unique(['product_id', 'product_variant_id', 'warehouse_id'], 'inventories_product_variant_warehouse_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventories');
    }
};
