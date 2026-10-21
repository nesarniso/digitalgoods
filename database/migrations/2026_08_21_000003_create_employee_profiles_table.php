<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Profile data for admin/staff accounts (Manager, Product Manager,
        // Order Manager, Support Agent, Accountant, etc. — spec section 4).
        // Role/permission assignment itself lives in spatie's pivot tables,
        // not here; this only holds employment metadata.
        Schema::create('employee_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->string('employee_id')->unique()->nullable();
            $table->string('department')->nullable();
            $table->string('designation')->nullable();
            $table->foreignId('reports_to_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_profiles');
    }
};
