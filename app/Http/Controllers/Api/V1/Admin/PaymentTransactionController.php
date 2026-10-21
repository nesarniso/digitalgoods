<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Domain\Payments\Models\PaymentTransaction;
use App\Domain\Payments\Services\PaymentService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Payments\RefundPaymentRequest;
use Illuminate\Http\Request;
use App\Domain\Orders\Services\OrderService;

class PaymentTransactionController extends Controller
{
    public function __construct(private readonly PaymentService $payments, private readonly OrderService $orders) {}

    public function index(Request $request)
    {
        abort_unless($request->user()->can('payment.view'), 403, 'This action is unauthorized.');

        $transactions = PaymentTransaction::query()
            ->with('payer:id,name,email')
            ->when($request->query('status'), fn ($q, $status) => $q->where('status', $status))
            ->when($request->query('gateway'), fn ($q, $gateway) => $q->where('gateway', $gateway))
            ->latest('id')
            ->paginate($request->integer('per_page', 20));

        return response()->json([
            'success' => true,
            'message' => 'Payment transactions retrieved.',
            'data' => $transactions->items(),
            'meta' => ['pagination' => [
                'current_page' => $transactions->currentPage(),
                'per_page' => $transactions->perPage(),
                'total' => $transactions->total(),
            ]],
        ]);
    }

    public function reject(Request $request, PaymentTransaction $transaction)
    {
        abort_unless($request->user()->can('payment.manage'), 403, 'This action is unauthorized.');
        $data = $request->validate(['reason' => ['required', 'string', 'max:500']]);
        if ($transaction->order) {
            $this->orders->rejectPayment($transaction->order, $request->user(), $data['reason']);
        } else {
            $this->payments->rejectManualPayment($transaction, $request->user(), $data['reason']);
        }
        return response()->json(['success' => true, 'message' => 'Payment rejected.', 'data' => $transaction->fresh(), 'meta' => (object) []]);
    }

    public function confirm(Request $request, PaymentTransaction $transaction)
    {
        abort_unless($request->user()->can('payment.manage'), 403, 'This action is unauthorized.');

        if ($transaction->order) {
            $this->orders->confirmPayment($transaction->order, $request->user(), $transaction);
            $transaction = $transaction->fresh();
        } else {
            $transaction = $this->payments->confirmManualPayment($transaction, $request->user());
        }

        return response()->json([
            'success' => true,
            'message' => 'Payment confirmed.',
            'data' => $transaction,
            'meta' => (object) [],
        ]);
    }

    public function refund(RefundPaymentRequest $request, PaymentTransaction $transaction)
    {
        $transaction = $this->payments->refund($transaction, (string) $request->input('amount'), $request->user());

        return response()->json([
            'success' => true,
            'message' => 'Refund processed.',
            'data' => $transaction,
            'meta' => (object) [],
        ]);
    }
}
