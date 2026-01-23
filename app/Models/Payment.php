<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'method',
        'amount',
        'status',
        'gateway_provider',
        'gateway_ref',
        'paid_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::saved(function (self $payment): void {
            $payment->order?->refreshPaidTotal();
        });

        static::deleted(function (self $payment): void {
            $payment->order?->refreshPaidTotal();
        });
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
