<?php

namespace Tests\Feature;

use App\Models\Ingredient;
use App\Models\IngredientCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IngredientCodeGenerationTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_generates_ingredient_code_automatically_when_code_is_empty(): void
    {
        $category = IngredientCategory::query()->create([
            'name' => 'Powder',
        ]);

        $firstIngredient = Ingredient::query()->create([
            'name' => 'Gula Pasir',
            'unit' => 'Kg',
            'ingredient_category_id' => $category->id,
            'is_active' => true,
        ]);

        $secondIngredient = Ingredient::query()->create([
            'name' => 'Susu Cair',
            'unit' => 'Liter',
            'ingredient_category_id' => $category->id,
            'is_active' => true,
        ]);

        $this->assertSame('ING-0001', $firstIngredient->code);
        $this->assertSame('ING-0002', $secondIngredient->code);
    }
}
