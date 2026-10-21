<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Domain\Inventory\Models\Inventory;
use App\Domain\Inventory\Models\RestockRequest;
use App\Domain\Inventory\Services\RestockRequestService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Inventory\StoreRestockRequestRequest;
use Illuminate\Http\Request;

class RestockRequestController extends Controller
{
    public function __construct(private readonly RestockRequestService $restockRequests) {}

    public function index(Request $request)
    {
        abort_unless($request->user()->can('product.view'), 403, 'This action is unauthorized.');

        $requests = RestockRequest::query()
            ->with(['inventory.product', 'inventory.warehouse'])
            ->when($request->query('status'), fn ($q, $status) => $q->where('status', $status))
            ->latest('id')
            ->paginate($request->integer('per_page', 20));

        return response()->json([
            'success' => true,
            'message' => 'Restock requests retrieved.',
            'data' => $requests->items(),
            'meta' => ['pagination' => [
                'current_page' => $requests->currentPage(),
                'per_page' => $requests->perPage(),
                'total' => $requests->total(),
            ]],
        ]);
    }

    public function store(StoreRestockRequestRequest $request)
    {
        $inventoryRow = Inventory::findOrFail($request->integer('inventory_id'));

        $restockRequest = $this->restockRequests->create(
            $inventoryRow,
            $request->integer('quantity'),
            $request->input('notes'),
            $request->user(),
        );

        return response()->json([
            'success' => true,
            'message' => 'Restock request created.',
            'data' => $restockRequest,
            'meta' => (object) [],
        ], 201);
    }

    public function approve(Request $request, RestockRequest $restockRequest)
    {
        abort_unless($request->user()->can('product.update'), 403, 'This action is unauthorized.');

        $restockRequest = $this->restockRequests->approve($restockRequest, $request->user());

        return response()->json([
            'success' => true,
            'message' => 'Restock request approved and stock updated.',
            'data' => $restockRequest->load('inventory'),
            'meta' => (object) [],
        ]);
    }

    public function reject(Request $request, RestockRequest $restockRequest)
    {
        abort_unless($request->user()->can('product.update'), 403, 'This action is unauthorized.');
        $request->validate(['reason' => ['required', 'string']]);

        $restockRequest = $this->restockRequests->reject($restockRequest, $request->string('reason'), $request->user());

        return response()->json([
            'success' => true,
            'message' => 'Restock request rejected.',
            'data' => $restockRequest,
            'meta' => (object) [],
        ]);
    }
}
