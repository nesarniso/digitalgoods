<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Append-only ledger — never updated after insert. Every stock
        // mutation (adjustment, reservation, release, sale, restock,
        // damaged, returned, transfer) writes exactly one row here, so the
        // full history of an inventory row can always be reconstructed.
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_id')->constrained('inventories')->cascadeOnDelete();
            $table->enum('type', [
                'adjustment', 'reservation', 'release', 'sale',
                'restock', 'damaged', 'returned', 'transfer_in', 'transfer_out',
            ]);
            $table->integer('quantity'); // signed delta: +10 restock, -1 sale
            $table->unsignedInteger('physical_stock_after');
            $table->unsignedInteger('reserved_stock_after');
            $table->string('reason')->nullable();
            $table->string('reference_type')->nullable(); // e.g. Order
            $table->string('reference_id')->nullable();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['inventory_id', 'created_at']);
            $table->index(['reference_type', 'reference_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
