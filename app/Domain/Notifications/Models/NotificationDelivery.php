<?php

namespace App\Domain\Notifications\Models;

use App\Domain\Settings\Models\PushSubscription;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationDelivery extends Model
{
    protected $fillable = ['notification_campaign_id', 'user_id', 'push_subscription_id', 'device_id', 'title', 'body', 'url', 'destination_url', 'status', 'sent_at', 'delivered_at', 'clicked_at', 'error'];
    protected function casts(): array { return ['sent_at' => 'datetime', 'delivered_at' => 'datetime', 'clicked_at' => 'datetime']; }
    public function campaign(): BelongsTo { return $this->belongsTo(NotificationCampaign::class, 'notification_campaign_id'); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function subscription(): BelongsTo { return $this->belongsTo(PushSubscription::class, 'push_subscription_id'); }
}
