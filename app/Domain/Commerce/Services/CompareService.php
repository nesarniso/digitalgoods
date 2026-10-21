<?php

namespace App\Domain\Commerce\Services;

use App\Domain\Catalog\Models\Product;
use App\Domain\Commerce\Models\CompareItem;
use App\Domain\Settings\Services\SettingService;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class CompareService
{
    private const DEFAULT_MAX_ITEMS = 4;

    public function __construct(
        private readonly SettingService $settings,
    ) {}

    public function add(User $user, Product $product): CompareItem
    {
        $existing = CompareItem::query()
            ->where('user_id', $user->id)
            ->where('product_id', $product->id)
            ->first();

        if ($existing) {
            return $existing;
        }

        $currentCount = CompareItem::query()->where('user_id', $user->id)->count();
        $max = (int) $this->settings->get('compare_max_items', self::DEFAULT_MAX_ITEMS);

        if ($currentCount >= $max) {
            throw ValidationException::withMessages([
                'product_id' => ["You can compare at most {$max} products at a time. Remove one before adding another."],
            ]);
        }

        return CompareItem::create(['user_id' => $user->id, 'product_id' => $product->id]);
    }

    public function remove(User $user, Product $product): void
    {
        CompareItem::query()
            ->where('user_id', $user->id)
            ->where('product_id', $product->id)
            ->delete();
    }

    public function list(User $user)
    {
        return CompareItem::query()
            ->where('user_id', $user->id)
            ->with('product.images')
            ->latest('id')
            ->get();
    }
}
