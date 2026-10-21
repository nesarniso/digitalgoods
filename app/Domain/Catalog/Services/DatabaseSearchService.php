<?php

namespace App\Domain\Catalog\Services;

use App\Domain\Catalog\Contracts\SearchServiceInterface;
use App\Domain\Catalog\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class DatabaseSearchService implements SearchServiceInterface
{
    public function searchProducts(?string $query, array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $builder = Product::query()
            ->with(['category', 'brand', 'images'])
            ->where('status', $filters['status'] ?? 'published');

        if (! empty($query)) {
            $builder->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                    ->orWhere('sku', 'like', "%{$query}%")
                    ->orWhere('short_description', 'like', "%{$query}%");
            });
        }

        if (! empty($filters['category_id'])) {
            $builder->where('category_id', $filters['category_id']);
        }

        if (! empty($filters['brand_id'])) {
            $builder->where('brand_id', $filters['brand_id']);
        }

        if (! empty($filters['min_price'])) {
            $builder->where('regular_price', '>=', $filters['min_price']);
        }

        if (! empty($filters['max_price'])) {
            $builder->where('regular_price', '<=', $filters['max_price']);
        }

        return $builder->latest('id')->paginate($perPage);
    }
}
