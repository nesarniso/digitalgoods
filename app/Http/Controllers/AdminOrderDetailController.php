<?php

namespace App\Http\Controllers;

use App\Domain\Orders\Models\Order;
use App\Domain\Orders\Services\OrderService;
use App\Domain\Shipping\Services\ShipmentService;
use App\Domain\Orders\Models\ReturnRequest;
use App\Domain\Orders\Services\ReturnRequestService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class AdminOrderDetailController extends Controller
{
    public function __construct(
        private readonly OrderService $orders,
        private readonly ShipmentService $shipments,
        private readonly ReturnRequestService $returns,
    ) {}

    public function show(Order $order)
    {
        abort_unless(
            auth()->user()->hasAnyRole(['admin', 'super_admin'])
                || auth()->user()->can('order.view'),
            403
        );

        $with = ['user', 'items.variant.attributeValues.attribute', 'addresses', 'statusHistory', 'paymentTransactions', 'latestPaymentTransaction', 'returnRequests.item'];
        if (Schema::hasTable('shipments')) {
            $with[] = 'shipments';
        }

        $order->load($with);

        return view('admin.order-detail', [
            'order' => $order,
            'shipment' => Schema::hasTable('shipments') ? $order->latestShipment()->first() : null,
        ]);
    }

    public function receipt(Order $order)
    {
        abort_unless(
            auth()->user()->hasAnyRole(['admin', 'super_admin'])
                || auth()->user()->can('order.view'),
            403
        );

        $order->load([
            'user',
            'items.variant.attributeValues.attribute',
            'addresses',
            'latestPaymentTransaction',
        ]);

        return view('admin.order-receipt', [
            'order' => $order,
            'paymentTransaction' => $order->latestPaymentTransaction,
        ]);
    }

    public function updateReturnStatus(Request $request, Order $order, ReturnRequest $returnRequest)
    {
        abort_unless(
            $request->user()->hasAnyRole(['admin', 'super_admin'])
                || $request->user()->can('order.update'),
            403
        );
        abort_unless($returnRequest->order_id === $order->id, 404);
        $data = $request->validate([
            'status' => ['required', 'in:approved,rejected,received,refunded,cancelled'],
            'admin_note' => ['nullable', 'string', 'max:2000'],
        ]);
        $this->returns->updateStatus($returnRequest, $data['status'], $data['admin_note'] ?? null, $request->user());

        return back()->with('success', 'Return request updated.');
    }

    public function updateStatus(Request $request, Order $order)
    {
        abort_unless(
            $request->user()->hasAnyRole(['admin', 'super_admin'])
                || $request->user()->can('order.update'),
            403
        );
        $data = $request->validate(['status' => ['required', 'in:pending,confirmed,processing,packed,shipped,out_for_delivery,delivered'], 'message' => ['nullable', 'string', 'max:500']]);
        $this->orders->updateStatus($order, $data['status'], $data['message'] ?? null, $request->user());

        return back()->with('success', 'Order status updated.');
    }

    public function retryDigitalLicenses(Request $request, Order $order)
    {
        abort_unless(
            $request->user()->hasAnyRole(['admin', 'super_admin'])
                || $request->user()->can('order.update'),
            403
        );

        $this->orders->retryDigitalLicenseAssignment($order, $request->user());

        return back()->with('success', 'Digital product keys assignment retried.');
    }

    public function createShipment(Request $request, Order $order)
    {
        abort_unless(
            $request->user()->hasAnyRole(['admin', 'super_admin'])
                || $request->user()->can('order.update'),
            403
        );

        if (! Schema::hasTable('shipments')) {
            return back()->with('error', 'Shipment records are unavailable until the database migrations are run.');
        }

        $shipment = $this->shipments->createForOrder($order, $request->input('courier', 'manual'), [
            'delivery_fee' => $request->input('delivery_fee', '0.00'),
            'notes' => $request->input('notes'),
        ]);

        return back()->with('success', 'Shipment '.$shipment->shipment_number.' created.');
    }

    public function updateShipmentStatus(Request $request, Order $order)
    {
        abort_unless(
            $request->user()->hasAnyRole(['admin', 'super_admin'])
                || $request->user()->can('order.update'),
            403
        );

        if (! Schema::hasTable('shipments')) {
            return back()->with('error', 'Shipment records are unavailable until the database migrations are run.');
        }

        $shipment = $order->shipments()->findOrFail($request->route('shipment'));
        $data = $request->validate([
            'status' => ['required', 'in:pending_dispatch,picked_up,in_transit,out_for_delivery,delivered,failed,cancelled'],
        ]);

        $this->shipments->updateStatus($shipment, $data['status'], [
            'admin_note' => $request->input('note'),
            'updated_by' => $request->user()->id,
            'updated_at' => now()->toDateTimeString(),
        ]);

        return back()->with('success', 'Shipment status updated to '.Str::headline($data['status']).'.');
    }
}
