<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
}
