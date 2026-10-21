<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('marketing_contact_lists', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedInteger('contact_count')->default(0);
            $table->timestamps();
        });

        Schema::create('marketing_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contact_list_id')->constrained('marketing_contact_lists')->cascadeOnDelete();
            $table->string('name')->nullable();
            $table->string('email');
            $table->timestamps();
            $table->unique(['contact_list_id', 'email']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marketing_contacts');
        Schema::dropIfExists('marketing_contact_lists');
    }
};
