<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Models\Order;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OrderInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Order')
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        TextEntry::make('order_number')
                            ->label('Nomor Order'),
                        TextEntry::make('ordered_at')
                            ->label('Waktu Order')
                            ->dateTime(),
                        TextEntry::make('order_type')
                            ->label('Tipe Order')
                            ->formatStateUsing(static fn (string $state): string => match ($state) {
                                Order::TYPE_DINE_IN => 'Dine In',
                                Order::TYPE_TAKE_AWAY => 'Take Away',
                                Order::TYPE_DELIVERY => 'Delivery',
                                default => $state,
                            }),
                        TextEntry::make('status')
                            ->label('Status')
                            ->formatStateUsing(static fn (string $state): string => match ($state) {
                                Order::STATUS_DRAFT => 'Draft',
                                Order::STATUS_QUEUED => 'Queued',
                                Order::STATUS_PREPARING => 'Preparing',
                                Order::STATUS_READY => 'Ready',
                                Order::STATUS_SERVED => 'Served',
                                Order::STATUS_CANCELED => 'Canceled',
                                default => $state,
                            }),
                        TextEntry::make('customer_type')
                            ->label('Tipe Customer'),
                        TextEntry::make('customer.name')
                            ->label('Customer')
                            ->placeholder('-'),
                        TextEntry::make('stockLocation.name')
                            ->label('Lokasi Stok')
                            ->placeholder('-'),
                        TextEntry::make('table_number')
                            ->label('Nomor Meja')
                            ->placeholder('-'),
                        TextEntry::make('queue_number')
                            ->label('Nomor Antrian')
                            ->placeholder('-'),
                        TextEntry::make('subtotal')
                            ->label('Subtotal')
                            ->money('IDR', locale: 'id'),
                        TextEntry::make('discount_total')
                            ->label('Total Diskon')
                            ->money('IDR', locale: 'id'),
                        TextEntry::make('tax_total')
                            ->label('Pajak')
                            ->money('IDR', locale: 'id'),
                        TextEntry::make('service_total')
                            ->label('Service')
                            ->money('IDR', locale: 'id'),
                        TextEntry::make('grand_total')
                            ->label('Grand Total')
                            ->money('IDR', locale: 'id'),
                        TextEntry::make('paid_total')
                            ->label('Total Dibayar')
                            ->money('IDR', locale: 'id'),
                        TextEntry::make('notes')
                            ->label('Catatan')
                            ->placeholder('-')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
