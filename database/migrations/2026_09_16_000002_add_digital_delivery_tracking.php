<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('digital_license_keys', function (Blueprint $table): void {
            $table->timestamp('email_sent_at')->nullable()->after('download_count');
            $table->string('email_status')->nullable()->after('email_sent_at');
            $table->text('email_error')->nullable()->after('email_status');
            $table->timestamp('revoked_at')->nullable()->after('email_error');
            $table->foreignId('revoked_by')->nullable()->after('revoked_at')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('digital_license_keys', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('revoked_by');
            $table->dropColumn(['email_sent_at', 'email_status', 'email_error', 'revoked_at']);
        });
    }
};
