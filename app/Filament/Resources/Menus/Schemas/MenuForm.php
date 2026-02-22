<?php

namespace App\Filament\Resources\Menus\Schemas;

use App\Models\Menu;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MenuForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Utama')
                    ->description('Data utama untuk identitas menu.')
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        TextInput::make('code')
                            ->label('Kode Menu/SKU')
                            ->required()
                            ->maxLength(50)
                            ->unique(Menu::class, 'code', ignoreRecord: true)
                            ->placeholder('Contoh: AMR-001'),
                        TextInput::make('name')
                            ->label('Nama Menu')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Contoh: Americano'),
                        TextInput::make('category')
                            ->label('Kategori')
                            ->maxLength(100)
                            ->placeholder('Makanan / Minuman'),
                        TextInput::make('unit')
                            ->label('Satuan')
                            ->maxLength(50)
                            ->placeholder('Contoh: Cup / Porsi'),
                    ]),

                Section::make('Status & Pengaturan Stok')
                    ->description('Pengaturan status aktif menu dan pemakaian stok.')
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true)
                            ->inline(false),
                        Toggle::make('is_stock_managed')
                            ->label('Kelola Stok')
                            ->default(false)
                            ->helperText('Aktifkan jika menu ini mengikuti pergerakan stok.')
                            ->inline(false),
                    ]),

                Section::make('Media & Deskripsi')
                    ->description('Tambahan foto dan keterangan menu.')
                    ->columnSpanFull()
                    ->columns(1)
                    ->schema([
                        FileUpload::make('image_path')
                            ->label('Foto')
                            ->disk('public')
                            ->directory('menus')
                            ->image()
                            ->imageEditor()
                            ->helperText('Opsional. Format gambar menu untuk katalog.')
                            ->columnSpanFull(),
                        Textarea::make('description')
                            ->label('Deskripsi')
                            ->rows(4)
                            ->placeholder('Tulis deskripsi singkat menu...')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
