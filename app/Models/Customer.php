<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'phone',
        'email',
        'is_member',
        'member_discount_percent',
        'member_since',
    ];

    protected $casts = [
        'is_member' => 'boolean',
        'member_discount_percent' => 'decimal:2',
        'member_since' => 'datetime',
    ];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
