<?php

namespace App\Domain\Notifications\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NotificationCampaign extends Model
{
    protected $fillable = ['created_by', 'title', 'body', 'url', 'audience', 'status', 'recipient_count', 'sent_count', 'failed_count', 'queued_at', 'scheduled_at', 'completed_at'];
    protected function casts(): array { return ['queued_at' => 'datetime', 'scheduled_at' => 'datetime', 'completed_at' => 'datetime']; }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function deliveries(): HasMany { return $this->hasMany(NotificationDelivery::class); }
}
