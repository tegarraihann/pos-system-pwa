<?php

namespace Database\Seeders;

use App\Models\Ingredient;
use App\Models\MenuVariant;
use App\Models\Recipe;
use App\Models\RecipeItem;
use Illuminate\Database\Seeder;

class AhwaWarkopInitialRecipesSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            AhwaWarkopMasterDataSeeder::class,
            AhwaWarkopInitialIngredientsSeeder::class,
        ]);

        $ingredientMap = Ingredient::query()->get()->keyBy('name');

        foreach ($this->recipeRows() as $recipeRow) {
            $variant = MenuVariant::query()
                ->whereHas('menu', fn ($query) => $query->where('name', $recipeRow['menu_name']))
                ->first();

            if (! $variant) {
                continue;
            }

            $recipe = Recipe::query()->updateOrCreate(
                ['menu_variant_id' => $variant->id],
                [
                    'prep_time_minutes' => $recipeRow['prep_time_minutes'],
                    'notes' => $recipeRow['notes'],
                ],
            );

            $syncedIngredientIds = [];

            foreach ($recipeRow['items'] as $ingredientRow) {
                $ingredientName = $ingredientRow['ingredient'] ?? $ingredientRow[0] ?? null;
                $quantity = $ingredientRow['quantity'] ?? $ingredientRow[1] ?? null;

                if (! is_string($ingredientName) || ! is_numeric($quantity)) {
                    continue;
                }

                $ingredient = $ingredientMap->get($ingredientName);

                if (! $ingredient) {
                    continue;
                }

                $syncedIngredientIds[] = $ingredient->id;

                RecipeItem::query()->updateOrCreate(
                    [
                        'recipe_id' => $recipe->id,
                        'ingredient_id' => $ingredient->id,
                    ],
                    [
                        'quantity' => $quantity,
                    ],
                );
            }

            RecipeItem::query()
                ->where('recipe_id', $recipe->id)
                ->when(
                    $syncedIngredientIds !== [],
                    fn ($query) => $query->whereNotIn('ingredient_id', $syncedIngredientIds),
                    fn ($query) => $query
                )
                ->delete();
        }
    }

    /**
     * @return array<int, array{
     *     menu_name: string,
     *     prep_time_minutes: int,
     *     notes: string,
     *     items: array<int, array{ingredient: string, quantity: float|int}>
     * }>
     */
    protected function recipeRows(): array
    {
        return [
            $this->recipe('Bluberry Yoghurt', 4, [
                ['Yoghurt Base', 160],
                ['Sirup Blueberry', 20],
                ['Simple Syrup', 10],
            ], 'Estimasi awal menu yoghurt buah dari data aktual AHWA Warkop.'),
            $this->recipe('Coffee Caramel', 4, [
                ['Biji Kopi Blend Espresso', 18],
                ['Fresh Milk', 140],
                ['Sirup Caramel', 20],
            ], 'Estimasi awal minuman kopi caramel.'),
            $this->recipe('Coffee Vanila', 4, [
                ['Biji Kopi Blend Espresso', 18],
                ['Fresh Milk', 140],
                ['Sirup Vanilla', 20],
            ], 'Estimasi awal minuman kopi vanilla.'),
            $this->recipe('Coklat', 4, [
                ['Bubuk Coklat', 20],
                ['Fresh Milk', 160],
                ['Simple Syrup', 10],
            ], 'Estimasi awal minuman coklat.'),
            $this->recipe('Kopi Ahwa', 4, [
                ['Biji Kopi Blend Espresso', 18],
                ['Fresh Milk', 120],
                ['Simple Syrup', 10],
            ], 'Estimasi awal signature coffee house blend.'),
            $this->recipe('Kopi Gula Aren', 4, [
                ['Biji Kopi Blend Espresso', 18],
                ['Fresh Milk', 130],
                ['Gula Aren Cair', 20],
            ], 'Estimasi awal kopi susu gula aren.'),
            $this->recipe('Kopi Susu', 4, [
                ['Biji Kopi Blend Espresso', 18],
                ['Fresh Milk', 140],
                ['Simple Syrup', 12],
            ], 'Estimasi awal kopi susu reguler.'),
            $this->recipe('Lemon Tea', 3, [
                ['Konsentrat Lemon Tea', 180],
                ['Simple Syrup', 10],
            ], 'Estimasi awal lemon tea.'),
            $this->recipe('Matcha', 4, [
                ['Bubuk Matcha', 18],
                ['Fresh Milk', 160],
                ['Simple Syrup', 10],
            ], 'Estimasi awal matcha latte.'),
            $this->recipe('Taro', 4, [
                ['Bubuk Taro', 22],
                ['Fresh Milk', 160],
                ['Simple Syrup', 10],
            ], 'Estimasi awal taro latte.'),
            $this->recipe('Thai Tea', 4, [
                ['Bubuk Thai Tea', 20],
                ['Fresh Milk', 150],
                ['Simple Syrup', 10],
            ], 'Estimasi awal thai tea.'),
            $this->recipe('BAKSO', 6, [
                ['Bakso Frozen', 180],
            ], 'Porsi bakso sederhana untuk dasar HPP.'),
            $this->recipe('KARI AYAM', 5, [
                ['Bumbu Kari Ayam', 1],
            ], 'Porsi kari ayam dasar untuk HPP awal.'),
            $this->recipe('KENTANG GORENG', 6, [
                ['Kentang Frozen', 150],
                ['Minyak Goreng', 30],
                ['Saus Sambal', 10],
                ['Mayones', 10],
            ], 'Porsi kentang goreng dengan saus.'),
            $this->recipe('UBI GORENG', 6, [
                ['Ubi Frozen', 180],
                ['Minyak Goreng', 30],
            ], 'Porsi ubi goreng dasar.'),
            $this->recipe('MIE BANGLADESH', 7, [
                ['Mie Bangladesh Pack', 1],
                ['Minyak Goreng', 10],
                ['Saus Sambal', 10],
            ], 'Porsi mie bangladesh dasar.'),
            $this->recipe('INDOMIE GORENG', 6, [
                ['Mie Instan Goreng Pack', 1],
            ], 'Porsi indomie goreng dasar.'),
            $this->recipe('INDOMIE KUAH', 6, [
                ['Mie Instan Kuah Pack', 1],
            ], 'Porsi indomie kuah dasar.'),
            $this->recipe('POP MIE SOTO AYAM', 4, [
                ['Pop Mie Soto Pack', 1],
            ], 'Porsi pop mie dasar.'),
            $this->recipe('NUGGET', 6, [
                ['Nugget Ayam Frozen', 120],
                ['Minyak Goreng', 20],
                ['Saus Sambal', 10],
                ['Mayones', 10],
            ], 'Porsi nugget goreng dengan saus.'),
            $this->recipe('SOSIS GORENG', 6, [
                ['Sosis Frozen', 120],
                ['Minyak Goreng', 20],
                ['Saus Sambal', 10],
            ], 'Porsi sosis goreng.'),
            $this->recipe('OTAK-OTAK', 6, [
                ['Otak-otak Frozen', 120],
                ['Minyak Goreng', 20],
                ['Saus Sambal', 10],
            ], 'Porsi otak-otak goreng.'),
            $this->recipe('MIX PLATTER', 8, [
                ['Kentang Frozen', 100],
                ['Nugget Ayam Frozen', 60],
                ['Sosis Frozen', 60],
                ['Otak-otak Frozen', 60],
                ['Minyak Goreng', 30],
                ['Saus Sambal', 15],
                ['Mayones', 15],
            ], 'Estimasi awal platter kombinasi snack goreng.'),
            $this->recipe('PEDES DOWER', 2, [
                ['Bumbu Pedas Dower', 15],
            ], 'Bumbu tabur pedas per porsi.'),
            $this->recipe('PEDES GLEDEK', 2, [
                ['Bumbu Pedas Gledek', 15],
            ], 'Bumbu tabur pedas per porsi.'),
            $this->recipe('NANAS', 2, [
                ['Nanas Potong', 150],
            ], 'Porsi buah nanas potong.'),
            $this->recipe('SEMANGKA', 2, [
                ['Semangka Potong', 150],
            ], 'Porsi buah semangka potong.'),
        ];
    }

    /**
     * @param  array<int, array{ingredient: string, quantity: float|int}>  $items
     * @return array{
     *     menu_name: string,
     *     prep_time_minutes: int,
     *     notes: string,
     *     items: array<int, array{ingredient: string, quantity: float|int}>
     * }
     */
    protected function recipe(string $menuName, int $prepTimeMinutes, array $items, string $notes): array
    {
        return [
            'menu_name' => $menuName,
            'prep_time_minutes' => $prepTimeMinutes,
            'notes' => $notes,
            'items' => $items,
        ];
    }
}
