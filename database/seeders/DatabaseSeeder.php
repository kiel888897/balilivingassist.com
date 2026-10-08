<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call([
            BlaSeeder::class,
            SampleProductsSeeder::class,
            RentalSamplesSeeder::class,
            PortfolioSamplesSeeder::class,
            RolePermissionSeeder::class,
            DeliverySeeder::class,
        ]);
    }
}
