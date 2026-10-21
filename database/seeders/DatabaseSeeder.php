<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            FeatureFlagSeeder::class,
            ShippingSeeder::class,
            DefaultCatalogSeeder::class,
            EmailTemplateSeeder::class,
            PageSeeder::class,
        ]);
    }
}
