<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // API-first cart identity: authenticated carts key on user_id (one
        // cart per customer), guest carts key on a client-managed
        // guest_token (sent as X-Guest-Cart-Token) rather than a server
        // session — this is what makes the same cart endpoints work for
        // both the storefront and the Flutter app without relying on
        // cookies.
        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();
            $table->foreignId('user_id')->nullable()->unique()->constrained('users')->cascadeOnDelete();
            $table->string('guest_token')->nullable()->unique();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('carts');
    }
};
