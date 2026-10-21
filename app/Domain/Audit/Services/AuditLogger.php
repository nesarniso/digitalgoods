<?php

namespace App\Domain\Audit\Services;

use App\Domain\Audit\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Sole entry point for writing audit records (Rule 9: "Every sensitive
 * admin action must be audited"). Every domain service that mutates
 * admin-controlled state (Settings, FeatureFlags, Employees, and later
 * Products/Orders/Vendors/etc.) calls this rather than writing to the
 * audit_logs table directly, so the "what counts as sensitive" decision
 * stays in one place.
 *
 * Writes to both the database (queryable, joins to user) and the
 * `audit` log channel (file-based, tamper-evident secondary record per
 * config/logging.php) — the file trail survives even if someone gets
 * write access to the audit_logs table itself.
 */
class AuditLogger
{
    public function log(
        ?User $actor,
        string $action,
        string $module,
        ?string $entityType = null,
        ?string $entityId = null,
        ?array $oldValue = null,
        ?array $newValue = null,
        ?Request $request = null,
    ): AuditLog {
        $entry = AuditLog::create([
            'user_id' => $actor?->id,
            'role' => $actor?->roles?->pluck('name')->implode(','),
            'ip_address' => $request?->ip(),
            'device' => $request ? (string) $request->userAgent() : null,
            'action' => $action,
            'module' => $module,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'old_value' => $oldValue,
            'new_value' => $newValue,
            'created_at' => now(),
        ]);

        Log::channel('audit')->info("{$module}.{$action}", [
            'actor_id' => $actor?->id,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'old_value' => $oldValue,
            'new_value' => $newValue,
        ]);

        return $entry;
    }
}
