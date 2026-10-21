<?php

namespace App\Http\Controllers\Api\V1\Storefront;

use App\Domain\Catalog\Contracts\SearchServiceInterface;
use App\Domain\Catalog\Models\Category;
use App\Domain\Catalog\Models\Product;
use App\Http\Controllers\Controller;
use App\Http\Resources\Catalog\CategoryResource;
use App\Http\Resources\Catalog\ProductResource;
use Illuminate\Http\Request;

/**
 * Public, unauthenticated catalog browsing — what the storefront and
 * Flutter customer app hit. Only ever returns published products; draft/
 * pending/rejected products never leak here regardless of query params.
 */
class CatalogController extends Controller
{
    public function __construct(private readonly SearchServiceInterface $search) {}

    public function categories()
    {
        $tree = Category::query()
            ->whereNull('parent_id')
            ->where('is_active', true)
            ->with(['children' => fn ($q) => $q->where('is_active', true)])
            ->orderBy('sort_order')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Categories retrieved.',
            'data' => CategoryResource::collection($tree),
            'meta' => (object) [],
        ]);
    }

    public function products(Request $request)
    {
        $results = $this->search->searchProducts(
            query: $request->query('q'),
            filters: [
                'category_id' => $request->query('category_id'),
                'brand_id' => $request->query('brand_id'),
                'min_price' => $request->query('min_price'),
                'max_price' => $request->query('max_price'),
                'status' => 'published',
            ],
            perPage: $request->integer('per_page', 20),
        );

        return response()->json([
            'success' => true,
            'message' => 'Products retrieved.',
            'data' => ProductResource::collection($results->items()),
            'meta' => ['pagination' => [
                'current_page' => $results->currentPage(),
                'per_page' => $results->perPage(),
                'total' => $results->total(),
            ]],
        ]);
    }

    public function show(string $slug)
    {
        $product = Product::query()
            ->where('slug', $slug)
            ->where('status', 'published')
            ->with(['category', 'brand', 'variants.attributeValues.attribute', 'images', 'tags'])
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'message' => 'Product retrieved.',
            'data' => new ProductResource($product),
            'meta' => (object) [],
        ]);
    }
}
