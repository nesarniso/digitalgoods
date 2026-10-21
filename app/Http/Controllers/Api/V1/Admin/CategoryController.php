<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Domain\Catalog\Models\Category;
use App\Domain\Catalog\Services\CategoryService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Catalog\StoreCategoryRequest;
use App\Http\Requests\Admin\Catalog\UpdateCategoryRequest;
use App\Http\Resources\Catalog\CategoryResource;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function __construct(private readonly CategoryService $categories) {}

    public function index(Request $request)
    {
        $tree = Category::query()
            ->whereNull('parent_id')
            ->with('children.children.children') // 3 levels deep is a sane default for a storefront tree
            ->orderBy('sort_order')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Categories retrieved.',
            'data' => CategoryResource::collection($tree),
            'meta' => (object) [],
        ]);
    }

    public function store(StoreCategoryRequest $request)
    {
        $category = $this->categories->create($request->validated(), $request->user());

        return response()->json([
            'success' => true,
            'message' => 'Category created.',
            'data' => new CategoryResource($category),
            'meta' => (object) [],
        ], 201);
    }

    public function update(UpdateCategoryRequest $request, Category $category)
    {
        $category = $this->categories->update($category, $request->validated(), $request->user());

        return response()->json([
            'success' => true,
            'message' => 'Category updated.',
            'data' => new CategoryResource($category),
            'meta' => (object) [],
        ]);
    }

    public function destroy(Request $request, Category $category)
    {
        abort_unless($request->user()->can('product.delete'), 403, 'This action is unauthorized.');

        $this->categories->delete($category, $request->user());

        return response()->json([
            'success' => true,
            'message' => 'Category deleted.',
            'data' => (object) [],
            'meta' => (object) [],
        ]);
    }
}
