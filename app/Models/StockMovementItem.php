<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class StockMovementItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'stock_movement_id',
        'item_type',
        'item_id',
        'qty',
        'unit',
        'cost',
    ];

    protected $casts = [
        'qty' => 'decimal:3',
        'cost' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $item): void {
            $movement = $item->movement()->first();

            if (! $movement) {
                return;
            }

            $requiresStockCheck = in_array($movement->type, [
                StockMovement::TYPE_OUT,
                StockMovement::TYPE_TRANSFER,
                StockMovement::TYPE_ADJUSTMENT,
            ], true);

            if (! $requiresStockCheck) {
                return;
            }

            $isDecrease = $movement->type === StockMovement::TYPE_ADJUSTMENT
                ? $movement->adjustment_type === StockMovement::ADJUSTMENT_DECREASE
                : true;

            if (! $isDecrease) {
                return;
            }

            $locationId = $movement->from_location_id;

            if (! $locationId) {
                return;
            }

            $available = StockLevel::getOnHand(
                locationId: $locationId,
                itemType: $item->item_type,
                itemId: $item->item_id,
            );

            if ($item->exists) {
                $available = $available + (float) $item->getOriginal('qty');
            }

            if ($available < (float) $item->qty) {
                throw ValidationException::withMessages([
                    'qty' => 'Stok tidak mencukupi untuk pergerakan ini.',
                ]);
            }
        });

        static::saved(function (): void {
            StockLevel::rebuild();
        });

        static::deleted(function (): void {
            StockLevel::rebuild();
        });
    }

    public function movement()
    {
        return $this->belongsTo(StockMovement::class, 'stock_movement_id');
    }

    public function item()
    {
        return $this->morphTo();
    }

    public function getItemLabelAttribute(): string
    {
        $item = $this->item;

        if ($item instanceof Ingredient) {
            return "{$item->code} - {$item->name}";
        }

        if ($item instanceof MenuVariant) {
            $menuName = $item->menu?->name ?? '-';

            return "{$menuName} - {$item->kd_varian}";
        }

        return (string) ($item->name ?? $item->id ?? '-');
    }
}
