<?php

namespace App\Domain\Payments\Gateways;

use App\Domain\Payments\Contracts\PaymentGatewayInterface;
use App\Domain\Payments\Models\PaymentTransaction;

/**
 * Customer transfers to a bank account the store publishes (via Settings
 * — bank_account_name/number/routing, managed like any other setting from
 * Phase 3), then an admin manually confirms receipt against their bank
 * statement. No external API exists to integrate with — this is
 * inherently a manual gateway, same as CashOnDeliveryGateway.
 */
class BankTransferGateway implements PaymentGatewayInterface
{
    public function initiate(array $data): array
    {
        return [
            'status' => 'pending',
            'gateway_reference' => null,
            'redirect_url' => null,
            'instructions' => 'Transfer the order amount to the account details provided and an admin will confirm receipt.',
            'raw' => [],
        ];
    }

    public function verify(PaymentTransaction $transaction): array
    {
        return ['status' => $transaction->status, 'raw' => []];
    }

    public function refund(PaymentTransaction $transaction, string $amount): array
    {
        return ['status' => 'refunded', 'refunded_amount' => $amount, 'raw' => [
            'note' => 'Bank refund recorded administratively — the actual transfer happens outside this system.',
        ]];
    }

    public function supportsAutomaticVerification(): bool
    {
        return false;
    }
}
