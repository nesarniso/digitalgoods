<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Services\ProductService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Catalog\StoreProductRequest;
use App\Http\Requests\Admin\Catalog\StoreVariantRequest;
use App\Http\Requests\Admin\Catalog\UpdateProductRequest;
use App\Http\Resources\Catalog\ProductResource;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct(private readonly ProductService $products) {}

    public function index(Request $request)
    {
        abort_unless($request->user()->can('product.view'), 403, 'This action is unauthorized.');

        $products = Product::query()
            ->with(['category', 'brand'])
            ->when($request->query('status'), fn ($q, $status) => $q->where('status', $status))
            ->when($request->query('category_id'), fn ($q, $id) => $q->where('category_id', $id))
            ->latest('id')
            ->paginate($request->integer('per_page', 20));

        return response()->json([
            'success' => true,
            'message' => 'Products retrieved.',
            'data' => ProductResource::collection($products),
            'meta' => ['pagination' => [
                'current_page' => $products->currentPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
            ]],
        ]);
    }

    public function show(Request $request, Product $product)
    {
        abort_unless($request->user()->can('product.view'), 403, 'This action is unauthorized.');

        return response()->json([
            'success' => true,
            'message' => 'Product retrieved.',
            'data' => new ProductResource($product->load(['category', 'brand', 'variants.attributeValues.attribute', 'images', 'tags'])),
            'meta' => (object) [],
        ]);
    }

    public function store(StoreProductRequest $request)
    {
        $product = $this->products->create($request->validated(), $request->user());

        return response()->json([
            'success' => true,
            'message' => 'Product created.',
            'data' => new ProductResource($product),
            'meta' => (object) [],
        ], 201);
    }

    public function update(UpdateProductRequest $request, Product $product)
    {
        $product = $this->products->update($product, $request->validated(), $request->user());

        return response()->json([
            'success' => true,
            'message' => 'Product updated.',
            'data' => new ProductResource($product),
            'meta' => (object) [],
        ]);
    }

    public function destroy(Request $request, Product $product)
    {
        $this->authorize('delete', $product);

        $this->products->delete($product, $request->user());

        return response()->json([
            'success' => true,
            'message' => 'Product deleted.',
            'data' => (object) [],
            'meta' => (object) [],
        ]);
    }

    public function storeVariant(StoreVariantRequest $request, Product $product)
    {
        $variant = $this->products->addVariant($product, $request->validated(), $request->user());

        return response()->json([
            'success' => true,
            'message' => 'Variant added.',
            'data' => $variant->load('attributeValues.attribute'),
            'meta' => (object) [],
        ], 201);
    }
}
