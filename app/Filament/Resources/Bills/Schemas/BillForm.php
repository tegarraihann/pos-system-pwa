<?php

namespace App\Filament\Resources\Bills\Schemas;

use App\Models\Bill;
use App\Models\Order;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class BillForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Tagihan')
                    ->description('Buat tagihan baru berdasarkan order.')
                    ->icon('heroicon-o-document-text')
                    ->columnSpanFull()
                    ->schema([
                        // Row 1: Order Info (3 columns)
                        Fieldset::make('Informasi Order')
                            ->columns(3)
                            ->schema([
                                Select::make('order_id')
                                    ->label('Order')
                                    ->options(fn (): array => Order::query()
                                        ->orderByDesc('id')
                                        ->limit(100)
                                        ->get()
                                        ->mapWithKeys(fn (Order $order) => [
                                            $order->id => $order->order_number . ' - Rp ' . number_format((float) $order->grand_total, 0, ',', '.'),
                                        ])
                                        ->all())
                                    ->searchable()
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function (Get $get, Set $set, ?int $state): void {
                                        if (! $state) {
                                            return;
                                        }

                                        $order = Order::find($state);

                                        if (! $order) {
                                            return;
                                        }

                                        // Auto-fill from Order
                                        $set('subtotal', (float) $order->subtotal);
                                        $set('discount_total', (float) $order->discount_total);
                                        $set('tax_total', (float) $order->tax_total);
                                        $set('service_total', (float) $order->service_total);
                                        $set('grand_total', (float) $order->grand_total);
                                        $set('paid_total', (float) $order->paid_total);

                                        // Auto-generate Bill No if empty
                                        if (empty($get('bill_no'))) {
                                            $set('bill_no', self::generateBillNumber());
                                        }
                                    }),

                                TextInput::make('bill_no')
                                    ->label('Nomor Tagihan')
                                    ->required()
                                    ->maxLength(20)
                                    ->placeholder('Otomatis'),

                                Select::make('status')
                                    ->label('Status')
                                    ->options([
                                        'unpaid' => 'Belum Bayar',
                                        'partial' => 'Sebagian',
                                        'paid' => 'Lunas',
                                    ])
                                    ->default('unpaid')
                                    ->required()
                                    ->native(false),
                            ]),

                        // Row 2: Financial Details (4 columns)
                        Fieldset::make('Rincian Biaya')
                            ->columns(4)
                            ->schema([
                                TextInput::make('subtotal')
                                    ->label('Subtotal')
                                    ->prefix('Rp')
                                    ->numeric()
                                    ->minValue(0)
                                    ->default(0)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (Get $get, Set $set) => self::recalculateGrandTotal($get, $set)),

                                TextInput::make('discount_total')
                                    ->label('Diskon')
                                    ->prefix('Rp')
                                    ->numeric()
                                    ->minValue(0)
                                    ->default(0)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (Get $get, Set $set) => self::recalculateGrandTotal($get, $set)),

                                TextInput::make('tax_total')
                                    ->label('Pajak')
                                    ->prefix('Rp')
                                    ->numeric()
                                    ->minValue(0)
                                    ->default(0)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (Get $get, Set $set) => self::recalculateGrandTotal($get, $set)),

                                TextInput::make('service_total')
                                    ->label('Service')
                                    ->prefix('Rp')
                                    ->numeric()
                                    ->minValue(0)
                                    ->default(0)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (Get $get, Set $set) => self::recalculateGrandTotal($get, $set)),
                            ]),

                        // Row 3: Totals (2 columns, Grand Total prominent)
                        Fieldset::make('Total Pembayaran')
                            ->columns(2)
                            ->schema([
                                TextInput::make('grand_total')
                                    ->label('GRAND TOTAL')
                                    ->prefix('Rp')
                                    ->numeric()
                                    ->minValue(0)
                                    ->default(0)
                                    ->readOnly()
                                    ->extraInputAttributes(['class' => 'text-xl font-bold']),

                                TextInput::make('paid_total')
                                    ->label('Sudah Dibayar')
                                    ->prefix('Rp')
                                    ->numeric()
                                    ->minValue(0)
                                    ->default(0),
                            ]),
                    ]),
            ]);
    }

    /**
     * Generate a unique bill number.
     */
    protected static function generateBillNumber(): string
    {
        $prefix = 'BILL-' . now()->format('Ymd') . '-';

        $lastBillNo = Bill::query()
            ->where('bill_no', 'like', $prefix . '%')
            ->orderByDesc('bill_no')
            ->value('bill_no');

        $lastSequence = 0;

        if ($lastBillNo) {
            $lastSequence = (int) substr($lastBillNo, -4);
        }

        $sequence = str_pad((string) ($lastSequence + 1), 4, '0', STR_PAD_LEFT);

        return $prefix . $sequence;
    }

    /**
     * Recalculate grand total based on component values.
     * Formula: GrandTotal = (Subtotal - Discount) + Tax + Service
     */
    protected static function recalculateGrandTotal(Get $get, Set $set): void
    {
        $subtotal = (float) ($get('subtotal') ?? 0);
        $discount = (float) ($get('discount_total') ?? 0);
        $tax = (float) ($get('tax_total') ?? 0);
        $service = (float) ($get('service_total') ?? 0);

        $grandTotal = ($subtotal - $discount) + $tax + $service;

        $set('grand_total', max($grandTotal, 0));
    }
}
