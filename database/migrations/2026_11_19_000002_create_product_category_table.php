<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_category', function (Blueprint $table): void {
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->primary(['product_id', 'category_id']);
        });

        DB::table('products')
            ->whereNotNull('category_id')
            ->orderBy('id')
            ->chunkById(500, function ($products): void {
                DB::table('product_category')->insertOrIgnore(
                    $products->map(fn ($product): array => [
                        'product_id' => $product->id,
                        'category_id' => $product->category_id,
                    ])->all(),
                );
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_category');
    }
};
