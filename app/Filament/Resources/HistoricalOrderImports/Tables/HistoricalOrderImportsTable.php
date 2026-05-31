<?php

namespace App\Filament\Resources\HistoricalOrderImports\Tables;

use App\Models\HistoricalOrderImport;
use App\Services\HistoricalOrderMigrationService;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class HistoricalOrderImportsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('ordered_at', 'desc')
            ->recordUrl(null)
            ->columns([
                TextColumn::make('source_order_number')
                    ->label('Nomor Sumber')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('ordered_at')
                    ->label('Waktu Order')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('payment_method_mapped')
                    ->label('Metode Bayar')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'cash' => 'Cash',
                        'bank_transfer_qris' => 'Bank Transfer / QRIS',
                        default => $state ?: '-',
                    }),
                TextColumn::make('items_count')
                    ->label('Item')
                    ->counts('items')
                    ->badge()
                    ->color('gray'),
                TextColumn::make('total_amount')
                    ->label('Total PDF')
                    ->money('IDR', locale: 'id')
                    ->sortable(),
                TextColumn::make('base_mapped_total')
                    ->label('Total Master')
                    ->money('IDR', locale: 'id')
                    ->sortable(),
                TextColumn::make('price_gap')
                    ->label('Selisih')
                    ->money('IDR', locale: 'id')
                    ->sortable()
                    ->color(fn ($state): string => ((float) $state) === 0.0 ? 'success' : 'warning'),
                TextColumn::make('mapping_status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        HistoricalOrderImport::STATUS_MATCHED => 'success',
                        HistoricalOrderImport::STATUS_PARTIAL => 'warning',
                        HistoricalOrderImport::STATUS_AMBIGUOUS => 'info',
                        HistoricalOrderImport::STATUS_UNMATCHED => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => HistoricalOrderImport::statusOptions()[$state] ?? $state),
                TextColumn::make('ready_for_migration')
                    ->label('Siap Migrasi')
                    ->badge()
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Siap' : 'Belum')
                    ->color(fn (bool $state): string => $state ? 'success' : 'gray'),
                TextColumn::make('migrated_at')
                    ->label('Migrated')
                    ->dateTime()
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('raw_products')
                    ->label('Produk Raw')
                    ->limit(60)
                    ->tooltip(fn (HistoricalOrderImport $record): string => $record->raw_products),
            ])
            ->filters([
                SelectFilter::make('mapping_status')
                    ->label('Status Mapping')
                    ->options(HistoricalOrderImport::statusOptions()),
                SelectFilter::make('payment_method_mapped')
                    ->label('Metode Bayar')
                    ->options([
                        'cash' => 'Cash',
                        'bank_transfer_qris' => 'Bank Transfer / QRIS',
                    ]),
            ])
            ->recordActions([
                Action::make('migrateRecord')
                    ->label('Migrasikan')
                    ->icon('heroicon-o-arrow-right-circle')
                    ->color('success')
                    ->visible(fn (HistoricalOrderImport $record): bool => $record->ready_for_migration && blank($record->migrated_order_id))
                    ->requiresConfirmation()
                    ->successNotificationTitle('Histori berhasil dimigrasikan ke order final')
                    ->action(function (HistoricalOrderImport $record): void {
                        app(HistoricalOrderMigrationService::class)->migrate($record, auth()->id());
                    }),
                ViewAction::make()
                    ->label('Review'),
            ]);
    }
}
