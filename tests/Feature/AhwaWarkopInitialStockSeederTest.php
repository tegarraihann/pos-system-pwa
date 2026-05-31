<?php

namespace Tests\Feature;

use App\Models\Ingredient;
use App\Models\StockLevel;
use App\Models\StockLocation;
use App\Models\StockMovement;
use Database\Seeders\AhwaWarkopInitialStockSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AhwaWarkopInitialStockSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_seeds_opening_stock_for_ahwa_warkop_location(): void
    {
        $this->seed(AhwaWarkopInitialStockSeeder::class);

        $location = StockLocation::query()->where('code', 'AHWA-WARKOP')->firstOrFail();
        $movement = StockMovement::query()->where('reference_no', 'OPENING-AHWA-2026-05')->firstOrFail();
        $coffeeBean = Ingredient::query()->where('name', 'Biji Kopi Blend Espresso')->firstOrFail();
        $indomie = Ingredient::query()->where('name', 'Mie Instan Goreng Pack')->firstOrFail();

        $this->assertSame(StockMovement::TYPE_IN, $movement->type);
        $this->assertSame($location->id, $movement->to_location_id);
        $this->assertSame(32, $movement->items()->count());

        $this->assertSame(
            2500.0,
            StockLevel::getOnHand($location->id, Ingredient::class, $coffeeBean->id)
        );

        $this->assertSame(
            72.0,
            StockLevel::getOnHand($location->id, Ingredient::class, $indomie->id)
        );
    }
}
