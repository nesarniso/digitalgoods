<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Models\ProductVariant;
use App\Domain\Inventory\Models\Inventory;
use App\Domain\Inventory\Models\Warehouse;
use App\Domain\Inventory\Services\InventoryService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Inventory\AdjustStockRequest;
use App\Http\Requests\Admin\Inventory\TransferStockRequest;
use App\Http\Resources\Inventory\InventoryResource;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function __construct(private readonly InventoryService $inventory) {}

    public function index(Request $request)
    {
        abort_unless($request->user()->can('product.view'), 403, 'This action is unauthorized.');

        $inventories = Inventory::query()
            ->with(['product', 'variant', 'warehouse'])
            ->when($request->query('warehouse_id'), fn ($q, $id) => $q->where('warehouse_id', $id))
            ->when($request->query('product_id'), fn ($q, $id) => $q->where('product_id', $id))
            ->when($request->boolean('low_stock_only'), fn ($q) => $q->whereColumn('physical_stock', '<=', 'low_stock_threshold'))
            ->paginate($request->integer('per_page', 20));

        return response()->json([
            'success' => true,
            'message' => 'Inventory retrieved.',
            'data' => InventoryResource::collection($inventories),
            'meta' => ['pagination' => [
                'current_page' => $inventories->currentPage(),
                'per_page' => $inventories->perPage(),
                'total' => $inventories->total(),
            ]],
        ]);
    }

    public function adjust(AdjustStockRequest $request)
    {
        $product = Product::findOrFail($request->integer('product_id'));
        $variant = $request->filled('product_variant_id') ? ProductVariant::findOrFail($request->integer('product_variant_id')) : null;
        $warehouse = Warehouse::findOrFail($request->integer('warehouse_id'));

        $inventoryRow = $this->inventory->firstOrCreateInventory($product, $variant, $warehouse);

        $inventoryRow = $this->inventory->adjust(
            $inventoryRow,
            $request->integer('delta'),
            $request->string('reason'),
            $request->user(),
        );

        return response()->json([
            'success' => true,
            'message' => 'Stock adjusted.',
            'data' => new InventoryResource($inventoryRow),
            'meta' => (object) [],
        ]);
    }

    public function transfer(TransferStockRequest $request)
    {
        $product = Product::findOrFail($request->integer('product_id'));
        $variant = $request->filled('product_variant_id') ? ProductVariant::findOrFail($request->integer('product_variant_id')) : null;
        $fromWarehouse = Warehouse::findOrFail($request->integer('from_warehouse_id'));
        $toWarehouse = Warehouse::findOrFail($request->integer('to_warehouse_id'));

        $fromInventory = $this->inventory->firstOrCreateInventory($product, $variant, $fromWarehouse);
        $toInventory = $this->inventory->firstOrCreateInventory($product, $variant, $toWarehouse);

        $this->inventory->transfer($fromInventory, $toInventory, $request->integer('quantity'), $request->user());

        return response()->json([
            'success' => true,
            'message' => 'Stock transferred.',
            'data' => [
                'from' => new InventoryResource($fromInventory->fresh()),
                'to' => new InventoryResource($toInventory->fresh()),
            ],
            'meta' => (object) [],
        ]);
    }
}
