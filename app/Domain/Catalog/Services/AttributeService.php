<?php

namespace App\Domain\Catalog\Services;

use App\Domain\Audit\Services\AuditLogger;
use App\Domain\Catalog\Models\Attribute;
use App\Domain\Catalog\Models\AttributeValue;
use App\Models\User;
use Illuminate\Support\Str;

class AttributeService
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
    ) {}

    public function create(string $name, User $actor): Attribute
    {
        $attribute = Attribute::create([
            'name' => $name,
            'slug' => Str::slug($name),
        ]);

        $this->auditLogger->log(
            actor: $actor, action: 'created', module: 'Catalog',
            entityType: 'Attribute', entityId: (string) $attribute->id,
            newValue: ['name' => $name],
        );

        return $attribute;
    }

    public function addValue(Attribute $attribute, string $value, User $actor): AttributeValue
    {
        $attributeValue = $attribute->values()->create([
            'value' => $value,
            'slug' => Str::slug($value),
        ]);

        $this->auditLogger->log(
            actor: $actor, action: 'value_added', module: 'Catalog',
            entityType: 'Attribute', entityId: (string) $attribute->id,
            newValue: ['value' => $value],
        );

        return $attributeValue;
    }

    public function update(Attribute $attribute, string $name, User $actor): Attribute
    {
        $old = $attribute->only(['name', 'slug']);
        $attribute->update(['name' => $name, 'slug' => Str::slug($name)]);
        $this->auditLogger->log(actor: $actor, action: 'updated', module: 'Catalog', entityType: 'Attribute', entityId: (string) $attribute->id, oldValue: $old, newValue: $attribute->only(['name', 'slug']));
        return $attribute->refresh();
    }

    public function delete(Attribute $attribute, User $actor): void
    {
        $old = $attribute->only(['name', 'slug']);
        $attribute->delete();
        $this->auditLogger->log(actor: $actor, action: 'deleted', module: 'Catalog', entityType: 'Attribute', entityId: (string) $attribute->id, oldValue: $old);
    }

    public function deleteValue(AttributeValue $value, User $actor): void
    {
        $attributeId = $value->attribute_id;
        abort_if($value->variants()->exists(), 422, 'This value is used by a product variant.');
        $old = $value->only(['attribute_id', 'value', 'slug']);
        $value->delete();
        $this->auditLogger->log(actor: $actor, action: 'value_deleted', module: 'Catalog', entityType: 'Attribute', entityId: (string) $attributeId, oldValue: $old);
    }
}
