<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Unlimited nesting via adjacency list (parent_id self-reference).
        // Simpler than nested-set/materialized-path and sufficient for a
        // catalog tree of the size these clients run — revisit only if a
        // deep-tree read pattern (e.g. "all descendants" on every request)
        // becomes a proven bottleneck.
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();
            $table->foreignId('parent_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('image_path')->nullable();
            $table->string('banner_path')->nullable();
            $table->string('icon_path')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->string('meta_title')->nullable();
            $table->string('meta_description')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['parent_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
