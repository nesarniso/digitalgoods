<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('carts', function (Blueprint $table): void {
            $table->timestamp('abandoned_notified_at')->nullable()->index();
        });
    }

    public function down(): void
    {
        Schema::table('carts', fn (Blueprint $table) => $table->dropColumn('abandoned_notified_at'));
    }
};
