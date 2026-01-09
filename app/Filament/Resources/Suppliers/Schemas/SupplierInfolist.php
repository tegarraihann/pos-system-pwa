<?php

namespace App\Filament\Resources\Suppliers\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class SupplierInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name')
                    ->label('Nama Supplier'),
                TextEntry::make('pic_name')
                    ->label('PIC Supplier')
                    ->placeholder('-'),
                TextEntry::make('email')
                    ->label('Email')
                    ->placeholder('-'),
                TextEntry::make('phone')
                    ->label('No. Telp')
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
