<?php

namespace App\Domain\Settings\Services;

use App\Domain\Audit\Services\AuditLogger;
use App\Domain\Settings\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

/**
 * Sole public entry point for reading/writing dynamic settings. Callers
 * (controllers, other domain services, theme view composers) always go
 * through this service rather than querying the Setting model directly —
 * this is what makes it possible to add a second cache layer, audit
 * logging, or a config-file fallback later without touching call sites.
 */
class SettingService
{
    private const CACHE_KEY = 'settings.all';

    private const CACHE_TTL = 3600;

    public function __construct(
        private readonly AuditLogger $auditLogger,
    ) {}

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->all()->get($key, $default);
    }

    public function boolean(string $key, bool $default = false): bool
    {
        return filter_var($this->get($key, $default), FILTER_VALIDATE_BOOLEAN);
    }

    public function set(string $key, mixed $value, string $group = 'general', string $type = 'string', ?User $actor = null): Setting
    {
        $previous = Setting::query()->where('key', $key)->first();

        $setting = Setting::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'group' => $group, 'type' => $type],
        );

        $this->flush();

        $this->auditLogger->log(
            actor: $actor,
            action: $previous ? 'updated' : 'created',
            module: 'Settings',
            entityType: 'Setting',
            entityId: $key,
            oldValue: $previous ? ['value' => $previous->value] : null,
            newValue: ['value' => $value],
        );

        return $setting;
    }

    /**
     * @return \Illuminate\Support\Collection<string, mixed>
     */
    public function all(): \Illuminate\Support\Collection
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            return Setting::query()->pluck('value', 'key')->map(fn ($value) => $this->normalizeBrandText($value));
        });
    }

    public function group(string $group): \Illuminate\Support\Collection
    {
        return Setting::query()->where('group', $group)->pluck('value', 'key')->map(fn ($value) => $this->normalizeBrandText($value));
    }

    private function normalizeBrandText(mixed $value): mixed
    {
        return is_string($value) ? str_replace(['RPC', 'rpc'], 'eflix', $value) : $value;
    }

    public function flush(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
