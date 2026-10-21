<?php

namespace App\Domain\Marketing\Models;

use Illuminate\Database\Eloquent\Model;

class MarketingCampaign extends Model
{
    protected $fillable = ['name', 'subject', 'body', 'recipients', 'status', 'sent_count', 'error_message', 'contact_list_ids'];

    protected function casts(): array
    {
        return ['recipients' => 'array', 'contact_list_ids' => 'array'];
    }
}
