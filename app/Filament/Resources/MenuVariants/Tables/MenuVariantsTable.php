<?php

namespace App\Filament\Resources\MenuVariants\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MenuVariantsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('menu.name')
                    ->label('Menu')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('kd_varian')
                    ->label('Kode Varian')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('size_varian')
                    ->label('Size')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('temperature')
                    ->label('Temperature')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('sugar_level')
                    ->label('Sugar Level')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('ice_level')
                    ->label('Ice Level')
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
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
