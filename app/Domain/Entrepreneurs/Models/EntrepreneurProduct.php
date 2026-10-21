<?php

namespace App\Domain\Entrepreneurs\Models;

use App\Domain\Catalog\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EntrepreneurProduct extends Model
{
    protected $fillable = ['entrepreneur_id', 'product_id', 'status', 'custom_selling_price'];

    protected function casts(): array
    {
        return ['custom_selling_price' => 'decimal:2'];
    }

    public function entrepreneur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'entrepreneur_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function linkClicks(): HasMany
    {
        return $this->hasMany(EntrepreneurLinkClick::class, 'product_id', 'product_id')
            ->whereColumn('entrepreneur_link_clicks.entrepreneur_id', 'entrepreneur_products.entrepreneur_id');
    }
}
