<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();

            // Vendor-ready per Phase 0 decision: nullable now (single-vendor
            // launch), populated once Phase 14 (Marketplace) exists. Not a
            // breaking change to add data to a nullable column later.
            $table->foreignId('vendor_id')->nullable()->constrained('users')->nullOnDelete();

            $table->foreignId('category_id')->constrained('categories')->restrictOnDelete();
            $table->foreignId('brand_id')->nullable()->constrained('brands')->nullOnDelete();

            $table->string('name');
            $table->string('slug')->unique();
            $table->string('sku')->unique();
            $table->string('barcode')->nullable()->unique();

            // simple: single price/stock, no variants.
            // variable: has product_variants rows; price/stock live on the
            //   variant, not here (Rule: variant price/stock independent).
            // digital: no physical shipping, gated by the digital_products
            //   feature flag — file/download fields land in Phase 6.
            $table->enum('product_type', ['simple', 'variable', 'digital'])->default('simple');

            $table->string('unit')->nullable(); // pcs, kg, litre...
            $table->text('description')->nullable();
            $table->text('short_description')->nullable();

            // Money: DECIMAL(12,2) per Phase 0 decision (standard fiat, not
            // the crypto-grade 20,8 from the original spec draft).
            $table->decimal('purchase_price', 12, 2)->nullable();
            $table->decimal('regular_price', 12, 2);
            $table->decimal('sale_price', 12, 2)->nullable();
            $table->decimal('tax_rate', 5, 2)->default(0); // percent; real tax engine is Phase 7

            $table->decimal('weight', 10, 3)->nullable();
            $table->decimal('length', 10, 3)->nullable();
            $table->decimal('width', 10, 3)->nullable();
            $table->decimal('height', 10, 3)->nullable();

            $table->unsignedInteger('minimum_order_quantity')->default(1);
            $table->unsignedInteger('maximum_order_quantity')->nullable();

            $table->enum('status', ['draft', 'pending_review', 'published', 'rejected'])->default('draft');
            $table->boolean('featured')->default(false);
            $table->timestamp('published_at')->nullable();

            $table->string('meta_title')->nullable();
            $table->string('meta_description')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['category_id', 'status']);
            $table->index(['vendor_id', 'status']);
            $table->index('featured');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
