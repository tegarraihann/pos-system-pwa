<?php

namespace Database\Seeders;

use App\Models\StockLocation;
use Illuminate\Database\Seeder;

class StockLocationsSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $locations = [
            ['code' => 'GUDANG', 'name' => 'Gudang', 'type' => 'gudang', 'is_active' => true],
            ['code' => 'DAPUR', 'name' => 'Dapur', 'type' => 'dapur', 'is_active' => true],
            ['code' => 'OUTLET-A', 'name' => 'Outlet A', 'type' => 'outlet', 'is_active' => true],
        ];

        foreach ($locations as $location) {
            StockLocation::query()->firstOrCreate(
                ['code' => $location['code']],
                $location,
            );
        }
    }
}
