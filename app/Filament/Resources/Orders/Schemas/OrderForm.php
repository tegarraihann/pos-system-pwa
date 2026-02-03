<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Models\Customer;
use App\Models\Order;
use App\Models\StockLocation;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Order')
                    ->columns(2)
                    ->schema([
                        TextInput::make('order_number')
                            ->label('Nomor Order')
                            ->disabled()
                            ->dehydrated(false)
                            ->placeholder('Auto'),
                        DateTimePicker::make('ordered_at')
                            ->label('Waktu Order')
                            ->default(now()),
                        Select::make('order_type')
                            ->label('Tipe Order')
                            ->options([
                                Order::TYPE_DINE_IN => 'Dine In',
                                Order::TYPE_TAKE_AWAY => 'Take Away',
                                Order::TYPE_DELIVERY => 'Delivery',
                            ])
                            ->required(),
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                Order::STATUS_DRAFT => 'Draft',
                                Order::STATUS_RECEIVED => 'Received',
                                Order::STATUS_QUEUED => 'Queued',
                                Order::STATUS_PREPARING => 'Preparing',
                                Order::STATUS_READY => 'Ready',
                                Order::STATUS_SERVED => 'Served',
                                Order::STATUS_CANCELED => 'Canceled',
                            ])
                            ->required(),
                        Select::make('customer_type')
                            ->label('Tipe Customer')
                            ->options([
                                Order::CUSTOMER_WALK_IN => 'Walk In',
                                Order::CUSTOMER_MEMBER => 'Member',
                            ])
                            ->default(Order::CUSTOMER_WALK_IN)
                            ->live(),
                        Select::make('customer_id')
                            ->label('Customer Member')
                            ->options(fn (): array => Customer::query()
                                ->orderBy('name')
                                ->pluck('name', 'id')
                                ->all())
                            ->searchable()
                            ->visible(fn (Get $get): bool => $get('customer_type') === Order::CUSTOMER_MEMBER),
                        Select::make('stock_location_id')
                            ->label('Lokasi Stok')
                            ->options(fn (): array => StockLocation::query()
                                ->where('is_active', true)
                                ->orderBy('name')
                                ->pluck('name', 'id')
                                ->all())
                            ->searchable()
                            ->required(),
                        TextInput::make('table_number')
                            ->label('Nomor Meja')
                            ->maxLength(50),
                        TextInput::make('queue_number')
                            ->label('Nomor Antrian')
                            ->numeric()
                            ->minValue(1),
                        Textarea::make('notes')
                            ->label('Catatan')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
                Section::make('Total')
                    ->columns(2)
                    ->schema([
                        TextInput::make('subtotal')
                            ->label('Subtotal')
                            ->prefix('Rp')
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('discount_total')
                            ->label('Total Diskon')
                            ->prefix('Rp')
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('tax_total')
                            ->label('Pajak')
                            ->prefix('Rp')
                            ->numeric()
                            ->minValue(0),
                        TextInput::make('service_total')
                            ->label('Service')
                            ->prefix('Rp')
                            ->numeric()
                            ->minValue(0),
                        TextInput::make('grand_total')
                            ->label('Grand Total')
                            ->prefix('Rp')
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('paid_total')
                            ->label('Total Dibayar')
                            ->prefix('Rp')
                            ->disabled()
                            ->dehydrated(false),
                    ]),
            ]);
    }
}
