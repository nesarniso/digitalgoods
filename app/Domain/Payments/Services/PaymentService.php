<?php

namespace App\Domain\Payments\Services;

use App\Domain\Audit\Services\AuditLogger;
use App\Domain\Orders\Models\Order;
use App\Domain\Payments\Contracts\PaymentGatewayInterface;
use App\Domain\Payments\Models\PaymentTransaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use App\Domain\Settings\Services\SettingService;

/**
 * Sole entry point for payment mutation. Phase 11 (Orders) is the
 * intended caller — OrderService will call initiate() as part of order
 * creation and confirmManualPayment()/handleGatewayCallback() as part of
 * confirming an order, inside the same transaction pattern Phase 5
 * established for inventory.
 */
class PaymentService
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
    ) {}

    /**
     * Idempotent by design: calling this twice with the same
     * $idempotencyKey returns the EXISTING transaction rather than
     * creating a second one and re-charging. This is the concrete
     * mechanism behind spec section 78's "duplicate webhook must not
     * create duplicate payments" — the caller (checkout/order flow)
     * generates one key per genuine payment attempt (e.g. per order),
     * and any retry — client double-submit, network retry, duplicate
     * webhook delivery — collapses onto the same row instead of
     * double-charging.
     */
    public function initiate(string $method, string $amount, string $idempotencyKey, ?User $payer = null, string $currency = 'INR', ?Order $order = null, array $manualDetails = []): PaymentTransaction
    {
        return DB::transaction(function () use ($method, $amount, $idempotencyKey, $payer, $currency, $order, $manualDetails) {
            if (! empty($manualDetails['manual_transaction_id'])) {
                $duplicate = PaymentTransaction::query()->where('manual_transaction_id', $manualDetails['manual_transaction_id'])->first();
                if ($duplicate) {
                    throw ValidationException::withMessages(['transaction_id' => ['This transaction ID has already been submitted.']]);
                }
            }
            $existing = PaymentTransaction::query()
                ->where('idempotency_key', $idempotencyKey)
                ->lockForUpdate()
                ->first();

            if ($existing) {
                return $existing;
            }

            $gateway = $this->resolveGateway($method);

            $transaction = PaymentTransaction::create([
                'uuid' => (string) Str::uuid(),
                'order_id' => $order?->id,
                'gateway' => $method,
                'idempotency_key' => $idempotencyKey,
                'status' => 'initiated',
                'amount' => $amount,
                'currency' => $currency,
                'payer_user_id' => $payer?->id,
                'payer_mobile' => $manualDetails['payer_mobile'] ?? null,
                'manual_transaction_id' => $manualDetails['manual_transaction_id'] ?? null,
                'submitted_amount' => $manualDetails['submitted_amount'] ?? null,
                'paid_at' => $manualDetails['paid_at'] ?? null,
            ]);

            $result = $gateway->initiate([
                'amount' => $amount,
                'currency' => $currency,
                'reference' => $transaction->uuid,
                ...$manualDetails,
                'payer_name' => $payer?->name,
            ]);

            $transaction->update([
                'status' => $result['status'],
                'gateway_reference' => $result['gateway_reference'],
                'raw_response' => $result['raw'],
            ]);
            $this->syncOrderPaymentStatus($transaction);

            Log::channel('payments')->info('payment.initiated', [
                'transaction_id' => $transaction->id, 'gateway' => $method, 'amount' => $amount,
            ]);

            return $transaction->fresh();
        });
    }

    /**
     * Server-side re-verification (Rule: never trust a client-reported
     * payment status). Also idempotent: if the transaction is already in
     * a terminal state, this is a no-op read rather than reprocessing —
     * exactly what prevents a duplicate webhook delivery from re-firing
     * whatever side effects a status change triggers (order confirmation,
     * stock conversion, notifications) once Phase 11 wires those in.
     */
    public function verify(PaymentTransaction $transaction): PaymentTransaction
    {
        return DB::transaction(function () use ($transaction): PaymentTransaction {
            $transaction = PaymentTransaction::query()->whereKey($transaction->id)->lockForUpdate()->firstOrFail();

            if ($transaction->isTerminal()) {
                return $transaction;
            }

            $gateway = $this->resolveGateway($transaction->gateway);
            $result = $gateway->verify($transaction);

            if ($result['status'] === $transaction->status) {
                return $transaction;
            }

            $transaction->update([
                'status' => $result['status'],
                'raw_response' => array_merge($transaction->raw_response ?? [], $result['raw']),
            ]);
            $this->syncOrderPaymentStatus($transaction);

            Log::channel('payments')->info('payment.verified', [
                'transaction_id' => $transaction->id, 'status' => $result['status'],
            ]);

            return $transaction->fresh();
        });
    }

    /**
     * The only way a manual gateway's (COD, Bank Transfer) transaction
     * reaches 'paid' — there's no webhook to trigger it automatically.
     * Audited since it's a sensitive admin action (Rule 9).
     */
    public function confirmManualPayment(PaymentTransaction $transaction, User $actor): PaymentTransaction
    {
        return DB::transaction(function () use ($transaction, $actor): PaymentTransaction {
            $transaction = PaymentTransaction::query()->whereKey($transaction->id)->lockForUpdate()->firstOrFail();
            $gateway = $this->resolveGateway($transaction->gateway);

            if ($gateway->supportsAutomaticVerification()) {
                throw ValidationException::withMessages([
                    'transaction' => ['This gateway supports automatic verification — use verify(), not manual confirmation.'],
                ]);
            }

            if ($transaction->isTerminal() || $transaction->isSuccessful()) {
                throw ValidationException::withMessages([
                    'transaction' => ['This transaction is already in a terminal state ('.$transaction->status.').'],
                ]);
            }

            $transaction->update(['status' => 'paid', 'paid_at' => $transaction->paid_at ?: now()]);
            $this->syncOrderPaymentStatus($transaction);

        $this->auditLogger->log(
            actor: $actor, action: 'payment_confirmed', module: 'Payments',
            entityType: 'PaymentTransaction', entityId: (string) $transaction->id,
            newValue: ['status' => 'paid', 'amount' => $transaction->amount],
        );

        Log::channel('payments')->info('payment.manually_confirmed', [
            'transaction_id' => $transaction->id, 'actor_id' => $actor->id,
        ]);

            return $transaction->fresh();
        });
    }

    public function rejectManualPayment(PaymentTransaction $transaction, User $actor, string $reason): PaymentTransaction
    {
        return DB::transaction(function () use ($transaction, $actor, $reason): PaymentTransaction {
            $transaction = PaymentTransaction::query()->whereKey($transaction->id)->lockForUpdate()->firstOrFail();
            if ($transaction->isTerminal()) {
                throw ValidationException::withMessages(['transaction' => ['This transaction is already closed.']]);
            }
            $transaction->update(['status' => 'failed', 'failure_reason' => $reason]);
            $this->syncOrderPaymentStatus($transaction);
            $this->auditLogger->log(
            actor: $actor, action: 'payment_rejected', module: 'Payments',
            entityType: 'PaymentTransaction', entityId: (string) $transaction->id,
            newValue: ['status' => 'failed', 'reason' => $reason],
            );
            return $transaction->fresh();
        });
    }

    /**
     * Called from OrderService when an order is cancelled while its
     * payment is still non-terminal (pending/initiated) — marks the
     * transaction cancelled too, so it doesn't sit forever in "pending"
     * lists confusing whoever reviews them. Does nothing if the
     * transaction already reached a terminal state (in particular,
     * 'paid' — cancelling an order whose payment already succeeded needs
     * a real refund, not a status flip, and that's handled separately by
     * whoever calls refund() explicitly).
     */
    public function cancelPending(PaymentTransaction $transaction): void
    {
        DB::transaction(function () use ($transaction): void {
            $transaction = PaymentTransaction::query()->whereKey($transaction->id)->lockForUpdate()->firstOrFail();
            if ($transaction->isTerminal()) {
                return;
            }

            $transaction->update(['status' => 'cancelled']);
            $this->syncOrderPaymentStatus($transaction);
        });
    }

    public function refund(PaymentTransaction $transaction, string $amount, User $actor): PaymentTransaction
    {
        return DB::transaction(function () use ($transaction, $amount, $actor): PaymentTransaction {
        $transaction = PaymentTransaction::query()->whereKey($transaction->id)->lockForUpdate()->firstOrFail();
        if (! $transaction->isSuccessful()) {
            throw ValidationException::withMessages([
                'transaction' => ['Only a paid transaction can be refunded.'],
            ]);
        }

        if (bccomp($amount, '0.00', 2) <= 0) {
            throw ValidationException::withMessages([
                'amount' => ['Refund amount must be greater than zero.'],
            ]);
        }

        $alreadyRefunded = (string) $transaction->refunded_amount;
        $newTotal = bcadd($alreadyRefunded, $amount, 2);

        if (bccomp($newTotal, (string) $transaction->amount, 2) > 0) {
            throw ValidationException::withMessages([
                'amount' => ['Refund amount would exceed the original payment amount.'],
            ]);
        }

        $gateway = $this->resolveGateway($transaction->gateway);
        $result = $gateway->refund($transaction, $amount);

        $isFullRefund = bccomp($newTotal, (string) $transaction->amount, 2) === 0;

        $transaction->update([
            'status' => $isFullRefund ? 'refunded' : 'partially_refunded',
            'refunded_amount' => $newTotal,
            'raw_response' => array_merge($transaction->raw_response ?? [], $result['raw']),
        ]);
        $this->syncOrderPaymentStatus($transaction);

        $this->auditLogger->log(
            actor: $actor, action: 'refunded', module: 'Payments',
            entityType: 'PaymentTransaction', entityId: (string) $transaction->id,
            newValue: ['refunded_amount' => $newTotal, 'full' => $isFullRefund],
        );

        Log::channel('payments')->info('payment.refunded', [
            'transaction_id' => $transaction->id, 'amount' => $amount, 'actor_id' => $actor->id,
        ]);

        return $transaction->fresh();
        });
    }

    private function syncOrderPaymentStatus(PaymentTransaction $transaction): void
    {
        if (! $transaction->order_id) {
            return;
        }

        $status = match ($transaction->status) {
            'paid' => 'paid',
            'partially_refunded' => 'partially_refunded',
            'refunded' => 'refunded',
            'authorized' => 'authorized',
            'failed' => 'failed',
            'cancelled' => 'cancelled',
            default => 'pending',
        };

        $transaction->order()->update(['payment_status' => $status]);
    }

    private function resolveGateway(string $method): PaymentGatewayInterface
    {
        $config = config("payments.methods.{$method}");

        if ($config === null || ! ($config['enabled'] ?? false)
            || ! app(SettingService::class)->boolean("payments.{$method}.enabled", $config['enabled'] ?? false)
            || empty($config['gateway'])) {
            throw ValidationException::withMessages([
                'method' => ["The payment method \"{$method}\" is not currently available."],
            ]);
        }

        return app($config['gateway']);
    }
}
