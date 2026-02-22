<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class StockLocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'type',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function stockLevels()
    {
        return $this->hasMany(StockLevel::class, 'location_id');
    }

    public function fromMovements()
    {
        return $this->hasMany(StockMovement::class, 'from_location_id');
    }

    public function toMovements()
    {
        return $this->hasMany(StockMovement::class, 'to_location_id');
    }

    public static function isMultiLocationEnabled(): bool
    {
        return (bool) config('stock.multi_location', true);
    }

    public static function getDefaultLocationCode(): string
    {
        return (string) config('stock.default_location_code', 'GUDANG');
    }

    public static function resolveDefaultLocation(): ?self
    {
        return static::query()
            ->where('code', static::getDefaultLocationCode())
            ->where('is_active', true)
            ->first()
            ?? static::query()
                ->where('is_active', true)
                ->orderBy('id')
                ->first();
    }

    public static function activeQuery(): Builder
    {
        return static::query()->where('is_active', true);
    }
}
