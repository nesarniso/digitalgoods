<?php

namespace App\Domain\Finance\Services;

use App\Domain\Orders\Models\Order;
use App\Domain\Orders\Models\ReturnRequest;
use App\Domain\Catalog\Models\Product;
use App\Domain\Settings\Services\SettingService;
use Illuminate\Support\Facades\DB;

class ProfitService
{
    public function __construct(
        private readonly WalletService $wallet,
        private readonly SettingService $settings,
    ) {}

    public function entrepreneurProfit(string $costPrice, string $sellingPrice, string $applicableCost = '0', ?float $percentage = null, string $flatRate = '0'): string
    {
        $percentage ??= $this->percentage();
        $profit = bcadd(
            bcmul($costPrice, bcdiv((string) $percentage, '100', 4), 2),
            max('0', $flatRate),
            2
        );
        $availableMargin = $this->calculate($sellingPrice, $costPrice, $applicableCost);

        return bccomp($profit, $availableMargin, 2) > 0 ? max('0', $availableMargin) : $profit;
    }

    public function percentage(): float
    {
        return max(0, min(100, (float) $this->settings->get('entrepreneur.profit_percent', 20)));
    }

    public function percentageForProduct(?Product $product): float
    {
        if ($product?->entrepreneur_profit_percent !== null) {
            return max(0, min(100, (float) $product->entrepreneur_profit_percent));
        }

        // A flat product rate intentionally disables the global percentage.
        return $product?->entrepreneur_profit_flat_rate !== null
            ? 0
            : $this->percentage();
    }

    public function flatRateForProduct(?Product $product): string
    {
        return $product?->entrepreneur_profit_flat_rate !== null
            ? max('0', (string) $product->entrepreneur_profit_flat_rate)
            : '0.00';
    }

    public function calculate(string $sellingPrice, string $costPrice, string $applicableCost = '0'): string
    {
        return bcsub(bcsub($sellingPrice, $costPrice, 2), $applicableCost, 2);
    }

    public function createPending(Order $order, string $applicableCost = '0'): void
    {
        $order->loadMissing(['items', 'entrepreneur']);

        if (! $order->entrepreneur_id || $order->items()->where('profit_status', '!=', 'none')->exists()) {
            return;
        }

        DB::transaction(function () use ($order, $applicableCost) {
            $total = '0';
            foreach ($order->items as $item) {
                $revenue = bcmul((string) $item->unit_price, (string) $item->quantity, 2);
                $supplierCost = $item->supplier_cost ?? ($item->cost_price === null
                    ? $revenue
                    : bcmul((string) $item->cost_price, (string) $item->quantity, 2));
                $landedCost = bcadd((string) ($item->delivery_cost ?? 0), (string) ($item->return_cost ?? 0), 2);
                $profit = $item->entrepreneur_profit ?? $this->entrepreneurProfit(
                    $supplierCost,
                    $revenue,
                    bcadd($landedCost, $applicableCost, 2),
                    $item->entrepreneur_profit_percent !== null ? (float) $item->entrepreneur_profit_percent : null,
                    (string) ($item->entrepreneur_profit_flat_rate ?? 0),
                );
                $item->forceFill([
                    'applicable_cost' => bcadd($landedCost, $applicableCost, 2),
                    'entrepreneur_profit' => $profit,
                    'profit' => $profit,
                    'profit_status' => 'pending',
                ])->save();
                $total = bcadd($total, $profit, 2);
            }

            if (bccomp($total, '0', 2) > 0) {
                $this->wallet->post($order->entrepreneur, 'sale_profit', $total, 'Order', $order->id, 'Profit pending return verification.', 'pending');
            }
        });
    }

    public function release(Order $order): void
    {
        $order->loadMissing('entrepreneur');

        DB::transaction(function () use ($order) {
            $transaction = $order->entrepreneur?->walletTransactions()
                ->where('reference_type', 'Order')
                ->where('reference_id', $order->id)
                ->where('type', 'sale_profit')
                ->where('status', 'pending')
                ->first();

            if ($transaction) {
                $transaction->update(['status' => 'posted', 'description' => 'Profit released after successful sale verification.']);
                $order->items()->where('profit_status', 'pending')->update(['profit_status' => 'available']);
            }
        });
    }

    public function reverseForReturn(ReturnRequest $returnRequest): void
    {
        $returnRequest->loadMissing(['item', 'order']);
        $item = $returnRequest->item;
        $order = $returnRequest->order;

        if (! $order?->entrepreneur_id || ! $item || (int) $returnRequest->quantity < 1) {
            return;
        }

        DB::transaction(function () use ($returnRequest, $item, $order) {
            $exists = $this->wallet->transactionsFor($order->entrepreneur_id)
                ->where('reference_type', 'ReturnRequest')
                ->where('reference_id', $returnRequest->id)
                ->where('type', 'refund')
                ->exists();

            if ($exists) {
                return;
            }

            $unitProfit = bcdiv((string) $item->profit, (string) max(1, $item->quantity), 2);
            $reversal = bcmul($unitProfit, (string) $returnRequest->quantity, 2);
            if (bccomp($reversal, '0', 2) > 0) {
                $this->wallet->post(
                    $order->entrepreneur,
                    'refund',
                    '-'.$reversal,
                    'ReturnRequest',
                    $returnRequest->id,
                    'Profit reversed for refunded return.',
                );
            }
        });
    }
}
