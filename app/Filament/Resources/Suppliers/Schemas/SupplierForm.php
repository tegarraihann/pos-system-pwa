<?php

namespace App\Filament\Resources\Suppliers\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;

class SupplierForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Supplier')
                    ->description('Lengkapi data utama supplier dan kontak PIC.')
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama Supplier')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Contoh: PT Sumber Pangan'),
                        TextInput::make('pic_name')
                            ->label('PIC Supplier')
                            ->maxLength(255)
                            ->placeholder('Contoh: Budi Santoso'),
                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->maxLength(255)
                            ->placeholder('contoh@domain.com'),
                        TextInput::make('phone')
                            ->label('No. Telp')
                            ->tel()
                            ->maxLength(50)
                            ->placeholder('Contoh: 081234567890'),
                    ]),
            ]);
    }
}
