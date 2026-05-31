<?php

namespace Tests\Feature;

use App\Models\Ingredient;
use App\Models\IngredientCategory;
use App\Models\Menu;
use App\Models\MenuVariant;
use App\Models\Order;
use App\Models\Recipe;
use App\Models\StockLevel;
use App\Models\StockLocation;
use App\Models\StockMovement;
use App\Services\OrderAccountingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderAccountingServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_accounts_cogs_and_consumes_ingredient_stock_when_order_is_served(): void
    {
        $location = StockLocation::query()->create([
            'code' => 'OUTLET-1',
            'name' => 'Outlet 1',
            'is_active' => true,
        ]);

        $category = IngredientCategory::query()->create([
            'name' => 'Kopi',
        ]);

        $ingredient = Ingredient::query()->create([
            'name' => 'Espresso Shot',
            'unit' => 'shot',
            'ingredient_category_id' => $category->id,
            'purchase_price' => 3000,
            'is_active' => true,
        ]);

        $stockIn = StockMovement::query()->create([
            'type' => StockMovement::TYPE_IN,
            'movement_date' => now(),
            'to_location_id' => $location->id,
        ]);

        $stockIn->items()->create([
            'item_type' => Ingredient::class,
            'item_id' => $ingredient->id,
            'qty' => 10,
            'unit' => 'shot',
            'cost' => 30000,
        ]);

        $menu = Menu::query()->create([
            'name' => 'Americano',
            'is_active' => true,
            'is_stock_managed' => false,
        ]);

        $variant = MenuVariant::query()->create([
            'menu_id' => $menu->id,
            'kd_varian' => 'HOT',
            'price' => 18000,
            'is_active' => true,
            'stock' => 0,
        ]);

        $recipe = Recipe::query()->create([
            'menu_variant_id' => $variant->id,
        ]);

        $recipe->items()->create([
            'ingredient_id' => $ingredient->id,
            'quantity' => 1.5,
        ]);

        $order = Order::query()->create([
            'ordered_at' => now(),
            'order_type' => Order::TYPE_DINE_IN,
            'status' => Order::STATUS_DRAFT,
            'customer_type' => Order::CUSTOMER_WALK_IN,
            'payment_method' => Order::PAYMENT_CASH,
            'stock_location_id' => $location->id,
        ]);

        $order->items()->create([
            'menu_variant_id' => $variant->id,
            'price' => 18000,
            'qty' => 2,
            'discount_amount' => 0,
        ]);

        /** @var OrderAccountingService $service */
        $service = app(OrderAccountingService::class);
        $accountedOrder = $service->markAsServed($order);

        $this->assertSame(Order::STATUS_SERVED, $accountedOrder->status);
        $this->assertSame(9000.0, (float) $accountedOrder->cogs_total);
        $this->assertSame(27000.0, (float) $accountedOrder->gross_profit_total);
        $this->assertNotNull($accountedOrder->cost_accounted_at);

        $item = $accountedOrder->items()->firstOrFail();

        $this->assertSame(36000.0, (float) $item->net_sales_snapshot);
        $this->assertSame(9000.0, (float) $item->cost_snapshot);
        $this->assertSame(27000.0, (float) $item->gross_profit_snapshot);
        $this->assertSame(75.0, (float) $item->margin_percent_snapshot);

        $this->assertSame(
            7.0,
            StockLevel::getOnHand($location->id, Ingredient::class, $ingredient->id)
        );

        $this->assertDatabaseHas('stock_movements', [
            'type' => StockMovement::TYPE_OUT,
            'reference_no' => 'ORDER-' . $accountedOrder->order_number,
        ]);

        $service->markAsServed($accountedOrder);

        $this->assertSame(1, StockMovement::query()->where('reference_no', 'ORDER-' . $accountedOrder->order_number)->count());
    }
}
