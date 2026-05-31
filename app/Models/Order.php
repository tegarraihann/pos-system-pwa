<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    public const STATUS_PENDING_PAYMENT = 'pending_payment';
    public const STATUS_PENDING_CONFIRMATION = 'pending_confirmation';
    public const STATUS_PAID = 'paid';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_DRAFT = 'draft';
    public const STATUS_SERVED = 'served';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_FAILED = 'failed';
    public const STATUS_CANCELED = 'canceled';

    public const TYPE_DINE_IN = 'dine_in';
    public const TYPE_TAKE_AWAY = 'take_away';
    public const TYPE_DELIVERY = 'delivery';

    public const CUSTOMER_WALK_IN = 'walk_in';
    public const CUSTOMER_MEMBER = 'member';

    public const PAYMENT_CASH = 'cash';
    public const PAYMENT_MIDTRANS = 'midtrans';

    public const SOURCE_POS = 'pos';
    public const SOURCE_PUBLIC_QR = 'public_qr';

    public const SYNC_STATUS_SYNCED = 'synced';
    public const SYNC_STATUS_PENDING = 'pending_sync';
    public const SYNC_STATUS_FAILED = 'failed_sync';

    protected $fillable = [
        'order_number',
        'ordered_at',
        'order_type',
        'status',
        'customer_type',
        'order_source',
        'customer_id',
        'ordering_qr_id',
        'guest_name',
        'guest_phone',
        'payment_method',
        'sync_status',
        'client_txn_id',
        'synced_at',
        'sync_error',
        'stock_location_id',
        'table_number',
        'queue_number',
        'notes',
        'subtotal',
        'discount_total',
        'member_discount_percent',
        'member_discount_total',
        'tax_total',
        'service_total',
        'grand_total',
        'paid_total',
        'cogs_total',
        'gross_profit_total',
        'cost_accounted_at',
        'cancel_reason',
        'canceled_at',
        'created_by',
        'cashier_session_id',
    ];

    protected $casts = [
        'ordered_at' => 'datetime',
        'canceled_at' => 'datetime',
        'subtotal' => 'decimal:2',
        'discount_total' => 'decimal:2',
        'member_discount_percent' => 'decimal:2',
        'member_discount_total' => 'decimal:2',
        'tax_total' => 'decimal:2',
        'service_total' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'paid_total' => 'decimal:2',
        'cogs_total' => 'decimal:2',
        'gross_profit_total' => 'decimal:2',
        'cost_accounted_at' => 'datetime',
        'synced_at' => 'datetime',
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

            if (! $order->order_source) {
                $order->order_source = self::SOURCE_POS;
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

    public static function statusOptions(): array
    {
        return [
            self::STATUS_PENDING_PAYMENT => 'Menunggu Pembayaran',
            self::STATUS_PENDING_CONFIRMATION => 'Menunggu Konfirmasi',
            self::STATUS_PAID => 'Sudah Dibayar',
            self::STATUS_PROCESSING => 'Sedang Diproses',
            self::STATUS_DRAFT => 'Draf',
            self::STATUS_SERVED => 'Selesai',
            self::STATUS_EXPIRED => 'Kadaluarsa',
            self::STATUS_FAILED => 'Gagal',
            self::STATUS_CANCELED => 'Dibatalkan',
        ];
    }

    public static function typeOptions(): array
    {
        return [
            self::TYPE_DINE_IN => 'Dine In',
            self::TYPE_TAKE_AWAY => 'Take Away',
            self::TYPE_DELIVERY => 'Delivery',
        ];
    }

    public static function customerTypeOptions(): array
    {
        return [
            self::CUSTOMER_WALK_IN => 'Walk In',
            self::CUSTOMER_MEMBER => 'Member',
        ];
    }

    public static function sourceOptions(): array
    {
        return [
            self::SOURCE_POS => 'POS Internal',
            self::SOURCE_PUBLIC_QR => 'QR Publik',
        ];
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function orderingQr()
    {
        return $this->belongsTo(OrderingQr::class);
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

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function cashierSession()
    {
        return $this->belongsTo(CashierSession::class);
    }

    public function isPublicQr(): bool
    {
        return $this->order_source === self::SOURCE_PUBLIC_QR;
    }

    public function recalculateTotals(): void
    {
        $items = $this->items()->get();

        $itemDiscountTotal = $items->sum('discount_amount');
        $lineTotal = $items->sum('total');
        $subtotal = $lineTotal + $itemDiscountTotal;
        $memberDiscountTotal = (float) $this->member_discount_total;
        $discountTotal = $itemDiscountTotal + $memberDiscountTotal;
        $grandTotal = $subtotal - $discountTotal + (float) $this->tax_total + (float) $this->service_total;

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
