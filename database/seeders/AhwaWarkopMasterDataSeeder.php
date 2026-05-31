<?php

namespace Database\Seeders;

use App\Models\Menu;
use App\Models\MenuVariant;
use App\Models\StockLocation;
use Illuminate\Database\Seeder;

class AhwaWarkopMasterDataSeeder extends Seeder
{
    public function run(): void
    {
        StockLocation::query()->updateOrCreate(
            ['code' => 'AHWA-WARKOP'],
            [
                'name' => 'AHWA Warkop',
                'type' => 'outlet',
                'is_active' => true,
            ],
        );

        foreach ($this->menuRows() as $index => $row) {
            $menu = Menu::query()->updateOrCreate(
                ['name' => $row['name']],
                [
                    'category' => $row['category'],
                    'description' => $row['description'],
                    'unit' => $row['unit'],
                    'is_active' => $row['is_active'],
                    'is_stock_managed' => false,
                ],
            );

            MenuVariant::query()->updateOrCreate(
                ['kd_varian' => sprintf('AHWA-%03d', $index + 1)],
                [
                    'menu_id' => $menu->id,
                    'size_varian' => 'Default',
                    'temperature' => null,
                    'sugar_level' => null,
                    'ice_level' => null,
                    'price' => $row['price'],
                    'is_active' => $row['is_active'],
                    'stock' => null,
                ],
            );
        }
    }

