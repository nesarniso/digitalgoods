<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('entrepreneur_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('entrepreneur_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('status')->default('active');
            $table->decimal('custom_selling_price', 12, 2)->nullable();
            $table->timestamps();

            $table->unique(['entrepreneur_id', 'product_id']);
            $table->index(['entrepreneur_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('entrepreneur_products');
    }
};
