<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'bill_id',
        'order_item_id',
        'qty',
        'total',
    ];

    protected $casts = [
        'qty' => 'decimal:3',
        'total' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $item): void {
            if (! $item->total) {
                $price = (float) ($item->orderItem?->price ?? 0);
                $discount = (float) ($item->orderItem?->discount_amount ?? 0);
                $qty = (float) $item->qty;
                $lineTotal = max(($price * $qty) - $discount, 0);
                $item->total = $lineTotal;
            }
        });

        static::saved(function (self $item): void {
            $item->bill?->refreshTotals();
        });

        static::deleted(function (self $item): void {
            $item->bill?->refreshTotals();
        });
    }

    public function bill()
    {
        return $this->belongsTo(Bill::class);
    }

    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class);
    }
}
