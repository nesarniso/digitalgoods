<?php

namespace Database\Seeders;

use App\Domain\Shipping\Models\ShippingMethod;
use App\Domain\Shipping\Models\ShippingRate;
use App\Domain\Shipping\Models\ShippingZone;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ShippingSeeder extends Seeder
{
    public function run(): void
    {
        $method = ShippingMethod::firstOrCreate(
            ['name' => 'Standard delivery'],
            ['uuid' => (string) Str::uuid(), 'type' => 'flat', 'is_active' => true, 'sort_order' => 1],
        );

        $dhaka = ShippingZone::firstOrCreate(
            ['name' => 'Inside Dhaka'],
            [
                'uuid' => (string) Str::uuid(),
                'country' => 'Bangladesh',
                'district' => 'Dhaka',
                'is_active' => true,
            ],
        );
        $outsideDhaka = ShippingZone::firstOrCreate(
            ['name' => 'Outside Dhaka'],
            ['uuid' => (string) Str::uuid(), 'is_active' => true],
        );

        ShippingRate::updateOrCreate(
            ['shipping_method_id' => $method->id, 'shipping_zone_id' => null],
            ['base_rate' => 100, 'per_kg_rate' => null, 'free_above_amount' => null],
        );
        ShippingRate::updateOrCreate(
            ['shipping_method_id' => $method->id, 'shipping_zone_id' => $dhaka->id],
            ['base_rate' => 50, 'per_kg_rate' => null, 'free_above_amount' => null],
        );
        ShippingRate::updateOrCreate(
            ['shipping_method_id' => $method->id, 'shipping_zone_id' => $outsideDhaka->id],
            ['base_rate' => 100, 'per_kg_rate' => null, 'free_above_amount' => null],
        );

        $free = ShippingMethod::firstOrCreate(
            ['name' => 'Free delivery'],
            ['uuid' => (string) Str::uuid(), 'type' => 'free', 'is_active' => true, 'sort_order' => 2],
        );

        ShippingRate::updateOrCreate(
            ['shipping_method_id' => $free->id, 'shipping_zone_id' => null],
            ['base_rate' => 0, 'per_kg_rate' => null, 'free_above_amount' => null],
        );
        $free->update(['is_active' => false]);
    }
}
