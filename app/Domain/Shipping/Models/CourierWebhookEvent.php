<?php

namespace App\Domain\Shipping\Models;

use Illuminate\Database\Eloquent\Model;

class CourierWebhookEvent extends Model
{
    protected $table = 'courier_webhook_events';

    protected $fillable = ['courier', 'event_id', 'payload', 'verified'];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'verified' => 'boolean',
        ];
    }
}
