<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Recipe extends Model
{
    use HasFactory;

    protected $fillable = [
        'menu_variant_id',
        'prep_time_minutes',
        'notes',
    ];

    protected $casts = [
        'prep_time_minutes' => 'integer',
    ];

    public function menuVariant()
    {
        return $this->belongsTo(MenuVariant::class);
    }

    public function items()
    {
        return $this->hasMany(RecipeItem::class);
    }
}
