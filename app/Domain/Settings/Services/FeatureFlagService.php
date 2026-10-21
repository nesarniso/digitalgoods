<?php

namespace App\Domain\Settings\Services;

use App\Domain\Audit\Services\AuditLogger;
use App\Domain\Settings\Models\FeatureFlag;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

/**
 * Sole public entry point for reading/toggling feature flags. Domain code
 * that needs to check whether an optional module is active should inject
 * this service (or use the `feature_enabled()` helper defined in
 * AppServiceProvider) rather than querying FeatureFlag directly.
 */
class FeatureFlagService
{
    private const CACHE_KEY = 'feature_flags.all';

    private const CACHE_TTL = 3600;

    public function __construct(
        private readonly AuditLogger $auditLogger,
    ) {}

    public function isEnabled(string $key): bool
    {
        return (bool) $this->all()->get($key, false);
    }

    public function enable(string $key, ?User $actor = null): FeatureFlag
    {
        return $this->toggle($key, true, $actor);
    }

    public function disable(string $key, ?User $actor = null): FeatureFlag
    {
        return $this->toggle($key, false, $actor);
    }

    /**
     * @return \Illuminate\Support\Collection<string, bool>
     */
    public function all(): \Illuminate\Support\Collection
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            return FeatureFlag::query()->pluck('enabled', 'key');
        });
    }

    private function toggle(string $key, bool $enabled, ?User $actor): FeatureFlag
    {
        $flag = FeatureFlag::query()->where('key', $key)->firstOrFail();
        $previous = $flag->enabled;

        $flag->update(['enabled' => $enabled]);

        $this->flush();

        if ($previous !== $enabled) {
            $this->auditLogger->log(
                actor: $actor,
                action: $enabled ? 'enabled' : 'disabled',
                module: 'FeatureFlags',
                entityType: 'FeatureFlag',
                entityId: $key,
                oldValue: ['enabled' => $previous],
                newValue: ['enabled' => $enabled],
            );
        }

        return $flag;
    }

    public function flush(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
