<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        'is_active',
    ];

    protected $casts = [
        'purchase_price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

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
}
