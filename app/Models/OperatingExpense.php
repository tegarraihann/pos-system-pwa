<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OperatingExpense extends Model
{
    use HasFactory;

    public const PAYMENT_CASH = 'cash';
    public const PAYMENT_TRANSFER = 'transfer';
    public const PAYMENT_QRIS = 'qris';
    public const PAYMENT_OTHER = 'other';

    protected $fillable = [
        'expense_date',
        'chart_of_account_id',
        'title',
        'amount',
        'payment_method',
        'reference_number',
        'description',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'expense_date' => 'datetime',
        'amount' => 'decimal:2',
    ];

    public static function paymentMethodOptions(): array
    {
        return [
            self::PAYMENT_CASH => 'Cash',
            self::PAYMENT_TRANSFER => 'Transfer Bank',
            self::PAYMENT_QRIS => 'QRIS',
            self::PAYMENT_OTHER => 'Lainnya',
        ];
    }

    public function account()
    {
        return $this->belongsTo(ChartOfAccount::class, 'chart_of_account_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
