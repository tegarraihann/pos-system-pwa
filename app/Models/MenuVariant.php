<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MenuVariant extends Model
{
    use HasFactory;

    public const TEMPERATURE_HOT = 'Hot';
    public const TEMPERATURE_ICE = 'Ice';

    public const SUGAR_LEVEL_NO_SUGAR = 'No Sugar';
    public const SUGAR_LEVEL_LESS_SUGAR = 'Less Sugar';
    public const SUGAR_LEVEL_NORMAL = 'Normal';
    public const SUGAR_LEVEL_EXTRA_SUGAR = 'Extra Sugar';

    public const ICE_LEVEL_NO_ICE = 'No Ice';
    public const ICE_LEVEL_LESS = 'Less';
    public const ICE_LEVEL_NORMAL = 'Normal';
    public const ICE_LEVEL_EXTRA = 'Extra';

    protected $fillable = [
        'menu_id',
        'kd_varian',
        'size_varian',
        'temperature',
        'sugar_level',
        'ice_level',
        'price',
        'is_active',
        'stock',
        'reminder_stock',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
        'stock' => 'integer',
        'reminder_stock' => 'decimal:3',
    ];

    public static function temperatureOptions(): array
    {
        return [
            self::TEMPERATURE_HOT => 'Hot',
            self::TEMPERATURE_ICE => 'Ice',
        ];
    }

    public static function sugarLevelOptions(): array
    {
        return [
            self::SUGAR_LEVEL_NO_SUGAR => 'No Sugar',
            self::SUGAR_LEVEL_LESS_SUGAR => 'Less Sugar',
            self::SUGAR_LEVEL_NORMAL => 'Normal',
            self::SUGAR_LEVEL_EXTRA_SUGAR => 'Extra Sugar',
        ];
    }

    public static function iceLevelOptions(): array
    {
        return [
            self::ICE_LEVEL_NO_ICE => 'No Ice',
            self::ICE_LEVEL_LESS => 'Less',
            self::ICE_LEVEL_NORMAL => 'Normal',
            self::ICE_LEVEL_EXTRA => 'Extra',
        ];
    }

    public function menu()
    {
        return $this->belongsTo(Menu::class);
    }

    public function recipe()
    {
        return $this->hasOne(Recipe::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function stockMovementItems()
    {
        return $this->morphMany(StockMovementItem::class, 'item');
    }

    public function stockLevels()
    {
        return $this->morphMany(StockLevel::class, 'item');
    }
}
