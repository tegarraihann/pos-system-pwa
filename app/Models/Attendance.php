<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    public const STATUS_CHECKED_IN = 'checked_in';
    public const STATUS_CHECKED_OUT = 'checked_out';

    protected $fillable = [
        'user_id',
        'shift_date',
        'device_id',
        'status',
        'check_in_at',
        'check_in_lat',
        'check_in_lng',
        'check_out_at',
        'check_out_lat',
        'check_out_lng',
        'work_minutes',
    ];

    protected $casts = [
        'shift_date' => 'date',
        'check_in_at' => 'datetime',
        'check_out_at' => 'datetime',
        'check_in_lat' => 'float',
        'check_in_lng' => 'float',
        'check_out_lat' => 'float',
        'check_out_lng' => 'float',
        'work_minutes' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

