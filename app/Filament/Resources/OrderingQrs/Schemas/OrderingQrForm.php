<?php

namespace App\Filament\Resources\OrderingQrs\Schemas;

use App\Models\OrderingQr;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OrderingQrForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Informasi QR Pemesanan')
                    ->description('QR ini akan mengarahkan customer ke halaman menu publik untuk meja tertentu.')
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        Hidden::make('type')
                            ->default(OrderingQr::TYPE_TABLE),
                        TextInput::make('name')
                            ->label('Nama QR')
                            ->required()
                            ->maxLength(120)
                            ->placeholder('Contoh: Meja A1'),
                        TextInput::make('table_number')
                            ->label('Nomor Meja')
                            ->required()
                            ->maxLength(50)
                            ->placeholder('Contoh: A1'),
                        TextInput::make('slug')
                            ->label('Slug URL')
                            ->disabled()
                            ->dehydrated(false)
                            ->placeholder('Otomatis dibuat setelah data disimpan.'),
                        Placeholder::make('public_url')
                            ->label('URL Publik')
                            ->content(fn (?OrderingQr $record): string => $record?->publicUrl() ?? 'URL tersedia setelah QR disimpan.'),
                        Select::make('stock_location_id')
                            ->relationship('stockLocation', 'name')
                            ->label('Lokasi Stok')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->native(false),
                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true),
                        Textarea::make('notes')
                            ->label('Catatan')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
