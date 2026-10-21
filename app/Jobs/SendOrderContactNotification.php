<?php

namespace App\Jobs;

use App\Domain\Notifications\Services\OrderContactNotificationService;
use App\Domain\Orders\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendOrderContactNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $orderId,
        public string $status,
    ) {}

    public function handle(OrderContactNotificationService $notifications): void
    {
        $order = Order::query()->find($this->orderId);
        if ($order) {
            $notifications->statusChanged($order, $this->status);
        }
    }
}
