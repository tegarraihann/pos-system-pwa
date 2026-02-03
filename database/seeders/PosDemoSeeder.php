<?php

namespace Database\Seeders;

use App\Models\Bill;
use App\Models\BillItem;
use App\Models\BillPayment;
use App\Models\Customer;
use App\Models\Ingredient;
use App\Models\IngredientCategory;
use App\Models\Menu;
use App\Models\MenuVariant;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Recipe;
use App\Models\RecipeItem;
use App\Models\StockLocation;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Seeder;

class PosDemoSeeder extends Seeder
{
    /**
     * Seed demo data for POS module.
     */
    public function run(): void
    {
        $menus = [
            [
                'code' => 'MENU-AMER',
                'name' => 'Americano',
                'description' => 'Kopi hitam klasik.',
                'unit' => 'Cup',
                'is_active' => true,
                'is_stock_managed' => true,
                'image_path' => null,
            ],
            [
                'code' => 'MENU-LATTE',
                'name' => 'Cafe Latte',
                'description' => 'Espresso dengan susu.',
                'unit' => 'Cup',
                'is_active' => true,
                'is_stock_managed' => true,
                'image_path' => null,
            ],
            [
                'code' => 'MENU-MATCHA',
                'name' => 'Matcha Latte',
                'description' => 'Matcha creamy.',
                'unit' => 'Cup',
                'is_active' => true,
                'is_stock_managed' => true,
                'image_path' => null,
            ],
            [
                'code' => 'MENU-CHOCO',
                'name' => 'Chocolate',
                'description' => 'Cokelat manis.',
                'unit' => 'Cup',
                'is_active' => true,
                'is_stock_managed' => true,
                'image_path' => null,
            ],
        ];

        $menuMap = [];
        foreach ($menus as $menuData) {
            $menu = Menu::query()->updateOrCreate(
                ['code' => $menuData['code']],
                $menuData,
            );
            $menuMap[$menuData['code']] = $menu;
        }

        $variants = [
            [
                'menu_code' => 'MENU-AMER',
                'kd_varian' => 'AMER-HOT',
                'size_varian' => 'Regular',
                'temperature' => 'Hot',
                'sugar_level' => 'No Sugar',
                'ice_level' => null,
                'price' => 18000,
                'is_active' => true,
                'stock' => 40,
            ],
            [
                'menu_code' => 'MENU-AMER',
                'kd_varian' => 'AMER-ICE',
                'size_varian' => 'Regular',
                'temperature' => 'Ice',
                'sugar_level' => 'No Sugar',
                'ice_level' => 'Normal',
                'price' => 20000,
                'is_active' => true,
                'stock' => 35,
            ],
            [
                'menu_code' => 'MENU-LATTE',
                'kd_varian' => 'LATTE-HOT',
                'size_varian' => 'Regular',
                'temperature' => 'Hot',
                'sugar_level' => 'Normal',
                'ice_level' => null,
                'price' => 22000,
                'is_active' => true,
                'stock' => 30,
            ],
            [
                'menu_code' => 'MENU-LATTE',
                'kd_varian' => 'LATTE-ICE',
                'size_varian' => 'Large',
                'temperature' => 'Ice',
                'sugar_level' => 'Normal',
                'ice_level' => 'Normal',
                'price' => 25000,
                'is_active' => true,
                'stock' => 28,
            ],
            [
                'menu_code' => 'MENU-MATCHA',
                'kd_varian' => 'MATCHA-ICE',
                'size_varian' => 'Regular',
                'temperature' => 'Ice',
                'sugar_level' => 'Less Sugar',
                'ice_level' => 'Normal',
                'price' => 24000,
                'is_active' => true,
                'stock' => 22,
            ],
            [
                'menu_code' => 'MENU-CHOCO',
                'kd_varian' => 'CHOCO-HOT',
                'size_varian' => 'Regular',
                'temperature' => 'Hot',
                'sugar_level' => 'Normal',
                'ice_level' => null,
                'price' => 21000,
                'is_active' => true,
                'stock' => 18,
            ],
        ];

        foreach ($variants as $variantData) {
            $menu = $menuMap[$variantData['menu_code']] ?? null;
            if (! $menu) {
                continue;
            }

            MenuVariant::query()->updateOrCreate(
                ['kd_varian' => $variantData['kd_varian']],
                [
                    'menu_id' => $menu->id,
                    'kd_varian' => $variantData['kd_varian'],
                    'size_varian' => $variantData['size_varian'],
                    'temperature' => $variantData['temperature'],
                    'sugar_level' => $variantData['sugar_level'],
                    'ice_level' => $variantData['ice_level'],
                    'price' => $variantData['price'],
                    'is_active' => $variantData['is_active'],
                    'stock' => $variantData['stock'],
                ],
            );
        }

        $ingredientCategories = [
            ['name' => 'Coffee Beans'],
            ['name' => 'Dairy'],
            ['name' => 'Sweetener'],
            ['name' => 'Tea'],
            ['name' => 'Powder'],
        ];

        $ingredientCategoryMap = [];
        foreach ($ingredientCategories as $categoryData) {
            $category = IngredientCategory::query()->updateOrCreate(
                ['name' => $categoryData['name']],
                $categoryData,
            );
            $ingredientCategoryMap[$categoryData['name']] = $category;
        }

        $suppliers = [
            [
                'name' => 'Supplier Nusantara',
                'pic_name' => 'Budi',
                'email' => 'supplier1@example.com',
                'phone' => '0812000111',
            ],
            [
                'name' => 'Supplier Sejahtera',
                'pic_name' => 'Sari',
                'email' => 'supplier2@example.com',
                'phone' => '0812000222',
            ],
        ];

        $supplierMap = [];
        foreach ($suppliers as $supplierData) {
            $supplier = Supplier::query()->updateOrCreate(
                ['name' => $supplierData['name']],
                $supplierData,
            );
            $supplierMap[$supplierData['name']] = $supplier;
        }

        $ingredients = [
            [
                'code' => 'ING-ESP',
                'name' => 'Espresso Shot',
                'unit' => 'shot',
                'category' => 'Coffee Beans',
                'supplier' => 'Supplier Nusantara',
                'purchase_price' => 2500,
            ],
            [
                'code' => 'ING-MILK',
                'name' => 'Fresh Milk',
                'unit' => 'ml',
                'category' => 'Dairy',
                'supplier' => 'Supplier Sejahtera',
                'purchase_price' => 20,
            ],
            [
                'code' => 'ING-SUGAR',
                'name' => 'Sugar Syrup',
                'unit' => 'ml',
                'category' => 'Sweetener',
                'supplier' => 'Supplier Nusantara',
                'purchase_price' => 10,
            ],
            [
                'code' => 'ING-MATCHA',
                'name' => 'Matcha Powder',
                'unit' => 'gram',
                'category' => 'Powder',
                'supplier' => 'Supplier Sejahtera',
                'purchase_price' => 150,
            ],
            [
                'code' => 'ING-CHOCO',
                'name' => 'Chocolate Powder',
                'unit' => 'gram',
                'category' => 'Powder',
                'supplier' => 'Supplier Sejahtera',
                'purchase_price' => 120,
            ],
            [
                'code' => 'ING-TEA',
                'name' => 'Tea Concentrate',
                'unit' => 'ml',
                'category' => 'Tea',
                'supplier' => 'Supplier Nusantara',
                'purchase_price' => 15,
            ],
        ];

        $ingredientMap = [];
        foreach ($ingredients as $ingredientData) {
            $category = $ingredientCategoryMap[$ingredientData['category']] ?? null;
            $supplier = $supplierMap[$ingredientData['supplier']] ?? null;

            if (! $category) {
                continue;
            }

            $ingredient = Ingredient::query()->updateOrCreate(
                ['code' => $ingredientData['code']],
                [
                    'name' => $ingredientData['name'],
                    'unit' => $ingredientData['unit'],
                    'ingredient_category_id' => $category->id,
                    'supplier_id' => $supplier?->id,
                    'purchase_price' => $ingredientData['purchase_price'],
                    'is_active' => true,
                ],
            );

            $ingredientMap[$ingredientData['code']] = $ingredient;
        }

        $recipeVariants = [
            'AMER-HOT' => [
                ['ING-ESP', 2],
            ],
            'LATTE-ICE' => [
                ['ING-ESP', 2],
                ['ING-MILK', 150],
                ['ING-SUGAR', 10],
            ],
            'MATCHA-ICE' => [
                ['ING-MATCHA', 8],
                ['ING-MILK', 120],
                ['ING-SUGAR', 10],
            ],
            'CHOCO-HOT' => [
                ['ING-CHOCO', 10],
                ['ING-MILK', 140],
                ['ING-SUGAR', 8],
            ],
        ];

        foreach ($recipeVariants as $variantCode => $items) {
            $variant = MenuVariant::query()->where('kd_varian', $variantCode)->first();
            if (! $variant) {
                continue;
            }

            $recipe = Recipe::query()->firstOrCreate(
                ['menu_variant_id' => $variant->id],
                ['prep_time_minutes' => 4],
            );

            foreach ($items as [$ingredientCode, $qty]) {
                $ingredient = $ingredientMap[$ingredientCode] ?? null;
                if (! $ingredient) {
                    continue;
                }

                RecipeItem::query()->updateOrCreate(
                    [
                        'recipe_id' => $recipe->id,
                        'ingredient_id' => $ingredient->id,
                    ],
                    ['quantity' => $qty],
                );
            }
        }

        $customers = [
            [
                'code' => 'CUS-0001',
                'name' => 'Walk In Customer',
                'phone' => null,
                'email' => null,
                'is_member' => false,
            ],
            [
                'code' => 'CUS-0002',
                'name' => 'Tegar Pratama',
                'phone' => '081234567890',
                'email' => 'tegar@example.com',
                'is_member' => true,
            ],
        ];

        $customerMap = [];
        foreach ($customers as $customerData) {
            $customer = Customer::query()->updateOrCreate(
                ['code' => $customerData['code']],
                $customerData,
            );
            $customerMap[$customerData['code']] = $customer;
        }

        if (Order::query()->exists()) {
            return;
        }

        $userId = User::query()->value('id');
        $locations = StockLocation::query()->pluck('id', 'code');

        $orderOne = Order::query()->create([
            'order_number' => Order::generateOrderNumber(),
            'order_type' => Order::TYPE_DINE_IN,
            'status' => Order::STATUS_QUEUED,
            'customer_type' => Order::CUSTOMER_WALK_IN,
            'stock_location_id' => $locations->get('OUTLET-A'),
            'table_number' => 'A1',
            'queue_number' => 1,
            'notes' => 'Pesanan demo.',
            'tax_total' => 0,
            'service_total' => 0,
            'created_by' => $userId,
        ]);

        $variantAmer = MenuVariant::query()->where('kd_varian', 'AMER-HOT')->first();
        $variantLatte = MenuVariant::query()->where('kd_varian', 'LATTE-ICE')->first();

        if ($variantAmer) {
            $orderOne->items()->create([
                'menu_variant_id' => $variantAmer->id,
                'price' => $variantAmer->price,
                'qty' => 1,
                'discount_amount' => 0,
            ]);
        }

        if ($variantLatte) {
            $orderOne->items()->create([
                'menu_variant_id' => $variantLatte->id,
                'price' => $variantLatte->price,
                'qty' => 2,
                'discount_amount' => 2000,
            ]);
        }

        $orderTwo = Order::query()->create([
            'order_number' => Order::generateOrderNumber(),
            'order_type' => Order::TYPE_TAKE_AWAY,
            'status' => Order::STATUS_SERVED,
            'customer_type' => Order::CUSTOMER_MEMBER,
            'customer_id' => $customerMap['CUS-0002']->id ?? null,
            'stock_location_id' => $locations->get('OUTLET-A'),
            'queue_number' => 2,
            'tax_total' => 0,
            'service_total' => 0,
            'created_by' => $userId,
        ]);

        $variantMatcha = MenuVariant::query()->where('kd_varian', 'MATCHA-ICE')->first();
        $variantChoco = MenuVariant::query()->where('kd_varian', 'CHOCO-HOT')->first();

        $orderTwoItems = [];
        if ($variantMatcha) {
            $orderTwoItems[] = $orderTwo->items()->create([
                'menu_variant_id' => $variantMatcha->id,
                'price' => $variantMatcha->price,
                'qty' => 1,
                'discount_amount' => 0,
            ]);
        }

        if ($variantChoco) {
            $orderTwoItems[] = $orderTwo->items()->create([
                'menu_variant_id' => $variantChoco->id,
                'price' => $variantChoco->price,
                'qty' => 1,
                'discount_amount' => 0,
            ]);
        }

        Payment::query()->create([
            'order_id' => $orderTwo->id,
            'method' => 'cash',
            'amount' => $orderTwo->grand_total,
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        $billA = Bill::query()->create([
            'order_id' => $orderTwo->id,
            'bill_no' => 'A',
            'status' => 'paid',
            'tax_total' => 0,
            'service_total' => 0,
        ]);

        if (isset($orderTwoItems[0])) {
            BillItem::query()->create([
                'bill_id' => $billA->id,
                'order_item_id' => $orderTwoItems[0]->id,
                'qty' => $orderTwoItems[0]->qty,
            ]);
        }

        BillPayment::query()->create([
            'bill_id' => $billA->id,
            'method' => 'cash',
            'amount' => $billA->grand_total,
            'status' => 'paid',
            'paid_at' => now(),
        ]);
    }
}
