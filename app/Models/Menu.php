<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'description',
        'unit',
        'is_active',
        'is_stock_managed',
        'image_path',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_stock_managed' => 'boolean',
    ];

    public function variants()
    {
        return $this->hasMany(MenuVariant::class);
    }
}
