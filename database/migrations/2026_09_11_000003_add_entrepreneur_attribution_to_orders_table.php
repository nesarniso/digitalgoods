<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('entrepreneur_id')->nullable()->after('user_id')->constrained('users')->nullOnDelete();
            $table->string('source')->default('direct')->after('status');
            $table->string('attribution_code')->nullable()->after('source');

            $table->index(['entrepreneur_id', 'status']);
            $table->index(['attribution_code', 'source']);
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['entrepreneur_id']);
            $table->dropIndex(['entrepreneur_id', 'status']);
            $table->dropIndex(['attribution_code', 'source']);
            $table->dropColumn(['entrepreneur_id', 'source', 'attribution_code']);
        });
    }
};
