<?php

namespace App\Filament\Resources\CashierSessions\Schemas;

use App\Models\CashierSession;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CashierSessionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Section::make('Ringkasan Sesi')
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        TextEntry::make('user.name')
                            ->label('Kasir')
                            ->placeholder('-'),
                        TextEntry::make('status')
                            ->label('Status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                CashierSession::STATUS_OPEN => 'warning',
                                CashierSession::STATUS_CLOSED => 'success',
                                default => 'gray',
                            })
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                CashierSession::STATUS_OPEN => 'Open',
                                CashierSession::STATUS_CLOSED => 'Closed',
                                default => $state,
                            }),
                        TextEntry::make('opened_at')
                            ->label('Waktu Buka')
                            ->dateTime(),
                        TextEntry::make('closed_at')
                            ->label('Waktu Tutup')
                            ->dateTime()
                            ->placeholder('-'),
                        TextEntry::make('opening_cash')
                            ->label('Modal Awal')
                            ->money('IDR', locale: 'id'),
                        TextEntry::make('expected_cash')
                            ->label('Kas Sistem')
                            ->money('IDR', locale: 'id'),
                        TextEntry::make('actual_cash')
                            ->label('Kas Aktual')
                            ->money('IDR', locale: 'id')
                            ->placeholder('-'),
                        TextEntry::make('difference_amount')
                            ->label('Selisih')
                            ->money('IDR', locale: 'id')
                            ->placeholder('-'),
                        TextEntry::make('device_id')
                            ->label('Device ID')
                            ->placeholder('-')
                            ->copyable()
                            ->columnSpanFull(),
                    ]),
                Section::make('Catatan')
                    ->columnSpanFull()
                    ->columns(1)
                    ->schema([
                        TextEntry::make('opening_notes')
                            ->label('Catatan Buka')
                            ->placeholder('-'),
                        TextEntry::make('closing_notes')
                            ->label('Catatan Tutup')
                            ->placeholder('-'),
                    ]),
                Section::make('Metadata')
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        TextEntry::make('attendance.shift_date')
                            ->label('Shift Absensi')
                            ->date()
                            ->placeholder('-'),
                        TextEntry::make('closedBy.name')
                            ->label('Ditutup Oleh')
                            ->placeholder('-'),
                    ]),
            ]);
    }
}
