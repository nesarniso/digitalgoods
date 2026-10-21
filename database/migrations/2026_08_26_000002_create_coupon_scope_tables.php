<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Empty scope (no rows for a coupon in either table) means the
        // coupon applies to the entire cart — CouponService checks this
        // explicitly rather than treating "no rows" as "applies to
        // nothing".
        Schema::create('coupon_products', function (Blueprint $table) {
            $table->foreignId('coupon_id')->constrained('coupons')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->primary(['coupon_id', 'product_id']);
        });

        Schema::create('coupon_categories', function (Blueprint $table) {
            $table->foreignId('coupon_id')->constrained('coupons')->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
            $table->primary(['coupon_id', 'category_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coupon_categories');
        Schema::dropIfExists('coupon_products');
    }
};
