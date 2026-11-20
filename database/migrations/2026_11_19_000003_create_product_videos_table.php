<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_videos', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->char('source_hash', 64);
            $table->string('source_url', 2048);
            $table->string('path');
            $table->string('title')->nullable();
            $table->string('mime_type', 100);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['product_id', 'source_hash']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_videos');
    }
};
