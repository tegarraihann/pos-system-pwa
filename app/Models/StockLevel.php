<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class StockLevel extends Model
{
    use HasFactory;

    protected $fillable = [
        'location_id',
        'item_type',
        'item_id',
        'on_hand',
    ];

    protected $casts = [
        'on_hand' => 'decimal:3',
    ];

    public function location()
    {
        return $this->belongsTo(StockLocation::class, 'location_id');
    }

    public function item()
    {
        return $this->morphTo();
    }

    public static function getOnHand(int $locationId, string $itemType, int $itemId): float
    {
        return (float) static::query()
            ->where('location_id', $locationId)
            ->where('item_type', $itemType)
            ->where('item_id', $itemId)
            ->value('on_hand') ?? 0.0;
    }

    public static function rebuild(): void
    {
        $balances = [];

        StockMovementItem::query()
            ->with('movement')
            ->orderBy('id')
            ->chunk(200, function ($items) use (&$balances): void {
                foreach ($items as $item) {
                    $movement = $item->movement;

                    if (! $movement) {
                        continue;
                    }

                    $deltas = self::getMovementDeltas($movement, $item);

                    foreach ($deltas as $locationId => $delta) {
                        if (! $locationId) {
                            continue;
                        }

                        $key = $locationId . '|' . $item->item_type . '|' . $item->item_id;
                        $balances[$key] = ($balances[$key] ?? 0) + $delta;
                    }
                }
            });

        DB::transaction(function () use ($balances): void {
            static::query()->delete();

            if (empty($balances)) {
                return;
            }

            $now = now();
            $rows = [];

            foreach ($balances as $key => $onHand) {
                [$locationId, $itemType, $itemId] = explode('|', $key, 3);

                $rows[] = [
                    'location_id' => (int) $locationId,
                    'item_type' => $itemType,
                    'item_id' => (int) $itemId,
                    'on_hand' => max($onHand, 0),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            DB::table('stock_levels')->insert($rows);
        });

        static::syncMenuVariantStock();
    }

    protected static function getMovementDeltas(StockMovement $movement, StockMovementItem $item): array
    {
        return match ($movement->type) {
            StockMovement::TYPE_IN => [
                $movement->to_location_id => (float) $item->qty,
            ],
            StockMovement::TYPE_OUT => [
                $movement->from_location_id => (float) $item->qty * -1,
            ],
            StockMovement::TYPE_TRANSFER => [
                $movement->from_location_id => (float) $item->qty * -1,
                $movement->to_location_id => (float) $item->qty,
            ],
            StockMovement::TYPE_ADJUSTMENT => [
                $movement->from_location_id => $movement->adjustment_type === StockMovement::ADJUSTMENT_DECREASE
                    ? (float) $item->qty * -1
                    : (float) $item->qty,
            ],
            default => [],
        };
    }

    protected static function syncMenuVariantStock(): void
    {
        $totals = static::query()
            ->select('item_id', DB::raw('SUM(on_hand) as total'))
            ->where('item_type', MenuVariant::class)
            ->groupBy('item_id')
            ->pluck('total', 'item_id');

        if ($totals->isEmpty()) {
            MenuVariant::query()->update(['stock' => null]);

            return;
        }

        MenuVariant::query()->update(['stock' => 0]);

        foreach ($totals as $itemId => $total) {
            MenuVariant::query()
                ->whereKey($itemId)
                ->update(['stock' => (int) round($total)]);
        }
    }
}
