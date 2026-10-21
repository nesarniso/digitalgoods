<?php

namespace App\Http\Controllers;

use App\Domain\Payments\Models\PaymentTransaction;
use App\Domain\Payments\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use App\Domain\Orders\Services\OrderService;

class AdminPaymentController extends Controller
{
    public function __construct(private readonly PaymentService $payments, private readonly OrderService $orders) {}

    public function show(PaymentTransaction $transaction)
    {
        return view('admin/payment-detail', ['transaction' => $transaction->load(['order.items.product', 'payer'])]);
    }

    public function confirm(Request $request, PaymentTransaction $transaction)
    {
        abort_unless($request->user()->can('payment.manage'), 403, 'This action is unauthorized.');
        try {
            if ($transaction->order) {
                $this->orders->confirmPayment($transaction->order, $request->user(), $transaction);
            } else {
                $this->payments->confirmManualPayment($transaction, $request->user());
            }
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors())->with('error', 'Payment could not be confirmed. Review the highlighted issue.');
        }

        return back()->with('success', 'Payment confirmed.');
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
        return back()->with('success', 'Payment rejected and order cancelled.');
    }

    public function refund(Request $request, PaymentTransaction $transaction)
    {
        abort_unless($request->user()->can('payment.manage'), 403, 'This action is unauthorized.');
        $data = $request->validate(['amount' => ['required', 'numeric', 'min:0.01']]);
        $this->payments->refund($transaction, (string) $data['amount'], $request->user());

        return back()->with('success', 'Refund processed.');
    }
}
