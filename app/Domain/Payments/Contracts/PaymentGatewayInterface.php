<?php

namespace App\Domain\Payments\Contracts;

use App\Domain\Payments\Models\PaymentTransaction;

/**
 * Every payment gateway — COD, Bank Transfer (built this phase), and
 * Stripe/SSLCommerz/bKash/Nagad (interface only, adapters deferred — see
 * Gateways/README or the Phase 10 README section) — implements this.
 * PaymentService is the only thing that resolves a concrete gateway (via
 * container binding keyed by the gateway string, config/payments.php);
 * nothing else in the codebase should type-hint a concrete gateway class,
 * which is what makes it possible to add Stripe later without touching
 * PaymentService, CheckoutService, or the eventual OrderService.
 *
 * Money amounts are always strings (DECIMAL-safe, bcmath-compatible),
 * never floats — consistent with every other monetary value in this
 * codebase since Phase 0.
 */
interface PaymentGatewayInterface
{
    /**
     * Starts a payment attempt. For a redirect-based gateway (Stripe
     * Checkout, SSLCommerz) this would return a redirect URL the client
     * follows; for a manual gateway (COD, Bank Transfer) it just marks the
     * transaction as pending with instructions, since there's no external
     * checkout session to redirect to.
     *
     * @param  array{amount: string, currency: string, reference: string, payer_name?: string, payer_phone?: string}  $data
     * @return array{status: string, gateway_reference: ?string, redirect_url: ?string, instructions: ?string, raw: array}
     */
    public function initiate(array $data): array;

    /**
     * Server-side status check (Rule: "Verify payment status
     * server-side" — never trust a client-reported "I paid"). For a real
     * gateway this calls their API; for a manual gateway there's nothing
     * to query automatically, so it just returns the transaction's
     * current stored status — manual gateways are confirmed by an admin
     * action instead (PaymentService::confirmManualPayment()).
     *
     * @return array{status: string, raw: array}
     */
    public function verify(PaymentTransaction $transaction): array;

    /**
     * @return array{status: string, refunded_amount: string, raw: array}
     */
    public function refund(PaymentTransaction $transaction, string $amount): array;

    public function supportsAutomaticVerification(): bool;
}
