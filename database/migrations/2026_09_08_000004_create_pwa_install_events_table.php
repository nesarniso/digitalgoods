<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pwa_install_events', function (Blueprint $table) {
            $table->id();
            $table->string('event', 30);
            $table->string('device_id', 100)->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('platform', 30)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->ipAddress('ip_address')->nullable();
            $table->timestamps();
            $table->index(['event', 'created_at']);
            $table->index(['device_id', 'event']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pwa_install_events');
    }
};
