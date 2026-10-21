<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Domain\Catalog\Models\Brand;
use App\Domain\Catalog\Services\BrandService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Catalog\StoreBrandRequest;
use App\Http\Requests\Admin\Catalog\UpdateBrandRequest;
use App\Http\Resources\Catalog\BrandResource;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    public function __construct(private readonly BrandService $brands) {}

    public function index(Request $request)
    {
        $brands = Brand::query()->orderBy('name')->paginate($request->integer('per_page', 20));

        return response()->json([
            'success' => true,
            'message' => 'Brands retrieved.',
            'data' => BrandResource::collection($brands),
            'meta' => ['pagination' => [
                'current_page' => $brands->currentPage(),
                'per_page' => $brands->perPage(),
                'total' => $brands->total(),
            ]],
        ]);
    }

    public function store(StoreBrandRequest $request)
    {
        $brand = $this->brands->create($request->validated(), $request->user());

        return response()->json([
            'success' => true,
            'message' => 'Brand created.',
            'data' => new BrandResource($brand),
            'meta' => (object) [],
        ], 201);
    }

    public function update(UpdateBrandRequest $request, Brand $brand)
    {
        $brand = $this->brands->update($brand, $request->validated(), $request->user());

        return response()->json([
            'success' => true,
            'message' => 'Brand updated.',
            'data' => new BrandResource($brand),
            'meta' => (object) [],
        ]);
    }

    public function destroy(Request $request, Brand $brand)
    {
        abort_unless($request->user()->can('product.delete'), 403, 'This action is unauthorized.');

        $this->brands->delete($brand, $request->user());

        return response()->json([
            'success' => true,
            'message' => 'Brand deleted.',
            'data' => (object) [],
            'meta' => (object) [],
        ]);
    }
}
