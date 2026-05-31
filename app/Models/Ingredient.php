<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Ingredient extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'unit',
        'ingredient_category_id',
        'supplier_id',
        'purchase_price',
        'reminder_stock',
        'is_active',
    ];

    protected $casts = [
        'purchase_price' => 'decimal:2',
        'reminder_stock' => 'decimal:3',
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $ingredient): void {
            if (blank($ingredient->code)) {
                $ingredient->code = self::generateCode();
            }
        });
    }

    public static function generateCode(): string
    {
        $prefix = 'ING-';

        $latestCode = static::query()
            ->where('code', 'like', $prefix . '%')
            ->orderByDesc('id')
            ->value('code');

        if (! $latestCode) {
            return $prefix . '0001';
        }

        $suffix = Str::after($latestCode, $prefix);

        if (ctype_digit($suffix)) {
            return $prefix . str_pad((string) (((int) $suffix) + 1), 4, '0', STR_PAD_LEFT);
        }

        return $prefix . str_pad((string) (static::query()->count() + 1), 4, '0', STR_PAD_LEFT);
    }

    public function category()
    {
        return $this->belongsTo(IngredientCategory::class, 'ingredient_category_id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function recipeItems()
    {
        return $this->hasMany(RecipeItem::class);
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
