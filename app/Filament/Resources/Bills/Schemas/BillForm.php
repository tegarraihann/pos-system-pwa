<?php

namespace App\Filament\Resources\Bills\Schemas;

use App\Models\Order;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class BillForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Bill')
                    ->columns(2)
                    ->schema([
                        Select::make('order_id')
                            ->label('Order')
                            ->options(fn (): array => Order::query()
                                ->orderByDesc('id')
                                ->pluck('order_number', 'id')
                                ->all())
                            ->searchable()
                            ->required(),
                        TextInput::make('bill_no')
                            ->label('Bill No')
                            ->required()
                            ->maxLength(20),
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'unpaid' => 'Unpaid',
                                'partial' => 'Partial',
                                'paid' => 'Paid',
                            ])
                            ->required(),
                        TextInput::make('subtotal')
                            ->label('Subtotal')
                            ->prefix('Rp')
                            ->numeric()
                            ->minValue(0)
                            ->default(0),
                        TextInput::make('discount_total')
                            ->label('Diskon')
                            ->prefix('Rp')
                            ->numeric()
                            ->minValue(0)
                            ->default(0),
                        TextInput::make('tax_total')
                            ->label('Pajak')
                            ->prefix('Rp')
                            ->numeric()
                            ->minValue(0)
                            ->default(0),
                        TextInput::make('service_total')
                            ->label('Service')
                            ->prefix('Rp')
                            ->numeric()
                            ->minValue(0)
                            ->default(0),
                        TextInput::make('grand_total')
                            ->label('Grand Total')
                            ->prefix('Rp')
                            ->numeric()
                            ->minValue(0)
                            ->default(0),
                        TextInput::make('paid_total')
                            ->label('Dibayar')
                            ->prefix('Rp')
                            ->numeric()
                            ->minValue(0)
                            ->default(0),
                    ]),
            ]);
    }
}
