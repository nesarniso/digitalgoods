<?php

namespace App\Domain\Marketing\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MarketingContactList extends Model
{
    protected $fillable = ['name', 'contact_count'];

    public function contacts(): HasMany
    {
        return $this->hasMany(MarketingContact::class, 'contact_list_id');
    }
}
