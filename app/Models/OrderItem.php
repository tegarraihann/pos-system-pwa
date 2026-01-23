<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'menu_variant_id',
        'item_name_snapshot',
        'price',
        'qty',
        'discount_amount',
        'total',
        'notes',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'qty' => 'decimal:3',
        'discount_amount' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $item): void {
            if (! $item->item_name_snapshot) {
                $menuName = $item->menuVariant?->menu?->name ?? '-';
                $variantCode = $item->menuVariant?->kd_varian ?? '-';
                $item->item_name_snapshot = "{$menuName} - {$variantCode}";
            }

            $price = (float) $item->price;
            $qty = (float) $item->qty;
            $discount = (float) $item->discount_amount;

            $item->total = max(($price * $qty) - $discount, 0);
        });

        static::saved(function (self $item): void {
            $item->order?->recalculateTotals();
        });

        static::deleted(function (self $item): void {
            $item->order?->recalculateTotals();
        });
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function menuVariant()
    {
        return $this->belongsTo(MenuVariant::class);
    }

    public function modifiers()
    {
        return $this->hasMany(OrderItemModifier::class);
    }
}
