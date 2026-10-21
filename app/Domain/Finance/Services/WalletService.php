<?php

namespace App\Domain\Finance\Services;

use App\Domain\Finance\Models\WalletTransaction;
use App\Domain\Finance\Models\Withdrawal;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class WalletService
{
    public function balance(User $entrepreneur, bool $availableOnly = true): string
    {
        $query = $entrepreneur->walletTransactions()->where('status', 'posted');

        if ($availableOnly) {
            $query->whereIn('type', ['sale_profit', 'bonus', 'referral_incentive', 'adjustment', 'withdraw', 'refund']);
        }

        return bcadd((string) $query->sum('amount'), '0', 2);
    }

    public function transactionsFor(int $entrepreneurId)
    {
        return WalletTransaction::query()->where('entrepreneur_id', $entrepreneurId);
    }

    public function post(
        User $entrepreneur,
        string $type,
        string $amount,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?string $description = null,
        string $status = 'posted',
    ): WalletTransaction {
        return $entrepreneur->walletTransactions()->create([
            'type' => $type,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'amount' => $amount,
            'status' => $status,
            'description' => $description,
        ]);
    }

    public function requestWithdrawal(User $entrepreneur, string $amount, string $method, string $accountNumber): Withdrawal
    {
        if (bccomp($amount, '0', 2) <= 0) {
            throw ValidationException::withMessages(['amount' => ['Withdrawal amount must be greater than zero.']]);
        }

        return DB::transaction(function () use ($entrepreneur, $amount, $method, $accountNumber) {
            $entrepreneur = User::query()
                ->whereKey($entrepreneur->id)
                ->lockForUpdate()
                ->firstOrFail();

            $available = $this->balance($entrepreneur);
            $reserved = (string) $entrepreneur->withdrawals()
                ->whereIn('status', ['pending', 'approved', 'processing'])
                ->sum('amount');

            if (bccomp(bcsub($available, $reserved, 2), $amount, 2) < 0) {
                throw ValidationException::withMessages(['amount' => ['Withdrawal exceeds available balance.']]);
            }

            return $entrepreneur->withdrawals()->create([
                'amount' => $amount,
                'method' => $method,
                'account_number' => $accountNumber,
                'status' => 'pending',
                'requested_at' => now(),
            ]);
        });
    }

    public function processWithdrawal(Withdrawal $withdrawal, string $status, ?string $notes = null): Withdrawal
    {
        if (! in_array($status, ['approved', 'rejected'], true)) {
            throw ValidationException::withMessages(['status' => ['Withdrawal status is invalid.']]);
        }

        return DB::transaction(function () use ($withdrawal, $status, $notes) {
            $withdrawal = Withdrawal::query()->whereKey($withdrawal->id)->lockForUpdate()->firstOrFail();

            if ($withdrawal->status !== 'pending') {
                throw ValidationException::withMessages(['status' => ['Only pending withdrawals can be processed.']]);
            }

            if ($status === 'approved') {
                $this->post(
                    $withdrawal->entrepreneur,
                    'withdraw',
                    '-'.number_format((float) $withdrawal->amount, 2, '.', ''),
                    'Withdrawal',
                    $withdrawal->id,
                    'Withdrawal approved.',
                );
            }

            $withdrawal->update([
                'status' => $status,
                'notes' => $notes,
                'processed_at' => now(),
            ]);

            return $withdrawal->fresh();
        });
    }
}
