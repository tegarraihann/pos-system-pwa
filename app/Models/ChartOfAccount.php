<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChartOfAccount extends Model
{
    use HasFactory;

    public const CATEGORY_ASSET = 'asset';
    public const CATEGORY_LIABILITY = 'liability';
    public const CATEGORY_EQUITY = 'equity';
    public const CATEGORY_REVENUE = 'revenue';
    public const CATEGORY_COGS = 'cogs';
    public const CATEGORY_EXPENSE = 'expense';

    public const BALANCE_DEBIT = 'debit';
    public const BALANCE_CREDIT = 'credit';

    protected $fillable = [
        'code',
        'name',
        'category',
        'normal_balance',
        'is_active',
        'is_system',
        'description',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_system' => 'boolean',
    ];

    public static function categoryOptions(): array
    {
        return [
            self::CATEGORY_ASSET => 'Aset',
            self::CATEGORY_LIABILITY => 'Liabilitas',
            self::CATEGORY_EQUITY => 'Ekuitas',
            self::CATEGORY_REVENUE => 'Pendapatan',
            self::CATEGORY_COGS => 'Harga Pokok Penjualan',
            self::CATEGORY_EXPENSE => 'Beban Operasional',
        ];
    }

    public static function normalBalanceOptions(): array
    {
        return [
            self::BALANCE_DEBIT => 'Debit',
            self::BALANCE_CREDIT => 'Kredit',
        ];
    }

    public function operatingExpenses()
    {
        return $this->hasMany(OperatingExpense::class);
    }
}
