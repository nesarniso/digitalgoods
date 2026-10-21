<?php

namespace App\Domain\Notifications\Services;

use App\Domain\Notifications\Models\NotificationCampaign;
use App\Domain\Notifications\Models\NotificationDelivery;
use App\Domain\Settings\Models\PushSubscription;
use App\Jobs\SendPushNotification;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class NotificationService
{
    public function queueForUser(User $user, string $title, string $body, ?string $url = null, ?NotificationCampaign $campaign = null): int
    {
        $subscriptions = $user->pushSubscriptions()->whereNull('revoked_at')->get();
        foreach ($subscriptions as $subscription) $this->queue($subscription, $title, $body, $url, $campaign);
        return $subscriptions->count();
    }

    public function queue(PushSubscription $subscription, string $title, string $body, ?string $url = null, ?NotificationCampaign $campaign = null): NotificationDelivery
    {
        $delivery = NotificationDelivery::create(['notification_campaign_id' => $campaign?->id, 'user_id' => $subscription->user_id, 'push_subscription_id' => $subscription->id, 'device_id' => $subscription->device_id, 'title' => $title, 'body' => $body, 'destination_url' => $url, 'status' => 'queued']);
        $delivery->update(['url' => \URL::signedRoute('notifications.delivery.click', $delivery)]);
        SendPushNotification::dispatch($delivery->id)->onQueue('push')->afterCommit();
        return $delivery;
    }

    public function broadcast(NotificationCampaign $campaign): void
    {
        $query = PushSubscription::query()->whereNull('revoked_at')->where(function ($q): void {
            $q->whereNull('user_id')->orWhereHas('user', fn ($user) => $user->whereDoesntHave('roles', fn ($roles) => $roles->whereIn('name', ['admin', 'super_admin'])));
        });
        if ($campaign->audience === 'customers') $query->whereNotNull('user_id');
        $count = 0;
        $query->chunkById(200, function ($subscriptions) use ($campaign, &$count): void {
            foreach ($subscriptions as $subscription) { $this->queue($subscription, $campaign->title, $campaign->body, $campaign->url, $campaign); $count++; }
        });
        $campaign->update(['recipient_count' => $count, 'status' => $count ? 'sending' : 'sent', 'completed_at' => $count ? null : now()]);
        if ($count && $campaign->deliveries()->whereIn('status', ['sent', 'failed'])->count() >= $count) {
            $campaign->update(['status' => 'sent', 'completed_at' => now()]);
        }
    }
}
