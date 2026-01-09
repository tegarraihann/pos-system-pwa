<?php

namespace App\Filament\Resources\Recipes\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RecipesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('menuVariant.menu.name')
                    ->label('Menu')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('menuVariant.kd_varian')
                    ->label('Kode Varian')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('prep_time_minutes')
                    ->label('Waktu (menit)')
                    ->sortable(),
                TextColumn::make('items_count')
                    ->label('Bahan')
                    ->counts('items')
                    ->sortable(),
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
