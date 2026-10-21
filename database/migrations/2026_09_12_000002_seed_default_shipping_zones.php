<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $method = DB::table('shipping_methods')->where('name', 'Standard delivery')->first();
        if (! $method) {
            $methodId = DB::table('shipping_methods')->insertGetId([
                'uuid' => (string) Str::uuid(),
                'name' => 'Standard delivery',
                'type' => 'flat',
                'is_active' => true,
                'sort_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $methodId = $method->id;
            DB::table('shipping_methods')->where('id', $methodId)->update([
                'type' => 'flat',
                'is_active' => true,
                'updated_at' => now(),
            ]);
        }

        $dhakaId = $this->zone('Inside Dhaka', 'Bangladesh', 'Dhaka');
        $outsideDhakaId = $this->zone('Outside Dhaka');

        $this->rate($methodId, null, 100);
        $this->rate($methodId, $dhakaId, 50);
        $this->rate($methodId, $outsideDhakaId, 100);

        DB::table('shipping_methods')
            ->where('name', 'Free delivery')
            ->update(['is_active' => false, 'updated_at' => now()]);
    }

    private function zone(string $name, ?string $country = null, ?string $district = null): int
    {
        $zone = DB::table('shipping_zones')->where('name', $name)->first();
        if ($zone) {
            return $zone->id;
        }

        return DB::table('shipping_zones')->insertGetId([
            'uuid' => (string) Str::uuid(),
            'name' => $name,
            'country' => $country,
            'division' => null,
            'district' => $district,
            'area' => null,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function rate(int $methodId, ?int $zoneId, int $amount): void
    {
        DB::table('shipping_rates')->updateOrInsert(
            ['shipping_method_id' => $methodId, 'shipping_zone_id' => $zoneId],
            ['base_rate' => $amount, 'per_kg_rate' => null, 'free_above_amount' => null, 'updated_at' => now(), 'created_at' => now()],
        );
    }

    public function down(): void
    {
        // Default shipping configuration is intentionally retained on rollback
        // so existing orders and administrator edits are not disrupted.
    }
};
