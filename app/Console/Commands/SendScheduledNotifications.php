<?php

namespace App\Console\Commands;

use App\Domain\Notifications\Models\NotificationCampaign;
use App\Domain\Notifications\Services\NotificationService;
use Illuminate\Console\Command;

class SendScheduledNotifications extends Command
{
    protected $signature = 'notifications:send-scheduled';
    protected $description = 'Queue due push notification campaigns';

    public function handle(NotificationService $notifications): int
    {
        NotificationCampaign::where('status', 'scheduled')->where('scheduled_at', '<=', now())->orderBy('id')->chunkById(50, function ($campaigns) use ($notifications): void {
            foreach ($campaigns as $campaign) {
                $campaign->update(['status' => 'queued', 'queued_at' => now()]);
                $notifications->broadcast($campaign);
            }
        });
        return self::SUCCESS;
    }
}
