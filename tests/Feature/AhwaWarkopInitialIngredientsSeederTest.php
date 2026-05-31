<?php

namespace Tests\Feature;

use App\Models\Ingredient;
use App\Models\IngredientCategory;
use App\Models\Supplier;
use Database\Seeders\AhwaWarkopInitialIngredientsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AhwaWarkopInitialIngredientsSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_seeds_initial_ingredient_master_data_for_ahwa_warkop(): void
    {
        $this->seed(AhwaWarkopInitialIngredientsSeeder::class);

        $this->assertSame(9, IngredientCategory::query()->count());
        $this->assertSame(4, Supplier::query()->count());
        $this->assertSame(32, Ingredient::query()->count());

        $this->assertDatabaseHas('ingredients', [
            'name' => 'Biji Kopi Blend Espresso',
            'unit' => 'gram',
            'purchase_price' => 280,
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('ingredients', [
            'name' => 'Mie Instan Goreng Pack',
            'unit' => 'pcs',
            'purchase_price' => 3500,
        ]);

        $this->assertDatabaseHas('ingredient_categories', [
            'name' => 'Powder & Tea',
        ]);
    }
}
