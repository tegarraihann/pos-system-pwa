<?php

namespace App\Models;

use chillerlan\QRCode\QRCode;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class OrderingQr extends Model
{
    use HasFactory;

    public const TYPE_TABLE = 'table';

    protected $fillable = [
        'name',
        'slug',
        'type',
        'table_number',
        'stock_location_id',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $orderingQr): void {
            if (blank($orderingQr->slug)) {
                $orderingQr->slug = self::generateUniqueSlug(
                    $orderingQr->table_number ?: $orderingQr->name
                );
            }

            if (blank($orderingQr->type)) {
                $orderingQr->type = self::TYPE_TABLE;
            }
        });
    }

    public static function typeOptions(): array
    {
        return [
            self::TYPE_TABLE => 'QR Meja',
        ];
    }

    public static function generateUniqueSlug(string $value): string
    {
        $baseSlug = Str::slug($value);

        if ($baseSlug === '') {
            $baseSlug = 'qr-order';
        }

        $slug = $baseSlug;
        $counter = 2;

        while (static::query()->where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function stockLocation()
    {
        return $this->belongsTo(StockLocation::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function publicUrl(): string
    {
        return route('public-ordering.show', $this);
    }

    public function qrImageUri(): string
    {
        return (new QRCode())->render($this->publicUrl());
    }
}
