<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Append-only trail of sensitive admin actions (spec section 64,
        // Rule 9). Old/new values stored as JSON so any entity's diff can
        // be recorded without a per-module audit table.
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('role')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('device')->nullable();
            $table->string('action'); // created, updated, deleted, enabled, disabled, approved, etc.
            $table->string('module'); // Settings, FeatureFlags, Employees, Products, Orders...
            $table->string('entity_type')->nullable();
            $table->string('entity_id')->nullable();
            $table->json('old_value')->nullable();
            $table->json('new_value')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['module', 'entity_type', 'entity_id']);
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
