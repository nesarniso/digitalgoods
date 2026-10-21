<?php

namespace App\Domain\Payments\Gateways;

use App\Domain\Payments\Contracts\PaymentGatewayInterface;
use App\Domain\Payments\Models\PaymentTransaction;

class ManualMobileGateway implements PaymentGatewayInterface
{
    public function initiate(array $data): array
    {
        return [
            'status' => 'pending',
            'gateway_reference' => $data['manual_transaction_id'] ?? null,
            'redirect_url' => null,
            'instructions' => $data['instructions'] ?? 'Send the order amount to the merchant number and submit the transaction ID.',
            'raw' => ['payer_mobile' => $data['payer_mobile'] ?? null],
        ];
    }

    public function verify(PaymentTransaction $transaction): array
    {
        return ['status' => $transaction->status, 'raw' => []];
    }

    public function refund(PaymentTransaction $transaction, string $amount): array
    {
        return ['status' => 'refunded', 'refunded_amount' => $amount, 'raw' => [
            'note' => 'Manual mobile payment refund recorded administratively.',
        ]];
    }

    public function supportsAutomaticVerification(): bool
    {
        return false;
    }
}
