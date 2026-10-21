<?php

namespace App\Http\Resources\Catalog;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductVariantResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'sku' => $this->sku,
            'price' => $this->price,
            'sale_price' => $this->sale_price,
            'effective_price' => $this->effective_price,
            'is_default' => $this->is_default,
            'attributes' => $this->whenLoaded('attributeValues', fn () => $this->attributeValues->map(fn ($v) => [
                'attribute' => $v->attribute?->name,
                'value' => $v->value,
            ])),
        ];
    }
}
