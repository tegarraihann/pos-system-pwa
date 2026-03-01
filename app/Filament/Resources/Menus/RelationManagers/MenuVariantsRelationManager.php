<?php

namespace App\Filament\Resources\Menus\RelationManagers;

use Filament\Tables\Table;
use App\Models\MenuVariant;
use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Toggle;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Enums\Width;

class MenuVariantsRelationManager extends RelationManager
{
    protected static string $relationship = 'variants';
    protected static ?string $title = 'Varian Menu';
    protected static ?string $modelLabel = 'Varian Menu';
    protected static ?string $pluralModelLabel = 'Varian Menu';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Data Varian')
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        TextInput::make('kd_varian')
                            ->label('Kode Varian')
                            ->required()
                            ->maxLength(50)
                            ->unique(MenuVariant::class, 'kd_varian', ignoreRecord: true),
                        TextInput::make('size_varian')
                            ->label('Ukuran Varian')
                            ->maxLength(50),
                        TextInput::make('temperature')
                            ->label('Suhu')
                            ->maxLength(50),
                        TextInput::make('sugar_level')
                            ->label('Level Gula')
                            ->maxLength(50),
                        TextInput::make('ice_level')
                            ->label('Level Es')
                            ->maxLength(50),
                        TextInput::make('price')
                            ->label('Harga')
                            ->prefix('Rp')
                            ->numeric()
                            ->minValue(0)
                            ->required(),
                        TextInput::make('stock')
                            ->label('Stok')
                            ->numeric()
                            ->minValue(0),
                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->searchPlaceholder('Cari varian...')
            ->columns([
                TextColumn::make('kd_varian')
                    ->label('Kode')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('size_varian')
                    ->label('Ukuran')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('temperature')
                    ->label('Suhu')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('sugar_level')
                    ->label('Level Gula')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('ice_level')
                    ->label('Level Es')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('price')
                    ->label('Harga')
                    ->money('IDR', locale: 'id')
                    ->sortable(),
                TextColumn::make('stock')
                    ->label('Stok')
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Tambah Varian Menu')
                    ->modalWidth(Width::FourExtraLarge)
                    ->modalSubmitActionLabel('Simpan')
                    ->modalCancelActionLabel('Batal'),
            ])
            ->actions([
                EditAction::make()
                    ->label('Ubah')
                    ->modalWidth(Width::FourExtraLarge)
                    ->modalSubmitActionLabel('Simpan perubahan')
                    ->modalCancelActionLabel('Batal'),
                DeleteAction::make()
                    ->label('Hapus'),
            ])
            ->bulkActions([
                DeleteBulkAction::make()
                    ->label('Hapus terpilih'),
            ]);
    }
}
