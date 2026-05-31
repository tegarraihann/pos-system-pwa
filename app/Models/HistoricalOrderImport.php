<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HistoricalOrderImport extends Model
{
    use HasFactory;

    public const STATUS_MATCHED = 'matched';
    public const STATUS_PARTIAL = 'partial';
    public const STATUS_AMBIGUOUS = 'ambiguous';
    public const STATUS_UNMATCHED = 'unmatched';

    protected $fillable = [
        'source_file',
        'source_order_number',
        'outlet_name',
        'ordered_at',
        'paid_at',
        'payment_method_raw',
        'payment_method_mapped',
        'payment_channel_raw',
        'operator_raw',
        'raw_products',
        'normalized_products',
        'unpaid_amount',
        'total_amount',
        'base_mapped_total',
        'price_gap',
        'mapping_status',
        'ready_for_migration',
        'reviewed_by',
        'reviewed_at',
        'review_notes',
        'migrated_order_id',
        'migrated_at',
        'migration_notes',
        'notes',
        'imported_at',
    ];

    protected $casts = [
        'ordered_at' => 'datetime',
        'paid_at' => 'datetime',
        'normalized_products' => 'array',
        'unpaid_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'base_mapped_total' => 'decimal:2',
        'price_gap' => 'decimal:2',
        'ready_for_migration' => 'boolean',
        'reviewed_at' => 'datetime',
        'migrated_at' => 'datetime',
        'imported_at' => 'datetime',
    ];

    public function items()
    {
        return $this->hasMany(HistoricalOrderImportItem::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function migratedOrder()
    {
        return $this->belongsTo(Order::class, 'migrated_order_id');
    }

    public static function statusOptions(): array
    {
        return [
            self::STATUS_MATCHED => 'Matched',
            self::STATUS_PARTIAL => 'Partial',
            self::STATUS_AMBIGUOUS => 'Ambiguous',
            self::STATUS_UNMATCHED => 'Unmatched',
        ];
    }
}
