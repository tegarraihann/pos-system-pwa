<?php

namespace App\Filament\Resources\Menus\Schemas;

use App\Models\Menu;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\FileUpload;

class MenuForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Menu')
                    ->columns(2)
                    ->schema([
                        TextInput::make('code')
                            ->label('Kode Menu/SKU')
                            ->required()
                            ->maxLength(50)
                            ->unique(Menu::class, 'code', ignoreRecord: true),
                        TextInput::make('name')
                            ->label('Nama Menu')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('category')
                            ->label('Kategori')
                            ->maxLength(100)
                            ->placeholder('Makanan / Minuman'),
                        TextInput::make('unit')
                            ->label('Satuan')
                            ->maxLength(50),
                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true),
                        Toggle::make('is_stock_managed')
                            ->label('Kelola Stok')
                            ->default(false),
                        FileUpload::make('image_path')
                            ->label('Foto')
                            ->disk('public')
                            ->directory('menus')
                            ->image()
                            ->columnSpanFull(),
                        Textarea::make('description')
                            ->label('Deskripsi')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
