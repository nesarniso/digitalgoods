<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('digital_license_keys', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('reference')->unique();
            $table->text('secret');
            $table->string('secret_hash', 64);
            $table->text('instructions')->nullable();
            $table->enum('status', ['available', 'reserved', 'assigned', 'revoked'])->default('available');
            $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->foreignId('order_item_id')->nullable()->constrained('order_items')->nullOnDelete();
            $table->timestamp('reserved_at')->nullable();
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('viewed_at')->nullable();
            $table->unsignedInteger('download_count')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['product_id', 'status']);
            $table->index('secret_hash');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('digital_license_keys');
    }
};
