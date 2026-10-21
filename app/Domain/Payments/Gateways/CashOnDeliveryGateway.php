<?php

namespace App\Domain\Payments\Gateways;

use App\Domain\Payments\Contracts\PaymentGatewayInterface;
use App\Domain\Payments\Models\PaymentTransaction;

/**
 * No external API — the "payment" is cash changing hands at delivery.
 * initiate() immediately marks the transaction pending; there's nothing
 * to verify automatically (no gateway to call), so confirmation is always
 * a manual admin/deliveryman action via
 * PaymentService::confirmManualPayment(). Refunds are similarly manual —
 * COD has no transaction to reverse electronically.
 */
class CashOnDeliveryGateway implements PaymentGatewayInterface
{
    public function initiate(array $data): array
    {
        return [
            'status' => 'pending',
            'gateway_reference' => null,
            'redirect_url' => null,
            'instructions' => 'Pay the delivery agent in cash upon receiving your order.',
            'raw' => [],
        ];
    }

    public function verify(PaymentTransaction $transaction): array
    {
        // No external system to query — status only changes via an
        // explicit admin confirmation action.
        return ['status' => $transaction->status, 'raw' => []];
    }

    public function refund(PaymentTransaction $transaction, string $amount): array
    {
        // Cash refunds happen outside the system (returned in person or
        // via a separate wallet/bank transfer, Phase 33/refund workflow).
        // Recording it here would imply an electronic reversal that never
        // happened.
        return ['status' => 'refunded', 'refunded_amount' => $amount, 'raw' => [
            'note' => 'Cash refund recorded administratively — no electronic reversal exists for COD.',
        ]];
    }

    public function supportsAutomaticVerification(): bool
    {
        return false;
    }
}
