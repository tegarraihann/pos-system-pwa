<?php

namespace App\Filament\Resources\Bills\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class BillInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Bill')
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        TextEntry::make('order.order_number')
                            ->label('Order'),
                        TextEntry::make('bill_no')
                            ->label('Bill No'),
                        TextEntry::make('status')
                            ->label('Status'),
                        TextEntry::make('subtotal')
                            ->label('Subtotal')
                            ->money('IDR', locale: 'id'),
                        TextEntry::make('discount_total')
                            ->label('Diskon')
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
                            ->label('Dibayar')
                            ->money('IDR', locale: 'id'),
                        TextEntry::make('created_at')
                            ->dateTime()
                            ->placeholder('-'),
                        TextEntry::make('updated_at')
                            ->dateTime()
                            ->placeholder('-'),
                    ]),
            ]);
    }
}
