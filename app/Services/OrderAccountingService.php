<?php

namespace App\Services;

use App\Models\Ingredient;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\StockLocation;
use App\Models\StockMovement;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class OrderAccountingService
{
    public function markAsServed(Order $order): Order
    {
        return $this->processOrder($order, true);
    }

    public function accountHistoricalOrder(Order $order): Order
    {
        return $this->processOrder($order, false);
    }

    protected function processOrder(Order $order, bool $consumeStock): Order
    {
        return DB::transaction(function () use ($order, $consumeStock): Order {
            /** @var Order $lockedOrder */
            $lockedOrder = Order::query()
                ->lockForUpdate()
                ->findOrFail($order->getKey());

            $lockedOrder->loadMissing([
                'stockLocation',
                'items.menuVariant.recipe.items.ingredient',
            ]);

            if ($lockedOrder->cost_accounted_at) {
                if ($lockedOrder->status !== Order::STATUS_SERVED) {
                    $lockedOrder->updateQuietly(['status' => Order::STATUS_SERVED]);
                }

                return $lockedOrder->fresh([
                    'items.menuVariant.menu',
                    'payments',
                    'creator',
                ]);
            }

            $summary = $this->buildSummary($lockedOrder);

            if ($consumeStock) {
                $this->createConsumptionMovement($lockedOrder, $summary['consumption']);
            }

            $this->persistSnapshots($lockedOrder, $summary);

            return $lockedOrder->fresh([
                'items.menuVariant.menu',
                'payments',
                'creator',
            ]);
        });
    }

    /**
     * @return array{
     *     itemSnapshots: array<int, array{item: OrderItem, net_sales_snapshot: float, cost_snapshot: float, gross_profit_snapshot: float, margin_percent_snapshot: float}>,
     *     cogs_total: float,
     *     gross_profit_total: float,
     *     consumption: array<int, array{ingredient: Ingredient, qty: float, cost: float}>
     * }
     */
    protected function buildSummary(Order $order): array
    {
        /** @var Collection<int, OrderItem> $items */
        $items = $order->items->values();
        $baseSalesTotal = round((float) $items->sum(fn (OrderItem $item): float => (float) $item->total), 2);
        $memberDiscountRemaining = round((float) $order->member_discount_total, 2);

        $itemSnapshots = [];
        $consumption = [];
        $cogsTotal = 0.0;
        $grossProfitTotal = 0.0;
        $lastIndex = max($items->count() - 1, 0);

        foreach ($items as $index => $item) {
            $baseLineSales = round((float) $item->total, 2);

            if ($index === $lastIndex) {
                $allocatedMemberDiscount = $memberDiscountRemaining;
            } else {
                $allocatedMemberDiscount = $baseSalesTotal > 0
                    ? round(((float) $order->member_discount_total) * ($baseLineSales / $baseSalesTotal), 2)
                    : 0.0;
                $memberDiscountRemaining = round($memberDiscountRemaining - $allocatedMemberDiscount, 2);
            }

            $netSalesSnapshot = round(max($baseLineSales - $allocatedMemberDiscount, 0), 2);
            $costSnapshot = 0.0;

            $recipeItems = $item->menuVariant?->recipe?->items ?? collect();

            foreach ($recipeItems as $recipeItem) {
                $ingredient = $recipeItem->ingredient;

                if (! $ingredient) {
                    continue;
                }

                $consumedQty = round((float) $recipeItem->quantity * (float) $item->qty, 3);
                $ingredientCost = round($consumedQty * (float) $ingredient->purchase_price, 2);

                $costSnapshot = round($costSnapshot + $ingredientCost, 2);

                $existing = $consumption[$ingredient->id] ?? [
                    'ingredient' => $ingredient,
                    'qty' => 0.0,
                    'cost' => 0.0,
                ];

                $existing['qty'] = round($existing['qty'] + $consumedQty, 3);
                $existing['cost'] = round($existing['cost'] + $ingredientCost, 2);
                $consumption[$ingredient->id] = $existing;
            }

            $grossProfitSnapshot = round($netSalesSnapshot - $costSnapshot, 2);
            $marginPercentSnapshot = $netSalesSnapshot > 0
                ? round(($grossProfitSnapshot / $netSalesSnapshot) * 100, 2)
                : 0.0;

            $itemSnapshots[] = [
                'item' => $item,
                'net_sales_snapshot' => $netSalesSnapshot,
                'cost_snapshot' => $costSnapshot,
                'gross_profit_snapshot' => $grossProfitSnapshot,
                'margin_percent_snapshot' => $marginPercentSnapshot,
            ];

            $cogsTotal = round($cogsTotal + $costSnapshot, 2);
            $grossProfitTotal = round($grossProfitTotal + $grossProfitSnapshot, 2);
        }

        return [
            'itemSnapshots' => $itemSnapshots,
            'cogs_total' => $cogsTotal,
            'gross_profit_total' => $grossProfitTotal,
            'consumption' => $consumption,
        ];
    }

    /**
     * @param  array<int, array{ingredient: Ingredient, qty: float, cost: float}>  $consumption
     */
    protected function createConsumptionMovement(Order $order, array $consumption): void
    {
        if ($consumption === []) {
            return;
        }

        $location = $order->stockLocation ?: StockLocation::resolveDefaultLocation();

        if (! $location) {
            return;
        }

        $movement = StockMovement::query()->create([
            'type' => StockMovement::TYPE_OUT,
            'movement_date' => $order->ordered_at ?? now(),
            'from_location_id' => $location->id,
            'reference_no' => 'ORDER-' . $order->order_number,
            'notes' => 'Pemakaian bahan baku untuk order ' . $order->order_number,
            'created_by' => $order->created_by,
        ]);

        foreach ($consumption as $row) {
            $movement->items()->create([
                'item_type' => Ingredient::class,
                'item_id' => $row['ingredient']->id,
                'qty' => $row['qty'],
                'unit' => $row['ingredient']->unit,
                'cost' => $row['cost'],
            ]);
        }
    }

    /**
     * @param  array{
     *     itemSnapshots: array<int, array{item: OrderItem, net_sales_snapshot: float, cost_snapshot: float, gross_profit_snapshot: float, margin_percent_snapshot: float}>,
     *     cogs_total: float,
     *     gross_profit_total: float
     * }  $summary
     */
    protected function persistSnapshots(Order $order, array $summary): void
    {
        foreach ($summary['itemSnapshots'] as $snapshot) {
            $snapshot['item']->updateQuietly([
                'net_sales_snapshot' => $snapshot['net_sales_snapshot'],
                'cost_snapshot' => $snapshot['cost_snapshot'],
                'gross_profit_snapshot' => $snapshot['gross_profit_snapshot'],
                'margin_percent_snapshot' => $snapshot['margin_percent_snapshot'],
            ]);
        }

        $order->updateQuietly([
            'status' => Order::STATUS_SERVED,
            'cogs_total' => $summary['cogs_total'],
            'gross_profit_total' => $summary['gross_profit_total'],
            'cost_accounted_at' => now(),
        ]);
    }
}
