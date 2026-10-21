<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->string('shipment_number')->unique();
            $table->string('courier')->default('manual');
            $table->string('tracking_number')->nullable();
            $table->string('tracking_url')->nullable();
            $table->enum('status', [
                'pending_dispatch',
                'picked_up',
                'in_transit',
                'out_for_delivery',
                'delivered',
                'failed',
                'cancelled',
            ])->default('pending_dispatch');
            $table->decimal('delivery_fee', 12, 2)->default(0);
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('picked_up_at')->nullable();
            $table->timestamp('dispatched_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();

            $table->index(['order_id', 'status']);
            $table->index('courier');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipments');
    }
};
