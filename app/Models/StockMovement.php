<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    use HasFactory;

    public const TYPE_IN = 'in';
    public const TYPE_OUT = 'out';
    public const TYPE_ADJUSTMENT = 'adjustment';
    public const TYPE_TRANSFER = 'transfer';

    public const ADJUSTMENT_INCREASE = 'increase';
    public const ADJUSTMENT_DECREASE = 'decrease';

    protected $fillable = [
        'type',
        'movement_date',
        'from_location_id',
        'to_location_id',
        'adjustment_type',
        'reference_no',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'movement_date' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::saved(function (): void {
            StockLevel::rebuild();
        });

        static::deleted(function (): void {
            StockLevel::rebuild();
        });
    }

    public function fromLocation()
    {
        return $this->belongsTo(StockLocation::class, 'from_location_id');
    }

    public function toLocation()
    {
        return $this->belongsTo(StockLocation::class, 'to_location_id');
    }

    public function items()
    {
        return $this->hasMany(StockMovementItem::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
