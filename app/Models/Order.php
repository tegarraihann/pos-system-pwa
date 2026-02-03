<?php

namespace App\Models;

use App\Events\OrderCreated;
use App\Events\OrderUpdated;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    public const STATUS_DRAFT = 'draft';
    public const STATUS_RECEIVED = 'received';
    public const STATUS_QUEUED = 'queued';
    public const STATUS_PREPARING = 'preparing';
    public const STATUS_READY = 'ready';
    public const STATUS_SERVED = 'served';
    public const STATUS_CANCELED = 'canceled';

    public const TYPE_DINE_IN = 'dine_in';
    public const TYPE_TAKE_AWAY = 'take_away';
    public const TYPE_DELIVERY = 'delivery';

    public const CUSTOMER_WALK_IN = 'walk_in';
    public const CUSTOMER_MEMBER = 'member';

    protected $fillable = [
        'order_number',
        'ordered_at',
        'order_type',
        'status',
        'customer_type',
        'customer_id',
        'stock_location_id',
        'table_number',
        'queue_number',
        'notes',
        'subtotal',
        'discount_total',
        'tax_total',
        'service_total',
        'grand_total',
        'paid_total',
        'received_at',
        'ready_at',
        'is_priority',
        'cancel_reason',
        'canceled_at',
        'created_by',
    ];

    protected $casts = [
        'ordered_at' => 'datetime',
        'canceled_at' => 'datetime',
        'subtotal' => 'decimal:2',
        'discount_total' => 'decimal:2',
        'tax_total' => 'decimal:2',
        'service_total' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'paid_total' => 'decimal:2',
        'received_at' => 'datetime',
        'ready_at' => 'datetime',
        'is_priority' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $order): void {
            if (! $order->order_number) {
                $order->order_number = self::generateOrderNumber();
            }

            if (! $order->ordered_at) {
                $order->ordered_at = now();
            }
        });

        static::created(function (self $order): void {
            event(new OrderCreated($order));
        });

        static::updated(function (self $order): void {
            if ($order->wasChanged(['status', 'is_priority', 'received_at', 'ready_at'])) {
                event(new OrderUpdated($order));
            }
        });

        static::saved(function (self $order): void {
            $order->recalculateTotals();
            $order->refreshPaidTotal();
        });
    }

    public static function generateOrderNumber(): string
    {
        $prefix = now()->format('Ymd') . '-';

        $lastOrderNumber = static::query()
            ->where('order_number', 'like', $prefix . '%')
            ->orderBy('order_number', 'desc')
            ->value('order_number');

        $lastSequence = 0;

        if ($lastOrderNumber) {
            $lastSequence = (int) substr($lastOrderNumber, -4);
        }

        $sequence = str_pad((string) ($lastSequence + 1), 4, '0', STR_PAD_LEFT);

        return $prefix . $sequence;
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function stockLocation()
    {
        return $this->belongsTo(StockLocation::class, 'stock_location_id');
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function bills()
    {
        return $this->hasMany(Bill::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function recalculateTotals(): void
    {
        $items = $this->items()->get();

        $discountTotal = $items->sum('discount_amount');
        $lineTotal = $items->sum('total');
        $subtotal = $lineTotal + $discountTotal;
        $grandTotal = $lineTotal + (float) $this->tax_total + (float) $this->service_total;

        $this->updateQuietly([
            'subtotal' => $subtotal,
            'discount_total' => $discountTotal,
            'grand_total' => max($grandTotal, 0),
        ]);
    }

    public function refreshPaidTotal(): void
    {
        $paidTotal = $this->payments()
            ->where('status', 'paid')
            ->sum('amount');

        $this->updateQuietly([
            'paid_total' => $paidTotal,
        ]);
    }
}
