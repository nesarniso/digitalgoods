<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // A zone matches an address when every non-null field here equals
        // the corresponding address field — null means "any". So a zone
        // with only country='Bangladesh' set matches the whole country;
        // one with country+division+district set matches only that
        // district. ShippingService picks the most specific match (most
        // non-null fields) when several zones could match the same
        // address.
        Schema::create('shipping_zones', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();
            $table->string('name');
            $table->string('country')->nullable();
            $table->string('division')->nullable();
            $table->string('district')->nullable();
            $table->string('area')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipping_zones');
    }
};
