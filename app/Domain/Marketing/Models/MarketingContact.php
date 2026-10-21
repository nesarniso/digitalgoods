<?php

namespace App\Domain\Marketing\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarketingContact extends Model
{
    protected $fillable = ['contact_list_id', 'name', 'email'];

    public function contactList(): BelongsTo
    {
        return $this->belongsTo(MarketingContactList::class, 'contact_list_id');
    }
}
