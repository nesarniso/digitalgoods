<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Spec section 19. Not previously built in an earlier phase
        // despite being core — Shipping (this phase) is the first module
        // that actually needs a real address to calculate a rate against,
        // so it's built here rather than earlier as a speculative
        // dependency. Checkout (Phase 9) and Orders (Phase 11) will both
        // reference this table.
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('type', ['shipping', 'billing', 'both'])->default('both');
            $table->string('label')->nullable(); // "Home", "Office"...
            $table->string('name');
            $table->string('phone');
            $table->string('country')->default('Bangladesh');
            $table->string('division')->nullable();
            $table->string('district')->nullable();
            $table->string('area')->nullable();
            $table->string('postal_code')->nullable();
            $table->text('address_line');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('addresses');
    }
};
