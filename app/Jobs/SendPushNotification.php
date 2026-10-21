<?php

namespace App\Jobs;

use App\Domain\Notifications\Models\NotificationDelivery;
use App\Domain\Notifications\Services\FirebasePushService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendPushNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public function __construct(public int $deliveryId) {}

    public function handle(FirebasePushService $firebase): void
    {
        $delivery = NotificationDelivery::with(['subscription', 'campaign'])->find($this->deliveryId);
        if (! $delivery || $delivery->status === 'sent') return;
        try {
            $firebase->send($delivery);
            $delivery->update(['status' => 'sent', 'sent_at' => now(), 'delivered_at' => now(), 'error' => null]);
            $delivery->campaign?->increment('sent_count');
        } catch (\Throwable $exception) {
            $delivery->update(['status' => 'failed', 'error' => $exception->getMessage()]);
            $delivery->campaign?->increment('failed_count');
            report($exception);
        }
        if ($delivery->campaign) {
            $campaign = $delivery->campaign->fresh();
            $done = $campaign->deliveries()->whereIn('status', ['sent', 'failed'])->count();
            if ($campaign->recipient_count > 0 && $done >= $campaign->recipient_count) $campaign->update(['status' => 'sent', 'completed_at' => now()]);
        }
    }
}
