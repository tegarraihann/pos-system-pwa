<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Menu extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'category',
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

    protected static function booted(): void
    {
        static::creating(function (self $menu): void {
            if (blank($menu->code)) {
                $menu->code = self::generateCode();
            }
        });
    }

    public static function generateCode(): string
    {
        $prefix = 'MENU-';

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

    public function variants()
    {
        return $this->hasMany(MenuVariant::class);
    }
}
