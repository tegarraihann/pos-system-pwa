<?php

namespace Tests\Feature;

use App\Filament\Pages\StockReminders;
use App\Models\Ingredient;
use App\Models\IngredientCategory;
use App\Models\Permission;
use App\Models\Role;
use App\Models\StockLocation;
use App\Models\StockMovement;
use App\Models\StockMovementItem;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockReminderPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_authorized_user_can_access_stock_reminder_page_and_snapshot_contains_item_detail_link(): void
    {
        $user = User::factory()->create();
        $role = Role::query()->create(['name' => 'admin', 'guard_name' => 'web']);
        $permission = Permission::query()->create(['name' => 'ViewAny:StockMovement', 'guard_name' => 'web']);
        $role->givePermissionTo($permission);
        $user->assignRole($role);

        $category = IngredientCategory::query()->create(['name' => 'Minuman']);
        $supplier = Supplier::query()->create(['name' => 'Supplier A']);
        $ingredient = Ingredient::query()->create([
            'name' => 'Gula Aren',
            'unit' => 'ml',
            'ingredient_category_id' => $category->id,
            'supplier_id' => $supplier->id,
            'purchase_price' => 100,
            'reminder_stock' => 10,
            'is_active' => true,
        ]);

        $location = StockLocation::query()->create([
            'code' => 'OUTLET-A',
            'name' => 'Outlet A',
            'type' => 'outlet',
            'is_active' => true,
        ]);

        $movement = StockMovement::query()->create([
            'type' => StockMovement::TYPE_IN,
            'movement_date' => now(),
            'to_location_id' => $location->id,
        ]);

        StockMovementItem::query()->create([
            'stock_movement_id' => $movement->id,
            'item_type' => Ingredient::class,
            'item_id' => $ingredient->id,
            'qty' => 5,
            'unit' => 'ml',
        ]);

        $this->actingAs($user);

        $this->assertTrue($user->can('ViewAny:StockMovement'));
        $this->assertTrue(StockReminders::canAccess());

        /** @var StockReminders $page */
        $page = app(StockReminders::class);
        $page->status = 'low';

        $snapshot = $page->getSnapshot();

        $this->assertSame(1, $snapshot['impacted_count']);
        $this->assertSame('Gula Aren', $snapshot['items']->first()['name']);
        $this->assertSame('low', $snapshot['items']->first()['status']);
        $this->assertStringContainsString('/ingredients/', $snapshot['items']->first()['detail_url']);
    }
}
