<?php

namespace App\Domain\Catalog\Services;

use App\Domain\Audit\Services\AuditLogger;
use App\Domain\Catalog\Models\Brand;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class BrandService
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
    ) {}

    public function create(array $data, User $actor): Brand
    {
        $brand = Brand::create([
            'uuid' => (string) Str::uuid(),
            'name' => $data['name'],
            'slug' => $this->uniqueSlug($data['name']),
            'description' => $data['description'] ?? null,
            'website' => $data['website'] ?? null,
            'is_active' => $data['is_active'] ?? true,
            'meta_title' => $data['meta_title'] ?? null,
            'meta_description' => $data['meta_description'] ?? null,
        ]);

        $this->auditLogger->log(
            actor: $actor, action: 'created', module: 'Catalog',
            entityType: 'Brand', entityId: (string) $brand->id,
            newValue: ['name' => $brand->name],
        );

        return $brand;
    }

    public function update(Brand $brand, array $data, User $actor): Brand
    {
        $before = $brand->only(['name', 'is_active']);

        if (isset($data['name']) && $data['name'] !== $brand->name) {
            $data['slug'] = $this->uniqueSlug($data['name'], $brand->id);
        }

        $brand->update($data);

        $this->auditLogger->log(
            actor: $actor, action: 'updated', module: 'Catalog',
            entityType: 'Brand', entityId: (string) $brand->id,
            oldValue: $before, newValue: $brand->only(['name', 'is_active']),
        );

        return $brand;
    }

    public function delete(Brand $brand, User $actor): void
    {
        if ($brand->products()->exists()) {
            throw ValidationException::withMessages([
                'brand' => ['Cannot delete a brand that has products assigned to it.'],
            ]);
        }

        $brand->delete();

        $this->auditLogger->log(
            actor: $actor, action: 'deleted', module: 'Catalog',
            entityType: 'Brand', entityId: (string) $brand->id,
        );
    }

    private function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $i = 1;

        while (Brand::query()->where('slug', $slug)->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))->exists()) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }
}
