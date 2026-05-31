<?php

namespace Database\Seeders;

use App\Models\Ingredient;
use App\Models\StockLocation;
use App\Models\StockMovement;
use Illuminate\Database\Seeder;

class AhwaWarkopInitialStockSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            AhwaWarkopMasterDataSeeder::class,
            AhwaWarkopInitialIngredientsSeeder::class,
        ]);

        $location = StockLocation::query()
            ->where('code', 'AHWA-WARKOP')
            ->first();

        if (! $location) {
            return;
        }

        $movement = StockMovement::query()->updateOrCreate(
            ['reference_no' => 'OPENING-AHWA-2026-05'],
            [
                'type' => StockMovement::TYPE_IN,
                'movement_date' => now()->startOfDay(),
                'to_location_id' => $location->id,
                'notes' => 'Stok awal bahan baku AHWA Warkop untuk fondasi operasional dan HPP awal.',
                'created_by' => null,
            ],
        );

        $ingredientMap = Ingredient::query()->get()->keyBy('name');
        $syncedItemIds = [];

        foreach ($this->stockRows() as $row) {
            $ingredient = $ingredientMap->get($row['ingredient']);

            if (! $ingredient) {
                continue;
            }

            $item = $movement->items()->updateOrCreate(
                [
                    'item_type' => Ingredient::class,
                    'item_id' => $ingredient->id,
                ],
                [
                    'qty' => $row['qty'],
                    'unit' => $ingredient->unit,
                    'cost' => round((float) $row['qty'] * (float) $ingredient->purchase_price, 2),
                ],
            );

            $syncedItemIds[] = $item->id;
        }

        $movement->items()
            ->when(
                $syncedItemIds !== [],
                fn ($query) => $query->whereNotIn('id', $syncedItemIds),
                fn ($query) => $query
            )
            ->delete();
    }

    /**
     * @return array<int, array{ingredient: string, qty: float|int}>
     */
    protected function stockRows(): array
    {
        return [
            $this->stock('Biji Kopi Blend Espresso', 2500),
            $this->stock('Fresh Milk', 12000),
            $this->stock('Susu Kental Manis', 2000),
            $this->stock('Gula Aren Cair', 3000),
            $this->stock('Simple Syrup', 4000),
            $this->stock('Sirup Caramel', 1500),
            $this->stock('Sirup Vanilla', 1500),
            $this->stock('Yoghurt Base', 2500),
            $this->stock('Sirup Blueberry', 1200),
            $this->stock('Bubuk Matcha', 800),
            $this->stock('Bubuk Coklat', 1500),
            $this->stock('Bubuk Taro', 1200),
            $this->stock('Bubuk Thai Tea', 1000),
            $this->stock('Konsentrat Lemon Tea', 1500),
            $this->stock('Bakso Frozen', 3000),
            $this->stock('Kentang Frozen', 5000),
            $this->stock('Nugget Ayam Frozen', 2500),
            $this->stock('Sosis Frozen', 2500),
            $this->stock('Otak-otak Frozen', 2500),
            $this->stock('Ubi Frozen', 4000),
            $this->stock('Mie Instan Goreng Pack', 72),
            $this->stock('Mie Instan Kuah Pack', 60),
            $this->stock('Pop Mie Soto Pack', 36),
            $this->stock('Mie Bangladesh Pack', 40),
            $this->stock('Bumbu Kari Ayam', 40),
            $this->stock('Bumbu Pedas Dower', 1200),
            $this->stock('Bumbu Pedas Gledek', 1200),
            $this->stock('Mayones', 1500),
            $this->stock('Saus Sambal', 2000),
            $this->stock('Nanas Potong', 2000),
            $this->stock('Semangka Potong', 2500),
            $this->stock('Minyak Goreng', 5000),
        ];
    }

    /**
     * @return array{ingredient: string, qty: float|int}
     */
    protected function stock(string $ingredient, float|int $qty): array
    {
        return [
            'ingredient' => $ingredient,
            'qty' => $qty,
        ];
    }
}
