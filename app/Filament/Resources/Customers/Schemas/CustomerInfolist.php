<?php

namespace App\Filament\Resources\Customers\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CustomerInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Customer')
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        TextEntry::make('code')
                            ->label('Kode'),
                        TextEntry::make('name')
                            ->label('Nama'),
                        TextEntry::make('phone')
                            ->label('No. Telepon')
                            ->placeholder('-'),
                        TextEntry::make('email')
                            ->label('Email')
                            ->placeholder('-'),
                        TextEntry::make('is_member')
                            ->label('Member')
                            ->formatStateUsing(fn (bool $state): string => $state ? 'Ya' : 'Tidak'),
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
