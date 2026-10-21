<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Domain\Orders\Models\Order;
use App\Domain\Orders\Services\OrderService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Orders\UpdateOrderStatusRequest;
use App\Http\Resources\Orders\OrderResource;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(private readonly OrderService $orders) {}

    public function index(Request $request)
    {
        abort_unless($request->user()->can('order.view'), 403, 'This action is unauthorized.');

        $orders = Order::query()
            ->with('user:id,name,email')
            ->when($request->query('status'), fn ($q, $status) => $q->where('status', $status))
            ->when($request->query('order_number'), fn ($q, $n) => $q->where('order_number', 'like', "%{$n}%"))
            ->latest('id')
            ->paginate($request->integer('per_page', 20));

        return response()->json([
            'success' => true,
            'message' => 'Orders retrieved.',
            'data' => OrderResource::collection($orders),
            'meta' => ['pagination' => [
                'current_page' => $orders->currentPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
            ]],
        ]);
    }

    public function show(Request $request, Order $order)
    {
        abort_unless($request->user()->can('order.view'), 403, 'This action is unauthorized.');

        return response()->json([
            'success' => true,
            'message' => 'Order retrieved.',
            'data' => new OrderResource($order->load(['items', 'addresses', 'statusHistory', 'paymentTransactions'])),
            'meta' => (object) [],
        ]);
    }

    public function updateStatus(UpdateOrderStatusRequest $request, Order $order)
    {
        $order = $this->orders->updateStatus($order, $request->string('status'), $request->input('message'), $request->user());

        return response()->json([
            'success' => true,
            'message' => 'Order status updated.',
            'data' => new OrderResource($order),
            'meta' => (object) [],
        ]);
    }

    public function confirmPayment(Request $request, Order $order)
    {
        abort_unless($request->user()->can('order.update'), 403, 'This action is unauthorized.');

        $order = $this->orders->confirmPayment($order, $request->user());

        return response()->json([
            'success' => true,
            'message' => 'Payment confirmed, order moved to confirmed.',
            'data' => new OrderResource($order),
            'meta' => (object) [],
        ]);
    }

    public function cancel(Request $request, Order $order)
    {
        abort_unless($request->user()->can('order.cancel'), 403, 'This action is unauthorized.');
        $request->validate(['reason' => ['required', 'string', 'max:500']]);

        $order = $this->orders->cancel($order, $request->string('reason'), $request->user());

        return response()->json([
            'success' => true,
            'message' => 'Order cancelled.',
            'data' => new OrderResource($order),
            'meta' => (object) [],
        ]);
    }
}
