<?php

namespace App\Console\Commands;

use App\Domain\Commerce\Models\Cart;
use Illuminate\Console\Command;

class PruneExpiredCarts extends Command
{
    protected $signature = 'cart:prune-expired';

    protected $description = 'Delete carts (and their items) past their expires_at timestamp.';

    public function handle(): int
    {
        $count = Cart::query()->where('expires_at', '<', now())->count();

        Cart::query()->where('expires_at', '<', now())->delete();

        $this->info("Pruned {$count} expired cart(s).");

        return self::SUCCESS;
    }
}
