<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('entrepreneur_link_clicks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('entrepreneur_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('attribution_code');
            $table->string('session_key', 64)->nullable();
            $table->timestamps();

            $table->index(['entrepreneur_id', 'product_id']);
            $table->index(['attribution_code', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('entrepreneur_link_clicks');
    }
};
