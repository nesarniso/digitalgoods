<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('installer_state', function (Blueprint $table): void {
            $table->id();
            $table->string('version', 32);
            $table->timestamp('installed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('installer_update_history', function (Blueprint $table): void {
            $table->id();
            $table->string('from_version', 32)->nullable();
            $table->string('to_version', 32);
            $table->string('status', 20);
            $table->string('backup_path')->nullable();
            $table->text('message')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });

        // Existing deployments receive a baseline instead of being treated as
        // a fresh install when this migration is first introduced.
        if (Schema::hasTable('users') && DB::table('users')->exists()) {
            $metadata = json_decode((string) file_get_contents(base_path('version.json')), true);
            DB::table('installer_state')->insert([
                'id' => 1,
                'version' => (string) ($metadata['version'] ?? '0.0.0'),
                'installed_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('installer_update_history');
        Schema::dropIfExists('installer_state');
    }
};
