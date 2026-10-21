<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();
            $table->string('code')->unique();
            $table->enum('type', ['percentage', 'fixed', 'free_shipping']);
            // Nullable: free_shipping coupons have no monetary value of
            // their own — the discount they grant is on shipping (Phase 8
            // territory), not on this field.
            $table->decimal('value', 12, 2)->nullable();
            $table->decimal('min_order_amount', 12, 2)->nullable();
            $table->decimal('max_discount_amount', 12, 2)->nullable();
            $table->unsignedInteger('usage_limit')->nullable(); // global cap; null = unlimited
            $table->unsignedInteger('per_user_limit')->nullable(); // null = unlimited per user
            $table->unsignedInteger('used_count')->default(0);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};
