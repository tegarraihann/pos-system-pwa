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
                Section::make('Informasi Customer')
                    ->description('Data identitas customer untuk transaksi dan membership.')
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        TextInput::make('code')
                            ->label('Kode Customer')
                            ->required()
                            ->maxLength(50)
                            ->unique(Customer::class, 'code', ignoreRecord: true)
                            ->placeholder('Contoh: CUST-001'),
                        TextInput::make('name')
                            ->label('Nama Customer')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Contoh: Budi Santoso'),
                    ]),
                Section::make('Kontak & Status')
                    ->description('Kontak customer dan status member.')
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        TextInput::make('phone')
                            ->label('No. Telepon')
                            ->tel()
                            ->maxLength(30)
                            ->placeholder('Contoh: 081234567890'),
                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->maxLength(255)
                            ->placeholder('contoh@domain.com'),
                        Toggle::make('is_member')
                            ->label('Member')
                            ->default(false)
                            ->inline(false)
                            ->columnSpan(1),
                    ]),
            ]);
    }
}
