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
                            ->formatStateUsing(static fn (string $state): string => Order::typeOptions()[$state] ?? $state),
                        TextEntry::make('status')
                            ->label('Status')
                            ->formatStateUsing(static fn (string $state): string => Order::statusOptions()[$state] ?? $state),
                        TextEntry::make('order_source')
                            ->label('Sumber Order')
                            ->formatStateUsing(static fn (string $state): string => Order::sourceOptions()[$state] ?? $state),
                        TextEntry::make('customer_type')
                            ->label('Tipe Customer'),
                        TextEntry::make('customer.name')
                            ->label('Customer')
                            ->placeholder('-'),
                        TextEntry::make('guest_name')
                            ->label('Nama Guest')
                            ->placeholder('-'),
                        TextEntry::make('guest_phone')
                            ->label('Nomor HP Guest')
                            ->placeholder('-'),
                        TextEntry::make('orderingQr.name')
                            ->label('QR Pemesanan')
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
                        TextEntry::make('member_discount_percent')
                            ->label('Diskon Member (%)')
                            ->formatStateUsing(static fn (mixed $state): string => number_format((float) $state, 2, ',', '.') . '%'),
                        TextEntry::make('member_discount_total')
                            ->label('Diskon Member')
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
