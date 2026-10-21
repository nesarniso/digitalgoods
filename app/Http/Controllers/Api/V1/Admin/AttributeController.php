<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Domain\Catalog\Models\Attribute;
use App\Domain\Catalog\Services\AttributeService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Catalog\StoreAttributeRequest;
use App\Http\Requests\Admin\Catalog\StoreAttributeValueRequest;
use Illuminate\Http\Request;

class AttributeController extends Controller
{
    public function __construct(private readonly AttributeService $attributes) {}

    public function index(Request $request)
    {
        abort_unless($request->user()->can('product.view'), 403, 'This action is unauthorized.');

        return response()->json([
            'success' => true,
            'message' => 'Attributes retrieved.',
            'data' => Attribute::with('values')->orderBy('name')->get(),
            'meta' => (object) [],
        ]);
    }

    public function store(StoreAttributeRequest $request)
    {
        $attribute = $this->attributes->create($request->string('name'), $request->user());

        return response()->json([
            'success' => true,
            'message' => 'Attribute created.',
            'data' => $attribute,
            'meta' => (object) [],
        ], 201);
    }

    public function storeValue(StoreAttributeValueRequest $request, Attribute $attribute)
    {
        $value = $this->attributes->addValue($attribute, $request->string('value'), $request->user());

        return response()->json([
            'success' => true,
            'message' => 'Attribute value added.',
            'data' => $value,
            'meta' => (object) [],
        ], 201);
    }
}
