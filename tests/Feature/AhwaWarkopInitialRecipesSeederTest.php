<?php

namespace Tests\Feature;

use App\Models\MenuVariant;
use App\Models\Recipe;
use App\Models\RecipeItem;
use Database\Seeders\AhwaWarkopInitialRecipesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AhwaWarkopInitialRecipesSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_seeds_initial_recipes_for_priority_ahwa_warkop_menus(): void
    {
        $this->seed(AhwaWarkopInitialRecipesSeeder::class);

        $this->assertSame(27, Recipe::query()->count());

        $matchaVariant = MenuVariant::query()
            ->whereHas('menu', fn ($query) => $query->where('name', 'Matcha'))
            ->firstOrFail();

        $matchaRecipe = Recipe::query()->where('menu_variant_id', $matchaVariant->id)->firstOrFail();

        $this->assertSame(3, $matchaRecipe->items()->count());
        $this->assertDatabaseHas('recipe_items', [
            'recipe_id' => $matchaRecipe->id,
            'quantity' => 18,
        ]);

        $mixPlatterVariant = MenuVariant::query()
            ->whereHas('menu', fn ($query) => $query->where('name', 'MIX PLATTER'))
            ->firstOrFail();

        $mixPlatterRecipe = Recipe::query()->where('menu_variant_id', $mixPlatterVariant->id)->firstOrFail();
        $this->assertSame(7, $mixPlatterRecipe->items()->count());

        $indomieVariant = MenuVariant::query()
            ->whereHas('menu', fn ($query) => $query->where('name', 'INDOMIE GORENG'))
            ->firstOrFail();

        $indomieRecipe = Recipe::query()->where('menu_variant_id', $indomieVariant->id)->firstOrFail();
        $this->assertSame(1, $indomieRecipe->items()->count());

        $this->assertGreaterThan(0, RecipeItem::query()->count());
    }
}
