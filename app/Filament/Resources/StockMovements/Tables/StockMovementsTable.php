<?php

namespace App\Filament\Resources\StockMovements\Tables;

use App\Models\StockMovement;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class StockMovementsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('movement_date')
                    ->label('Tanggal')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('type')
                    ->label('Jenis')
                    ->badge()
                    ->formatStateUsing(static fn (string $state): string => match ($state) {
                        StockMovement::TYPE_IN => 'Stock In',
                        StockMovement::TYPE_OUT => 'Stock Out',
                        StockMovement::TYPE_ADJUSTMENT => 'Adjustment',
                        StockMovement::TYPE_TRANSFER => 'Transfer',
                        default => $state,
                    })
                    ->sortable(),
                TextColumn::make('fromLocation.name')
                    ->label('Lokasi Asal')
                    ->placeholder('-'),
                TextColumn::make('toLocation.name')
                    ->label('Lokasi Tujuan')
                    ->placeholder('-'),
                TextColumn::make('items_count')
                    ->label('Item')
                    ->counts('items'),
                TextColumn::make('reference_no')
                    ->label('Referensi')
                    ->toggleable(isToggledHiddenByDefault: true),
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
