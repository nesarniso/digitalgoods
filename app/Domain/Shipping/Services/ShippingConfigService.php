<?php

namespace App\Domain\Shipping\Services;

use App\Domain\Audit\Services\AuditLogger;
use App\Domain\Shipping\Models\ShippingMethod;
use App\Domain\Shipping\Models\ShippingRate;
use App\Domain\Shipping\Models\ShippingZone;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ShippingConfigService
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
    ) {}

    public function createZone(array $data, User $actor): ShippingZone
    {
        $zone = ShippingZone::create([
            'uuid' => (string) Str::uuid(),
            'name' => $data['name'],
            'country' => $data['country'] ?? null,
            'division' => $data['division'] ?? null,
            'district' => $data['district'] ?? null,
            'area' => $data['area'] ?? null,
            'is_active' => $data['is_active'] ?? true,
        ]);

        $this->auditLogger->log(
            actor: $actor, action: 'created', module: 'Shipping',
            entityType: 'ShippingZone', entityId: (string) $zone->id,
            newValue: ['name' => $zone->name],
        );

        return $zone;
    }

    public function updateZone(ShippingZone $zone, array $data, User $actor): ShippingZone
    {
        $before = $zone->only(['name', 'is_active']);
        $zone->update($data);

        $this->auditLogger->log(
            actor: $actor, action: 'updated', module: 'Shipping',
            entityType: 'ShippingZone', entityId: (string) $zone->id,
            oldValue: $before, newValue: $zone->only(['name', 'is_active']),
        );

        return $zone;
    }

    public function deleteZone(ShippingZone $zone, User $actor): void
    {
        $snapshot = $zone->only(['id', 'name', 'country', 'division', 'district', 'area', 'is_active']);

        DB::transaction(function () use ($zone): void {
            $zone->delete();
        });

        $this->auditLogger->log(
            actor: $actor,
            action: 'deleted',
            module: 'Shipping',
            entityType: 'ShippingZone',
            entityId: (string) $snapshot['id'],
            oldValue: $snapshot,
        );
    }

    public function createMethod(array $data, User $actor): ShippingMethod
    {
        $method = ShippingMethod::create([
            'uuid' => (string) Str::uuid(),
            'name' => $data['name'],
            'type' => $data['type'],
            'is_active' => $data['is_active'] ?? true,
            'sort_order' => $data['sort_order'] ?? 0,
        ]);

        $this->auditLogger->log(
            actor: $actor, action: 'created', module: 'Shipping',
            entityType: 'ShippingMethod', entityId: (string) $method->id,
            newValue: ['name' => $method->name, 'type' => $method->type],
        );

        return $method;
    }

    public function updateMethod(ShippingMethod $method, array $data, User $actor): ShippingMethod
    {
        $before = $method->only(['name', 'is_active']);
        $method->update($data);

        $this->auditLogger->log(
            actor: $actor, action: 'updated', module: 'Shipping',
            entityType: 'ShippingMethod', entityId: (string) $method->id,
            oldValue: $before, newValue: $method->only(['name', 'is_active']),
        );

        return $method;
    }

    public function deleteMethod(ShippingMethod $method, User $actor): void
    {
        $snapshot = $method->only(['id', 'name', 'type', 'is_active']);

        DB::transaction(function () use ($method): void {
            $method->delete();
        });

        $this->auditLogger->log(
            actor: $actor,
            action: 'deleted',
            module: 'Shipping',
            entityType: 'ShippingMethod',
            entityId: (string) $snapshot['id'],
            oldValue: $snapshot,
        );
    }

    /**
     * Upsert: one rate row per (method, zone) pair. `zone_id: null` sets
     * the method's catch-all/default rate.
     */
    public function setRate(ShippingMethod $method, ?int $zoneId, array $data, User $actor): ShippingRate
    {
        $rate = ShippingRate::query()->updateOrCreate(
            ['shipping_method_id' => $method->id, 'shipping_zone_id' => $zoneId],
            [
                'base_rate' => $data['base_rate'] ?? 0,
                'per_kg_rate' => $data['per_kg_rate'] ?? null,
                'free_above_amount' => $data['free_above_amount'] ?? null,
            ],
        );

        $this->auditLogger->log(
            actor: $actor, action: 'rate_set', module: 'Shipping',
            entityType: 'ShippingRate', entityId: (string) $rate->id,
            newValue: ['method_id' => $method->id, 'zone_id' => $zoneId, 'base_rate' => $rate->base_rate],
        );

        return $rate;
    }
}
