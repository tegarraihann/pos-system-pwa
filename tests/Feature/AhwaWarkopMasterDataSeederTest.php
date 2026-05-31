<?php

namespace Tests\Feature;

use App\Models\Menu;
use App\Models\MenuVariant;
use App\Models\StockLocation;
use Database\Seeders\AhwaWarkopMasterDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AhwaWarkopMasterDataSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_seeds_actual_master_menu_data_from_ahwa_warkop(): void
    {
        $this->seed(AhwaWarkopMasterDataSeeder::class);

        $this->assertDatabaseHas('stock_locations', [
            'code' => 'AHWA-WARKOP',
            'name' => 'AHWA Warkop',
        ]);

        $this->assertSame(60, Menu::query()->count());
        $this->assertSame(60, MenuVariant::query()->count());

        $this->assertDatabaseHas('menus', [
            'name' => 'PRIMA 600 ML',
            'category' => 'Air Mineral',
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('menu_variants', [
            'kd_varian' => 'AHWA-043',
            'price' => 5000,
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('menus', [
            'name' => 'Nasi Goreng Ahwa',
            'is_active' => false,
        ]);
    }
}
