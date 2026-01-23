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
    ];

    protected $casts = [
        'is_member' => 'boolean',
    ];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
