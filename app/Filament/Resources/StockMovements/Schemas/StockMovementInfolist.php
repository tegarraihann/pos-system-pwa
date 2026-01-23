<?php

namespace App\Filament\Resources\StockMovements\Schemas;

use App\Models\StockMovement;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\RepeatableEntry\TableColumn;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class StockMovementInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Pergerakan Stok')
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        TextEntry::make('type')
                            ->label('Jenis')
                            ->formatStateUsing(static fn (string $state): string => match ($state) {
                                StockMovement::TYPE_IN => 'Stock In',
                                StockMovement::TYPE_OUT => 'Stock Out',
                                StockMovement::TYPE_ADJUSTMENT => 'Adjustment',
                                StockMovement::TYPE_TRANSFER => 'Transfer',
                                default => $state,
                            }),
                        TextEntry::make('movement_date')
                            ->label('Tanggal')
                            ->dateTime(),
                        TextEntry::make('fromLocation.name')
                            ->label('Lokasi Asal')
                            ->placeholder('-'),
                        TextEntry::make('toLocation.name')
                            ->label('Lokasi Tujuan')
                            ->placeholder('-'),
                        TextEntry::make('adjustment_type')
                            ->label('Tipe Adjustment')
                            ->formatStateUsing(static fn (?string $state): string => match ($state) {
                                StockMovement::ADJUSTMENT_INCREASE => 'Penambahan',
                                StockMovement::ADJUSTMENT_DECREASE => 'Pengurangan',
                                default => '-',
                            }),
                        TextEntry::make('reference_no')
                            ->label('Referensi')
                            ->placeholder('-'),
                        TextEntry::make('notes')
                            ->label('Catatan')
                            ->placeholder('-')
                            ->columnSpanFull(),
                    ]),
                Section::make('Item')
                    ->schema([
                        RepeatableEntry::make('items')
                            ->table([
                                TableColumn::make('Item'),
                                TableColumn::make('Qty'),
                                TableColumn::make('Satuan'),
                            ])
                            ->schema([
                                TextEntry::make('item_label')
                                    ->label('Item'),
                                TextEntry::make('qty')
                                    ->label('Qty'),
                                TextEntry::make('unit')
                                    ->label('Satuan')
                                    ->placeholder('-'),
                            ])
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
