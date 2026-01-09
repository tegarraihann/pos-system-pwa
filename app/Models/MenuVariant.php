<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MenuVariant extends Model
{
    use HasFactory;

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
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
        'stock' => 'integer',
    ];

    public function menu()
    {
        return $this->belongsTo(Menu::class);
    }

    public function recipe()
    {
        return $this->hasOne(Recipe::class);
    }
}
