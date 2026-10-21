<?php

namespace Database\Seeders;

use App\Domain\Settings\Models\FeatureFlag;
use Illuminate\Database\Seeder;

class FeatureFlagSeeder extends Seeder
{
    public function run(): void
    {
        $flags = [
            // Business mode: single-vendor at launch, per your Phase 0
            // decision. Product/order schema is already vendor-ready, so
            // this can flip to true later without a migration.
            ['key' => 'multi_vendor', 'enabled' => false, 'description' => 'Enable vendor marketplace features.'],

            ['key' => 'wishlist', 'enabled' => true, 'description' => 'Customer wishlist.'],
            ['key' => 'reviews', 'enabled' => true, 'description' => 'Product reviews.'],
            ['key' => 'compare', 'enabled' => true, 'description' => 'Product compare.'],
            ['key' => 'wallet', 'enabled' => false, 'description' => 'Customer wallet.'],
            ['key' => 'loyalty', 'enabled' => false, 'description' => 'Loyalty points.'],
            ['key' => 'referral', 'enabled' => false, 'description' => 'Referral program.'],
            ['key' => 'affiliate', 'enabled' => false, 'description' => 'Affiliate program.'],
            ['key' => 'digital_products', 'enabled' => true, 'description' => 'Digital/downloadable products.'],
            ['key' => 'pos', 'enabled' => false, 'description' => 'Point of sale.'],
            ['key' => 'abandoned_cart', 'enabled' => false, 'description' => 'Abandoned cart recovery.'],
            ['key' => 'auction', 'enabled' => false, 'description' => 'Auction add-on.'],
            ['key' => 'ai_module', 'enabled' => false, 'description' => 'AI-assisted content/recommendations.'],
        ];

        foreach ($flags as $flag) {
            FeatureFlag::query()->updateOrCreate(['key' => $flag['key']], $flag);
        }
    }
}
