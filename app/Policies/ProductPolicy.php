<?php

namespace App\Policies;

use App\Domain\Catalog\Models\Product;
use App\Domain\Settings\Services\FeatureFlagService;
use App\Models\User;

/**
 * The first policy in the project (previous phases only needed flat
 * permission checks). Products need an actual ownership rule: while
 * multi_vendor is off, having the permission is sufficient — there's only
 * one seller. Once Phase 14 turns multi_vendor on, a vendor may only
 * touch their own products; admins with the permission can still touch
 * any product regardless of vendor mode.
 */
class ProductPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('product.view') || $user->hasAnyRole(['super_admin', 'admin']);
    }

    public function create(User $user): bool
    {
        return $user->can('product.create') || $user->hasAnyRole(['super_admin', 'admin']);
    }

    public function update(User $user, Product $product): bool
    {
        return $this->canManage($user, $product, 'product.update');
    }

    public function delete(User $user, Product $product): bool
    {
        return $this->canManage($user, $product, 'product.delete');
    }

    public function restore(User $user, Product $product): bool
    {
        return $this->canManage($user, $product, 'product.delete');
    }

    private function canManage(User $user, Product $product, string $permission): bool
    {
        if (! $user->can($permission) && ! $user->hasAnyRole(['super_admin', 'admin'])) {
            return false;
        }

        if ($user->hasRole(['super_admin', 'admin'])) {
            return true;
        }

        if (! app(FeatureFlagService::class)->isEnabled('multi_vendor')) {
            return true;
        }

        return $product->vendor_id !== null && $product->vendor_id === $user->id;
    }
}
