<?php

namespace App\Services;

use App\Models\Ingredient;
use App\Models\MenuVariant;
use App\Models\StockLevel;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class StockReminderService
{
    /**
     * @return array{
     *   total_items:int,
     *   low_count:int,
     *   out_count:int,
     *   impacted_count:int,
     *   items:\Illuminate\Support\Collection<int, array{
     *     item_type:string,
     *     item_id:int,
     *     name:string,
     *     stock:float,
     *     reminder_stock:float,
     *     status:string
     *   }>
     * }
     */
    public function getSnapshot(?int $limit = null): array
    {
        $totalsByItemKey = StockLevel::query()
            ->select('item_type', 'item_id', DB::raw('SUM(on_hand) as total_on_hand'))
            ->groupBy('item_type', 'item_id')
            ->get()
            ->mapWithKeys(static fn ($row): array => [
                $row->item_type . ':' . $row->item_id => (float) $row->total_on_hand,
            ]);

        $items = collect()
            ->merge($this->getIngredientCandidates())
            ->merge($this->getMenuVariantCandidates());

        $evaluated = $items
            ->map(function (array $item) use ($totalsByItemKey): ?array {
                $key = $item['item_type'] . ':' . $item['item_id'];
                $stock = (float) ($totalsByItemKey[$key] ?? 0);
                $reminderStock = (float) $item['reminder_stock'];

                $status = $stock <= 0
                    ? 'out'
                    : ($stock <= $reminderStock ? 'low' : null);

                if ($status === null) {
                    return null;
                }

                return [
                    ...$item,
                    'stock' => $stock,
                    'reminder_stock' => $reminderStock,
                    'status' => $status,
                ];
            })
            ->filter()
            ->values()
            ->sortBy(static fn (array $item): string => sprintf(
                '%d-%012.3f',
                $item['status'] === 'out' ? 0 : 1,
                (float) $item['stock'],
            ))
            ->values();

        $outCount = $evaluated->where('status', 'out')->count();
        $lowCount = $evaluated->where('status', 'low')->count();

        if ($limit !== null) {
            $evaluated = $evaluated->take($limit)->values();
        }

        return [
            'total_items' => $items->count(),
            'low_count' => $lowCount,
            'out_count' => $outCount,
            'impacted_count' => $lowCount + $outCount,
            'items' => $evaluated,
        ];
    }

    /**
     * @return Collection<int, array{item_type:string, item_id:int, name:string, reminder_stock:float}>
     */
    protected function getIngredientCandidates(): Collection
    {
        return Ingredient::query()
            ->where('is_active', true)
            ->whereNotNull('reminder_stock')
            ->where('reminder_stock', '>=', 0)
            ->get(['id', 'name', 'reminder_stock'])
            ->map(static fn (Ingredient $ingredient): array => [
                'item_type' => Ingredient::class,
                'item_id' => (int) $ingredient->id,
                'name' => $ingredient->name,
                'reminder_stock' => (float) $ingredient->reminder_stock,
            ]);
    }

    /**
     * @return Collection<int, array{item_type:string, item_id:int, name:string, reminder_stock:float}>
     */
    protected function getMenuVariantCandidates(): Collection
    {
        return MenuVariant::query()
            ->where('is_active', true)
            ->whereNotNull('reminder_stock')
            ->where('reminder_stock', '>=', 0)
            ->whereHas('menu', static fn ($query) => $query
                ->where('is_active', true)
                ->where('is_stock_managed', true))
            ->with('menu:id,name')
            ->get(['id', 'menu_id', 'kd_varian', 'reminder_stock'])
            ->map(static function (MenuVariant $variant): array {
                $menuName = $variant->menu?->name ?? 'Menu';

                return [
                    'item_type' => MenuVariant::class,
                    'item_id' => (int) $variant->id,
                    'name' => $menuName . ' - ' . $variant->kd_varian,
                    'reminder_stock' => (float) $variant->reminder_stock,
                ];
            });
    }
}
