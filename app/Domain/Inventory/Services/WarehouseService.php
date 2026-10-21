<?php

namespace App\Domain\Inventory\Services;

use App\Domain\Audit\Services\AuditLogger;
use App\Domain\Inventory\Models\Warehouse;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class WarehouseService
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
    ) {}

    public function create(array $data, User $actor): Warehouse
    {
        return DB::transaction(function () use ($data, $actor) {
            $isDefault = $data['is_default'] ?? false;

            if ($isDefault) {
                $this->unsetExistingDefault();
            }

            // The very first warehouse is always the default, regardless
            // of what was passed — the system should never end up with
            // zero default warehouses.
            if (! $isDefault && ! Warehouse::query()->exists()) {
                $isDefault = true;
            }

            $warehouse = Warehouse::create([
                'uuid' => (string) Str::uuid(),
                'name' => $data['name'],
                'code' => $data['code'],
                'country' => $data['country'] ?? null,
                'division' => $data['division'] ?? null,
                'district' => $data['district'] ?? null,
                'area' => $data['area'] ?? null,
                'postal_code' => $data['postal_code'] ?? null,
                'address' => $data['address'] ?? null,
                'is_default' => $isDefault,
                'is_active' => $data['is_active'] ?? true,
            ]);

            $this->auditLogger->log(
                actor: $actor, action: 'created', module: 'Inventory',
                entityType: 'Warehouse', entityId: (string) $warehouse->id,
                newValue: ['name' => $warehouse->name, 'code' => $warehouse->code],
            );

            return $warehouse;
        });
    }

    public function update(Warehouse $warehouse, array $data, User $actor): Warehouse
    {
        return DB::transaction(function () use ($warehouse, $data, $actor) {
            $before = $warehouse->only(['name', 'is_active', 'is_default']);

            if (($data['is_default'] ?? false) && ! $warehouse->is_default) {
                $this->unsetExistingDefault();
            }

            // Prevent leaving zero default warehouses.
            if (array_key_exists('is_default', $data) && ! $data['is_default'] && $warehouse->is_default) {
                unset($data['is_default']);
            }

            $warehouse->update($data);

            $this->auditLogger->log(
                actor: $actor, action: 'updated', module: 'Inventory',
                entityType: 'Warehouse', entityId: (string) $warehouse->id,
                oldValue: $before, newValue: $warehouse->only(['name', 'is_active', 'is_default']),
            );

            return $warehouse;
        });
    }

    private function unsetExistingDefault(): void
    {
        Warehouse::query()->where('is_default', true)->update(['is_default' => false]);
    }
}
