<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bill extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'bill_no',
        'subtotal',
        'discount_total',
        'tax_total',
        'service_total',
        'grand_total',
        'paid_total',
        'status',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount_total' => 'decimal:2',
        'tax_total' => 'decimal:2',
        'service_total' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'paid_total' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::saved(function (self $bill): void {
            $bill->refreshTotals();
        });

        static::deleted(function (self $bill): void {
            $bill->order?->recalculateTotals();
        });
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function items()
    {
        return $this->hasMany(BillItem::class);
    }

    public function payments()
    {
        return $this->hasMany(BillPayment::class);
    }

    public function refreshTotals(): void
    {
        $items = $this->items()->get();
        $lineTotal = $items->sum('total');
        $grandTotal = $lineTotal + (float) $this->tax_total + (float) $this->service_total;

        $paidTotal = $this->payments()
            ->where('status', 'paid')
            ->sum('amount');

        $this->updateQuietly([
            'subtotal' => $lineTotal,
            'grand_total' => max($grandTotal, 0),
            'paid_total' => $paidTotal,
        ]);
    }
}
