<?php

namespace Database\Seeders;

use App\Models\Ingredient;
use App\Models\IngredientCategory;
use App\Models\Supplier;
use Illuminate\Database\Seeder;

class AhwaWarkopInitialIngredientsSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [];

        foreach ($this->categoryRows() as $categoryName) {
            $categories[$categoryName] = IngredientCategory::query()->updateOrCreate(
                ['name' => $categoryName],
                ['name' => $categoryName],
            );
        }

        $suppliers = [];

        foreach ($this->supplierRows() as $supplierRow) {
            $suppliers[$supplierRow['name']] = Supplier::query()->updateOrCreate(
                ['name' => $supplierRow['name']],
                $supplierRow,
            );
        }

        foreach ($this->ingredientRows() as $row) {
            Ingredient::query()->updateOrCreate(
                ['name' => $row['name']],
                [
                    'unit' => $row['unit'],
                    'ingredient_category_id' => $categories[$row['category']]->id,
                    'supplier_id' => $suppliers[$row['supplier']]->id,
                    'purchase_price' => $row['purchase_price'],
                    'reminder_stock' => $row['reminder_stock'],
                    'is_active' => $row['is_active'],
                ],
            );
        }
    }

    /**
     * @return array<int, string>
     */
    protected function categoryRows(): array
    {
        return [
            'Kopi & Espresso',
            'Susu & Dairy',
            'Powder & Tea',
            'Sirup & Pemanis',
            'Frozen Food',
            'Mi & Makanan Instan',
            'Buah & Topping',
            'Bumbu & Saus',
            'Bahan Pendukung Dapur',
        ];
    }

    /**
     * @return array<int, array{name: string, pic_name: string, email: string, phone: string}>
     */
    protected function supplierRows(): array
    {
        return [
            [
                'name' => 'Supplier Kopi AHWA',
                'pic_name' => 'Tim Kopi',
                'email' => 'kopi-ahwa@example.com',
                'phone' => '081200000101',
            ],
            [
                'name' => 'Supplier Dairy & Powder AHWA',
                'pic_name' => 'Tim Beverage',
                'email' => 'dairy-powder-ahwa@example.com',
                'phone' => '081200000102',
            ],
            [
                'name' => 'Supplier Frozen Food AHWA',
                'pic_name' => 'Tim Frozen',
                'email' => 'frozen-ahwa@example.com',
                'phone' => '081200000103',
            ],
            [
                'name' => 'Supplier Grocery AHWA',
                'pic_name' => 'Tim Grocery',
                'email' => 'grocery-ahwa@example.com',
                'phone' => '081200000104',
            ],
        ];
    }

    /**
     * @return array<int, array{name: string, unit: string, category: string, supplier: string, purchase_price: float|int, reminder_stock: float|int, is_active: bool}>
     */
    protected function ingredientRows(): array
    {
        return [
            $this->ingredient('Biji Kopi Blend Espresso', 'gram', 'Kopi & Espresso', 'Supplier Kopi AHWA', 280, 1000),
            $this->ingredient('Fresh Milk', 'ml', 'Susu & Dairy', 'Supplier Dairy & Powder AHWA', 18, 5000),
            $this->ingredient('Susu Kental Manis', 'ml', 'Susu & Dairy', 'Supplier Dairy & Powder AHWA', 15, 1000),
            $this->ingredient('Gula Aren Cair', 'ml', 'Sirup & Pemanis', 'Supplier Grocery AHWA', 20, 1500),
            $this->ingredient('Simple Syrup', 'ml', 'Sirup & Pemanis', 'Supplier Grocery AHWA', 8, 1500),
            $this->ingredient('Sirup Caramel', 'ml', 'Sirup & Pemanis', 'Supplier Grocery AHWA', 22, 1000),
            $this->ingredient('Sirup Vanilla', 'ml', 'Sirup & Pemanis', 'Supplier Grocery AHWA', 22, 1000),
            $this->ingredient('Yoghurt Base', 'ml', 'Susu & Dairy', 'Supplier Dairy & Powder AHWA', 35, 1500),
            $this->ingredient('Sirup Blueberry', 'ml', 'Sirup & Pemanis', 'Supplier Grocery AHWA', 28, 1000),
            $this->ingredient('Bubuk Matcha', 'gram', 'Powder & Tea', 'Supplier Dairy & Powder AHWA', 220, 500),
            $this->ingredient('Bubuk Coklat', 'gram', 'Powder & Tea', 'Supplier Dairy & Powder AHWA', 140, 1000),
            $this->ingredient('Bubuk Taro', 'gram', 'Powder & Tea', 'Supplier Dairy & Powder AHWA', 110, 800),
            $this->ingredient('Bubuk Thai Tea', 'gram', 'Powder & Tea', 'Supplier Dairy & Powder AHWA', 95, 800),
            $this->ingredient('Konsentrat Lemon Tea', 'ml', 'Powder & Tea', 'Supplier Dairy & Powder AHWA', 18, 1000),
            $this->ingredient('Bakso Frozen', 'gram', 'Frozen Food', 'Supplier Frozen Food AHWA', 38, 2000),
            $this->ingredient('Kentang Frozen', 'gram', 'Frozen Food', 'Supplier Frozen Food AHWA', 35, 3000),
            $this->ingredient('Nugget Ayam Frozen', 'gram', 'Frozen Food', 'Supplier Frozen Food AHWA', 45, 2000),
            $this->ingredient('Sosis Frozen', 'gram', 'Frozen Food', 'Supplier Frozen Food AHWA', 40, 2000),
            $this->ingredient('Otak-otak Frozen', 'gram', 'Frozen Food', 'Supplier Frozen Food AHWA', 42, 2000),
            $this->ingredient('Ubi Frozen', 'gram', 'Frozen Food', 'Supplier Frozen Food AHWA', 18, 3000),
            $this->ingredient('Mie Instan Goreng Pack', 'pcs', 'Mi & Makanan Instan', 'Supplier Grocery AHWA', 3500, 40),
            $this->ingredient('Mie Instan Kuah Pack', 'pcs', 'Mi & Makanan Instan', 'Supplier Grocery AHWA', 3500, 40),
            $this->ingredient('Pop Mie Soto Pack', 'pcs', 'Mi & Makanan Instan', 'Supplier Grocery AHWA', 4500, 30),
            $this->ingredient('Mie Bangladesh Pack', 'pcs', 'Mi & Makanan Instan', 'Supplier Grocery AHWA', 6000, 30),
            $this->ingredient('Bumbu Kari Ayam', 'porsi', 'Bumbu & Saus', 'Supplier Grocery AHWA', 3000, 25),
            $this->ingredient('Bumbu Pedas Dower', 'gram', 'Bumbu & Saus', 'Supplier Grocery AHWA', 120, 500),
            $this->ingredient('Bumbu Pedas Gledek', 'gram', 'Bumbu & Saus', 'Supplier Grocery AHWA', 120, 500),
            $this->ingredient('Mayones', 'ml', 'Bumbu & Saus', 'Supplier Grocery AHWA', 18, 1000),
            $this->ingredient('Saus Sambal', 'ml', 'Bumbu & Saus', 'Supplier Grocery AHWA', 12, 1000),
            $this->ingredient('Nanas Potong', 'gram', 'Buah & Topping', 'Supplier Grocery AHWA', 20, 1500),
            $this->ingredient('Semangka Potong', 'gram', 'Buah & Topping', 'Supplier Grocery AHWA', 18, 1500),
            $this->ingredient('Minyak Goreng', 'ml', 'Bahan Pendukung Dapur', 'Supplier Grocery AHWA', 17, 3000),
        ];
    }

    /**
     * @return array{name: string, unit: string, category: string, supplier: string, purchase_price: float|int, reminder_stock: float|int, is_active: bool}
     */
    protected function ingredient(
        string $name,
        string $unit,
        string $category,
        string $supplier,
        float|int $purchasePrice,
        float|int $reminderStock,
        bool $isActive = true,
    ): array {
        return [
            'name' => $name,
            'unit' => $unit,
            'category' => $category,
            'supplier' => $supplier,
            'purchase_price' => $purchasePrice,
            'reminder_stock' => $reminderStock,
            'is_active' => $isActive,
        ];
    }
}
