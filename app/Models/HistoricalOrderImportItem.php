<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HistoricalOrderImportItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'historical_order_import_id',
        'menu_variant_id',
        'raw_item_name',
        'normalized_item_name',
        'listed_qty',
        'inferred_qty',
        'unit_price',
        'line_total_inferred',
        'mapping_status',
        'notes',
    ];

    protected $casts = [
        'listed_qty' => 'decimal:3',
        'inferred_qty' => 'decimal:3',
        'unit_price' => 'decimal:2',
        'line_total_inferred' => 'decimal:2',
    ];

    public function historicalOrderImport()
    {
        return $this->belongsTo(HistoricalOrderImport::class);
    }

    public function menuVariant()
    {
        return $this->belongsTo(MenuVariant::class);
    }

    public function getMenuLabelAttribute(): string
    {
        return $this->menuVariant?->menu?->name ?? '-';
    }
}
