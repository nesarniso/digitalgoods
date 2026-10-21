<?php

namespace App\Domain\Commerce\Services;

use App\Domain\Catalog\Models\Product;
use App\Domain\Commerce\Models\WishlistItem;
use App\Models\User;

class WishlistService
{
    public function add(User $user, Product $product): WishlistItem
    {
        return WishlistItem::query()->firstOrCreate([
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);
    }

    public function remove(User $user, Product $product): void
    {
        WishlistItem::query()
            ->where('user_id', $user->id)
            ->where('product_id', $product->id)
            ->delete();
    }

    public function list(User $user)
    {
        return WishlistItem::query()
            ->where('user_id', $user->id)
            ->with('product.images', 'product.variants.attributeValues.attribute')
            ->latest('id')
            ->get();
    }
}
