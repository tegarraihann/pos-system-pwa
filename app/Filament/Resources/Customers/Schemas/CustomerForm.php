<?php

namespace App\Filament\Resources\Customers\Schemas;

use App\Models\Customer;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CustomerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Customer')
                    ->columns(2)
                    ->schema([
                        TextInput::make('code')
                            ->label('Kode Customer')
                            ->required()
                            ->maxLength(50)
                            ->unique(Customer::class, 'code', ignoreRecord: true),
                        TextInput::make('name')
                            ->label('Nama Customer')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('phone')
                            ->label('No. Telepon')
                            ->maxLength(30),
                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->maxLength(255),
                        Toggle::make('is_member')
                            ->label('Member')
                            ->default(false),
                    ]),
            ]);
    }
}
