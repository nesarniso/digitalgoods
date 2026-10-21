<?php

namespace App\Http\Controllers;

use App\Domain\Orders\Models\Order;
use App\Domain\Orders\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AdminOrderViewController extends Controller
{
    public function __construct(private readonly OrderService $orderService) {}

    public function __invoke(Request $request)
    {
        abort_unless(
            $request->user()->hasAnyRole(['admin', 'super_admin'])
                || $request->user()->can('order.view'),
            403
        );

        return view('admin.orders', [
            'orders' => Order::query()
                ->with('latestPaymentTransaction')
                ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
                ->when($request->filled('q'), fn ($query) => $query->where('order_number', 'like', '%'.$request->string('q').'%'))
                ->latest('id')
                ->paginate(15)
                ->withQueryString(),
        ]);
    }

    public function bulkStatus(Request $request)
    {
        abort_unless(
            $request->user()->hasAnyRole(['admin', 'super_admin'])
                || $request->user()->can('order.update'),
            403
        );

        $data = $request->validate([
            'order_ids' => ['required', 'array', 'min:1', 'max:100'],
            'order_ids.*' => ['integer', 'distinct', 'exists:orders,id'],
            'status' => ['required', 'in:pending,confirmed,processing,packed,shipped,out_for_delivery,delivered'],
        ]);

        $status = $data['status'];
        $orders = Order::query()->whereIn('id', $data['order_ids'])->get();
        $invalid = $orders->first(fn (Order $order) => $order->status === 'delivered' && $status !== 'delivered');
        if ($invalid) {
            return back()->with('error', "Order {$invalid->order_number} cannot move from ".str_replace('_', ' ', $invalid->status)." to {$status}.");
        }

        try {
            DB::transaction(function () use ($data, $status, $request): void {
                $orders = Order::query()->whereIn('id', $data['order_ids'])->lockForUpdate()->get();
                foreach ($orders as $order) {
                    if ($order->status === $status) {
                        continue;
                    }

                    if ($status === 'confirmed') {
                        $this->orderService->confirmPayment($order, $request->user());
                        continue;
                    }

                    $this->orderService->updateStatus($order, $status, 'Bulk status update from order management.', $request->user());
                }
            });
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors())->with('error', 'Bulk update was rolled back. Review the selected orders and payment details.');
        }

        return back()->with('success', $orders->count().' order(s) updated to '.($status === 'confirmed' ? 'Paid' : ucfirst(str_replace('_', ' ', $status))).'.');
    }

    private function canBulkMove(Order $order, string $target): bool
    {
        return $order->status !== 'delivered' || $target === 'delivered';
    }
}
