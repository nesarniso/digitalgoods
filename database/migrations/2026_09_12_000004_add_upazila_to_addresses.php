<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('addresses', function (Blueprint $table): void {
            $table->string('upazila')->nullable()->after('district');
        });

        Schema::table('order_addresses', function (Blueprint $table): void {
            $table->string('upazila')->nullable()->after('district');
        });
    }

    public function down(): void
    {
        Schema::table('order_addresses', fn (Blueprint $table) => $table->dropColumn('upazila'));
        Schema::table('addresses', fn (Blueprint $table) => $table->dropColumn('upazila'));
    }
};
