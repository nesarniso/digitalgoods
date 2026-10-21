<?php

namespace App\Console\Commands;

use App\Domain\Commerce\Models\Cart;
use App\Domain\Inventory\Models\Inventory;
use App\Domain\Notifications\Services\NotificationService;
use Illuminate\Console\Command;

class SendCommercePushAutomations extends Command
{
    protected $signature = 'notifications:send-automations';
    protected $description = 'Send abandoned-cart and low-stock push reminders';

    public function handle(NotificationService $notifications): int
    {
        Cart::with('user')->whereNotNull('user_id')->whereNull('abandoned_notified_at')->where('updated_at', '<=', now()->subHours(24))->whereHas('items')->chunkById(100, function ($carts) use ($notifications): void {
            foreach ($carts as $cart) {
                if ($cart->user) $notifications->queueForUser($cart->user, 'Your cart is waiting', 'Complete your purchase before items sell out.', url('/cart'));
                $cart->update(['abandoned_notified_at' => now()]);
            }
        });
        Inventory::with('product')->where(function ($query): void {
            $query->whereColumn('physical_stock', '<=', 'reserved_stock')
                ->orWhereRaw('(physical_stock - reserved_stock) <= low_stock_threshold');
        })->chunkById(100, function ($inventories) use ($notifications): void {
            foreach ($inventories as $inventory) {
                $product = $inventory->product;
                if (! $product) continue;
                \App\Models\User::whereHas('pushSubscriptions', fn ($q) => $q->whereNull('revoked_at'))->each(fn ($user) => $notifications->queueForUser($user, 'Low stock alert', "{$product->name} is almost sold out.", url('/products/'.$product->slug)));
            }
        });
        return self::SUCCESS;
    }
}
