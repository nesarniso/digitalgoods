<?php

namespace App\Domain\Catalog\Services;

use App\Domain\Audit\Services\AuditLogger;
use App\Domain\Catalog\Models\Category;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CategoryService
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
    ) {}

    public function create(array $data, User $actor): Category
    {
        if (! empty($data['parent_id'])) {
            $this->assertParentExists($data['parent_id']);
        }

        $category = Category::create([
            'uuid' => (string) Str::uuid(),
            'parent_id' => $data['parent_id'] ?? null,
            'name' => $data['name'],
            'slug' => $this->uniqueSlug($data['name']),
            'description' => $data['description'] ?? null,
            'sort_order' => $data['sort_order'] ?? 0,
            'is_active' => $data['is_active'] ?? true,
            'is_featured' => $data['is_featured'] ?? false,
            'meta_title' => $data['meta_title'] ?? null,
            'meta_description' => $data['meta_description'] ?? null,
        ]);

        $this->auditLogger->log(
            actor: $actor, action: 'created', module: 'Catalog',
            entityType: 'Category', entityId: (string) $category->id,
            newValue: ['name' => $category->name, 'parent_id' => $category->parent_id],
        );

        return $category;
    }

    public function update(Category $category, array $data, User $actor): Category
    {
        if (array_key_exists('parent_id', $data) && $data['parent_id'] !== null) {
            $this->assertParentExists($data['parent_id']);
            $this->assertNoCycle($category, (int) $data['parent_id']);
        }

        $before = $category->only(['name', 'parent_id', 'is_active']);

        if (isset($data['name']) && $data['name'] !== $category->name) {
            $data['slug'] = $this->uniqueSlug($data['name'], $category->id);
        }

        $category->update($data);

        $this->auditLogger->log(
            actor: $actor, action: 'updated', module: 'Catalog',
            entityType: 'Category', entityId: (string) $category->id,
            oldValue: $before,
            newValue: $category->only(['name', 'parent_id', 'is_active']),
        );

        return $category;
    }

    public function delete(Category $category, User $actor): void
    {
        if ($category->children()->exists()) {
            throw ValidationException::withMessages([
                'category' => ['Cannot delete a category that has subcategories. Move or delete them first.'],
            ]);
        }

        if ($category->products()->exists()) {
            throw ValidationException::withMessages([
                'category' => ['Cannot delete a category that has products assigned to it.'],
            ]);
        }

        $category->delete();

        $this->auditLogger->log(
            actor: $actor, action: 'deleted', module: 'Catalog',
            entityType: 'Category', entityId: (string) $category->id,
        );
    }

    private function assertParentExists(int $parentId): void
    {
        if (! Category::query()->whereKey($parentId)->exists()) {
            throw ValidationException::withMessages([
                'parent_id' => ['The selected parent category does not exist.'],
            ]);
        }
    }

    /**
     * Walks up the ancestor chain of the *proposed* parent to make sure the
     * category being updated never becomes its own ancestor — otherwise
     * `children()` recursion would infinite-loop.
     */
    private function assertNoCycle(Category $category, int $proposedParentId): void
    {
        if ($proposedParentId === $category->id) {
            throw ValidationException::withMessages([
                'parent_id' => ['A category cannot be its own parent.'],
            ]);
        }

        $ancestorId = $proposedParentId;
        $depth = 0;

        while ($ancestorId !== null && $depth < 100) {
            if ($ancestorId === $category->id) {
                throw ValidationException::withMessages([
                    'parent_id' => ['This would create a circular category reference.'],
                ]);
            }

            $ancestorId = Category::query()->whereKey($ancestorId)->value('parent_id');
            $depth++;
        }
    }

    private function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $i = 1;

        while (Category::query()->where('slug', $slug)->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))->exists()) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }
}
