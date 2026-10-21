<?php

namespace App\Domain\Catalog\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Search abstraction per spec section 16: today it's backed by plain SQL
 * (works identically on MySQL or sqlite), but nothing outside this
 * interface should know that. Swapping to Meilisearch/Elasticsearch later
 * means writing one new class and changing config/search.php — not
 * touching ProductService, controllers, or anything else that searches.
 */
interface SearchServiceInterface
{
    /**
     * @param  array{category_id?: int, brand_id?: int, min_price?: string, max_price?: string, status?: string}  $filters
     */
    public function searchProducts(?string $query, array $filters = [], int $perPage = 20): LengthAwarePaginator;
}
