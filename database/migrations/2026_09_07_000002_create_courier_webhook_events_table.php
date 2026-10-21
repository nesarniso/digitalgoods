<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('courier_webhook_events', function (Blueprint $table) {
            $table->id();
            $table->string('courier');
            $table->string('event_id');
            $table->json('payload');
            $table->boolean('verified')->default(false);
            $table->timestamps();

            $table->unique(['courier', 'event_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('courier_webhook_events');
    }
};
