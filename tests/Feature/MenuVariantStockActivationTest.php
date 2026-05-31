<?php

namespace Tests\Feature;

use App\Models\Menu;
use App\Models\MenuVariant;
use App\Models\StockLocation;
use App\Models\StockMovement;
use App\Models\StockMovementItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MenuVariantStockActivationTest extends TestCase
{
    use RefreshDatabase;

    public function test_stock_managed_variant_becomes_inactive_when_stock_reaches_zero_and_active_again_when_restocked(): void
    {
        $trackedMenu = Menu::query()->create([
            'name' => 'Americano',
            'is_active' => true,
            'is_stock_managed' => true,
        ]);

        $trackedVariant = MenuVariant::query()->create([
            'menu_id' => $trackedMenu->id,
            'kd_varian' => 'AMER-HOT',
            'price' => 18000,
            'is_active' => true,
        ]);

        $untrackedMenu = Menu::query()->create([
            'name' => 'Jasa Refill',
            'is_active' => true,
            'is_stock_managed' => false,
        ]);

        $untrackedVariant = MenuVariant::query()->create([
            'menu_id' => $untrackedMenu->id,
            'kd_varian' => 'REFILL-01',
            'price' => 5000,
            'is_active' => true,
        ]);

        $location = StockLocation::query()->create([
            'code' => 'OUTLET-A',
            'name' => 'Outlet A',
            'type' => 'outlet',
            'is_active' => true,
        ]);

        $stockIn = StockMovement::query()->create([
            'type' => StockMovement::TYPE_IN,
            'movement_date' => now(),
            'to_location_id' => $location->id,
        ]);

        StockMovementItem::query()->create([
            'stock_movement_id' => $stockIn->id,
            'item_type' => MenuVariant::class,
            'item_id' => $trackedVariant->id,
            'qty' => 5,
            'unit' => 'Cup',
        ]);

        $trackedVariant->refresh();
        $untrackedVariant->refresh();

        $this->assertSame(5, (int) $trackedVariant->stock);
        $this->assertTrue($trackedVariant->is_active);
        $this->assertTrue($untrackedVariant->is_active);

        $stockOut = StockMovement::query()->create([
            'type' => StockMovement::TYPE_OUT,
            'movement_date' => now(),
            'from_location_id' => $location->id,
        ]);

        StockMovementItem::query()->create([
            'stock_movement_id' => $stockOut->id,
            'item_type' => MenuVariant::class,
            'item_id' => $trackedVariant->id,
            'qty' => 5,
            'unit' => 'Cup',
        ]);

        $trackedVariant->refresh();
        $untrackedVariant->refresh();

        $this->assertSame(0, (int) $trackedVariant->stock);
        $this->assertFalse($trackedVariant->is_active);
        $this->assertTrue($untrackedVariant->is_active);

        $restock = StockMovement::query()->create([
            'type' => StockMovement::TYPE_IN,
            'movement_date' => now(),
            'to_location_id' => $location->id,
        ]);

        StockMovementItem::query()->create([
            'stock_movement_id' => $restock->id,
            'item_type' => MenuVariant::class,
            'item_id' => $trackedVariant->id,
            'qty' => 2,
            'unit' => 'Cup',
        ]);

        $trackedVariant->refresh();

        $this->assertSame(2, (int) $trackedVariant->stock);
        $this->assertTrue($trackedVariant->is_active);
    }
}
