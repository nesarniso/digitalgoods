<?php

namespace App\Http\Resources\Catalog;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'name' => $this->name,
            'slug' => $this->slug,
            'sku' => $this->sku,
            'product_type' => $this->product_type,
            'regular_price' => $this->regular_price,
            'sale_price' => $this->sale_price,
            'effective_price' => $this->effective_price,
            'status' => $this->status,
            'featured' => $this->featured,
            'category' => new CategoryResource($this->whenLoaded('category')),
            'brand' => new BrandResource($this->whenLoaded('brand')),
            'variants' => ProductVariantResource::collection($this->whenLoaded('variants')),
            'images' => $this->whenLoaded('images', fn () => $this->images->map(fn ($img) => [
                'id' => $img->id,
                'url' => $img->url,
                'is_thumbnail' => $img->is_thumbnail,
            ])),
            'tags' => $this->whenLoaded('tags', fn () => $this->tags->pluck('name')),
            'created_at' => $this->created_at,
        ];
    }
}
