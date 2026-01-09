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
                Section::make('Supplier')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama Supplier')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('pic_name')
                            ->label('PIC Supplier')
                            ->maxLength(255),
                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->maxLength(255),
                        TextInput::make('phone')
                            ->label('No. Telp')
                            ->maxLength(50),
                    ]),
            ]);
    }
}
