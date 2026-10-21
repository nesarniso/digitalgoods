<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Domain\Inventory\Models\Warehouse;
use App\Domain\Inventory\Services\WarehouseService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Inventory\StoreWarehouseRequest;
use App\Http\Requests\Admin\Inventory\UpdateWarehouseRequest;
use Illuminate\Http\Request;

class WarehouseController extends Controller
{
    public function __construct(private readonly WarehouseService $warehouses) {}

    public function index(Request $request)
    {
        abort_unless($request->user()->can('product.view'), 403, 'This action is unauthorized.');

        return response()->json([
            'success' => true,
            'message' => 'Warehouses retrieved.',
            'data' => Warehouse::orderBy('name')->get(),
            'meta' => (object) [],
        ]);
    }

    public function store(StoreWarehouseRequest $request)
    {
        $warehouse = $this->warehouses->create($request->validated(), $request->user());

        return response()->json([
            'success' => true,
            'message' => 'Warehouse created.',
            'data' => $warehouse,
            'meta' => (object) [],
        ], 201);
    }

    public function update(UpdateWarehouseRequest $request, Warehouse $warehouse)
    {
        $warehouse = $this->warehouses->update($warehouse, $request->validated(), $request->user());

        return response()->json([
            'success' => true,
            'message' => 'Warehouse updated.',
            'data' => $warehouse,
            'meta' => (object) [],
        ]);
    }
}
