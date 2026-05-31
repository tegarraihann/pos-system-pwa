<?php

namespace App\Filament\Resources\HistoricalOrderImports\Schemas;

use App\Models\HistoricalOrderImport;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\RepeatableEntry\TableColumn;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class HistoricalOrderImportInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Transaksi Sumber')
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        TextEntry::make('source_order_number')
                            ->label('Nomor Sumber'),
                        TextEntry::make('outlet_name')
                            ->label('Outlet')
                            ->placeholder('-'),
                        TextEntry::make('ordered_at')
                            ->label('Waktu Order')
                            ->dateTime()
                            ->placeholder('-'),
                        TextEntry::make('paid_at')
                            ->label('Waktu Bayar')
                            ->dateTime()
                            ->placeholder('-'),
                        TextEntry::make('payment_method_raw')
                            ->label('Metode Bayar Raw')
                            ->placeholder('-'),
                        TextEntry::make('payment_channel_raw')
                            ->label('Channel Bayar')
                            ->placeholder('-'),
                        TextEntry::make('operator_raw')
                            ->label('Operator Raw')
                            ->placeholder('-')
                            ->columnSpanFull(),
                        TextEntry::make('raw_products')
                            ->label('Produk Raw')
                            ->placeholder('-')
                            ->columnSpanFull(),
                    ]),
                Section::make('Hasil Mapping')
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        TextEntry::make('mapping_status')
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
                        TextEntry::make('ready_for_migration')
                            ->label('Siap Migrasi')
                            ->badge()
                            ->formatStateUsing(fn (bool $state): string => $state ? 'Siap' : 'Belum')
                            ->color(fn (bool $state): string => $state ? 'success' : 'gray'),
                        TextEntry::make('normalized_products')
                            ->label('Produk Ternormalisasi')
                            ->state(fn (HistoricalOrderImport $record): string => implode(', ', $record->normalized_products ?? []))
                            ->placeholder('-'),
                        TextEntry::make('total_amount')
                            ->label('Total PDF')
                            ->money('IDR', locale: 'id'),
                        TextEntry::make('base_mapped_total')
                            ->label('Total Harga Master')
                            ->money('IDR', locale: 'id'),
                        TextEntry::make('price_gap')
                            ->label('Selisih')
                            ->money('IDR', locale: 'id'),
                        TextEntry::make('notes')
                            ->label('Catatan Mapping')
                            ->placeholder('-')
                            ->columnSpanFull(),
                        TextEntry::make('review_notes')
                            ->label('Catatan Review')
                            ->placeholder('-')
                            ->columnSpanFull(),
                        TextEntry::make('reviewer.name')
                            ->label('Direview Oleh')
                            ->placeholder('-'),
                        TextEntry::make('reviewed_at')
                            ->label('Waktu Review')
                            ->dateTime()
                            ->placeholder('-'),
                        TextEntry::make('migratedOrder.order_number')
                            ->label('Order Final')
                            ->placeholder('-'),
                        TextEntry::make('migrated_at')
                            ->label('Waktu Migrasi')
                            ->dateTime()
                            ->placeholder('-'),
                        TextEntry::make('migration_notes')
                            ->label('Catatan Migrasi')
                            ->placeholder('-')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
