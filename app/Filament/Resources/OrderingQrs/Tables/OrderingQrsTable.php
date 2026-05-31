<?php

namespace App\Filament\Resources\OrderingQrs\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class OrderingQrsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('name')
                    ->label('Nama QR')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('table_number')
                    ->label('Meja')
                    ->searchable(),
                TextColumn::make('stockLocation.name')
                    ->label('Lokasi')
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
                TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable()
                    ->copyable(),
                TextColumn::make('updated_at')
                    ->label('Terakhir Diubah')
                    ->since()
                    ->sortable(),
            ])
            ->recordActions([
                Action::make('openPublicUrl')
                    ->label('Buka Publik')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn ($record): string => $record->publicUrl())
                    ->openUrlInNewTab(),
                Action::make('toggleActive')
                    ->label(fn ($record): string => $record->is_active ? 'Nonaktifkan' : 'Aktifkan')
                    ->icon('heroicon-o-power')
                    ->color(fn ($record): string => $record->is_active ? 'gray' : 'success')
                    ->requiresConfirmation()
                    ->authorize(fn (): bool => auth()->user()?->can('Toggle:OrderingQr') ?? false)
                    ->action(fn ($record) => $record->update(['is_active' => ! $record->is_active])),
                ViewAction::make()
                    ->label('Detail'),
                EditAction::make()
                    ->label('Ubah'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