    /**
     * @return array<int, array{name: string, category: string, unit: string, price: float|int, is_active: bool, description: string}>
     */
    protected function menuRows(): array
    {
        return [
            $this->item('BAKSO', 'Makanan', 'Porsi', 8000, true, 'direct'),
            $this->item('Bluberry Yoghurt', 'Minuman Racik', 'Cup', 5000, true, 'direct'),
            $this->item('CHOCO MELT', 'Snack', 'Pcs', 7000, true, 'direct'),
            $this->item('CHOCO MELT STIK', 'Snack', 'Pcs', 5000, true, 'inferred'),
            $this->item('CHOCOLATE CRISPY', 'Snack', 'Pcs', 5000, true, 'direct'),
            $this->item('COFFEE CRISPY STICK', 'Snack', 'Pcs', 5000, true, 'direct'),
            $this->item('CRISPY BALLS', 'Snack', 'Pcs', 5000, true, 'direct'),
            $this->item('Coffee Caramel', 'Minuman Racik', 'Cup', 18000, true, 'inferred'),
            $this->item('Coffee Vanila', 'Minuman Racik', 'Cup', 18000, true, 'inferred'),
            $this->item('Coklat', 'Minuman Racik', 'Cup', 15000, true, 'direct'),
            $this->item('FRUIT TEA APPEL 500 ML', 'Minuman Botol', 'Bottle', 8000, true, 'direct'),
            $this->item('FRUIT TEA BLACKCURRANT 500 ML', 'Minuman Botol', 'Bottle', 8000, true, 'direct'),
            $this->item('FRUIT TEA FREEZEE 500 ML', 'Minuman Botol', 'Bottle', 8000, true, 'direct'),
            $this->item('FRUIT TEA GUAVA 500 ML', 'Minuman Botol', 'Bottle', 8000, true, 'direct'),
            $this->item('FRUIT TEA STRAWBERRY 500 ML', 'Minuman Botol', 'Bottle', 8000, true, 'direct'),
            $this->item('Fruizzy Grape', 'Minuman Botol', 'Bottle', 5000, true, 'direct'),
            $this->item('INDOMIE GORENG', 'Makanan', 'Porsi', 12000, true, 'direct'),
            $this->item('INDOMIE KUAH', 'Makanan', 'Porsi', 12000, true, 'direct'),
            $this->item('KARI AYAM', 'Makanan', 'Porsi', 8000, true, 'direct'),
            $this->item('KENTANG GORENG', 'Snack', 'Porsi', 15000, true, 'direct'),
            $this->item('Kopi Ahwa', 'Minuman Racik', 'Cup', 12000, true, 'direct'),
            $this->item('Kopi Gula Aren', 'Minuman Racik', 'Cup', 18000, true, 'direct'),
            $this->item('Kopi Susu', 'Minuman Racik', 'Cup', 14000, true, 'direct'),
            $this->item('Lemon Tea', 'Minuman Racik', 'Cup', 15000, true, 'inferred'),
            $this->item('MIE BANGLADESH', 'Makanan', 'Porsi', 15000, true, 'direct'),
            $this->item('MIKI-MIKI DOUBEL CHOCO', 'Snack', 'Pcs', 3000, true, 'direct'),
            $this->item('MIKI-MIKI VANILLA', 'Snack', 'Pcs', 3000, true, 'direct'),
            $this->item('MIX PLATTER', 'Snack', 'Porsi', 15000, true, 'direct'),
            $this->item('MOCHI CHOCOLATE', 'Snack', 'Pcs', 5000, true, 'direct'),
            $this->item('MOCHI VANILLA', 'Snack', 'Pcs', 5000, true, 'direct'),
            $this->item('Mango Sluch HI-C', 'Minuman Botol', 'Bottle', 5000, true, 'direct'),
            $this->item('Matcha', 'Minuman Racik', 'Cup', 18000, true, 'direct'),
            $this->item('MieNas', 'Makanan', 'Porsi', 36000, true, 'direct'),
            $this->item('Mochi Durian', 'Snack', 'Pcs', 5000, true, 'direct'),
            $this->item('NANAS', 'Buah', 'Porsi', 5000, true, 'direct'),
            $this->item('NUGGET', 'Snack', 'Porsi', 12000, true, 'direct'),
            $this->item('Nasi Goreng Ahwa', 'Makanan', 'Porsi', 18000, false, 'estimated'),
            $this->item('OTAK-OTAK', 'Snack', 'Porsi', 12000, true, 'direct'),
            $this->item('PEDES DOWER', 'Snack', 'Porsi', 8000, true, 'direct'),
            $this->item('PEDES GLEDEK', 'Snack', 'Porsi', 8000, true, 'direct'),
            $this->item('POP MIE SOTO AYAM', 'Makanan', 'Porsi', 8000, true, 'inferred'),
            $this->item('PRIMA 1500ML', 'Air Mineral', 'Bottle', 8000, true, 'direct'),
            $this->item('PRIMA 600 ML', 'Air Mineral', 'Bottle', 5000, true, 'direct'),
            $this->item('Red Velvet', 'Minuman Racik', 'Cup', 15000, true, 'inferred'),
            $this->item('Rempeyek Kacang / Teri', 'Snack', 'Porsi', 15000, true, 'direct'),
            $this->item('SEMANGKA', 'Buah', 'Porsi', 5000, true, 'direct'),
            $this->item('SOSIS GORENG', 'Snack', 'Porsi', 12000, true, 'direct'),
            $this->item('STRAWBERRY CONE', 'Dessert', 'Pcs', 5000, false, 'estimated'),
            $this->item('STRAWBERRY CRISPY', 'Snack', 'Pcs', 5000, true, 'direct'),
            $this->item('SUNDAE CHOCOLATE CUP', 'Dessert', 'Cup', 7000, true, 'direct'),
            $this->item('SUNDAE STRAWBERY CUP', 'Dessert', 'Cup', 7000, true, 'direct'),
            $this->item('SWEET CORN', 'Snack', 'Porsi', 5000, false, 'estimated'),
            $this->item('TARO CRISPY', 'Snack', 'Pcs', 5000, true, 'direct'),
            $this->item('TEBS SPARKLING 500 ML', 'Minuman Botol', 'Bottle', 8000, true, 'direct'),
            $this->item('TEH BOTOL SOSRO', 'Minuman Botol', 'Bottle', 8000, true, 'direct'),
            $this->item('Taro', 'Minuman Racik', 'Cup', 15000, true, 'direct'),
            $this->item('Teh Tanjak', 'Minuman Racik', 'Cup', 5000, false, 'estimated'),
            $this->item('Thai Tea', 'Minuman Racik', 'Cup', 15000, true, 'direct'),
            $this->item('UBI GORENG', 'Snack', 'Porsi', 12000, true, 'inferred'),
            $this->item('WORTEL KUDA', 'Snack', 'Porsi', 10000, true, 'direct'),
        ];
    }

    /**
     * @return array{name: string, category: string, unit: string, price: float|int, is_active: bool, description: string}
     */
    protected function item(
        string $name,
        string $category,
        string $unit,
        float|int $price,
        bool $isActive,
        string $priceSource,
    ): array {
        $sourceLabel = match ($priceSource) {
            'direct' => 'harga teramati langsung dari transaksi tunggal',
            'inferred' => 'harga hasil inferensi dari kombinasi transaksi',
            default => 'harga estimasi awal dan masih perlu verifikasi',
        };

        return [
            'name' => $name,
            'category' => $category,
            'unit' => $unit,
            'price' => $price,
            'is_active' => $isActive,
            'description' => "Master data awal dari laporan penjualan AHWA Warkop periode April 2026; {$sourceLabel}.",
        ];
    }
}
