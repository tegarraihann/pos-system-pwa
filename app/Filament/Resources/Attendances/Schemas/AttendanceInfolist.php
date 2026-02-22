<?php

namespace App\Filament\Resources\Attendances\Schemas;

use App\Models\Attendance;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AttendanceInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Section::make('Detail Absensi')
                    ->description('Tampilan dua kolom untuk memudahkan review data absensi.')
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
                                Attendance::STATUS_CHECKED_IN => 'warning',
                                Attendance::STATUS_CHECKED_OUT => 'success',
                                default => 'gray',
                            })
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                Attendance::STATUS_CHECKED_IN => 'Checked In',
                                Attendance::STATUS_CHECKED_OUT => 'Checked Out',
                                default => $state,
                            }),
                        TextEntry::make('shift_date')
                            ->label('Tanggal Shift')
                            ->date(),
                        TextEntry::make('work_minutes')
                            ->label('Total Menit Kerja')
                            ->formatStateUsing(fn (?int $state): string => (string) ($state ?? 0) . ' menit'),
                        TextEntry::make('check_in_at')
                            ->label('Waktu Check-in')
                            ->dateTime()
                            ->placeholder('-'),
                        TextEntry::make('check_out_at')
                            ->label('Waktu Check-out')
                            ->dateTime()
                            ->placeholder('-'),
                        TextEntry::make('check_in_lat')
                            ->label('Latitude Check-in')
                            ->placeholder('-'),
                        TextEntry::make('check_in_lng')
                            ->label('Longitude Check-in')
                            ->placeholder('-'),
                        TextEntry::make('check_out_lat')
                            ->label('Latitude Check-out')
                            ->placeholder('-'),
                        TextEntry::make('check_out_lng')
                            ->label('Longitude Check-out')
                            ->placeholder('-'),
                        TextEntry::make('device_id')
                            ->label('Device ID')
                            ->placeholder('-')
                            ->copyable()
                            ->columnSpanFull(),
                    ]),
                Section::make('Metadata')
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        TextEntry::make('created_at')
                            ->label('Dibuat pada')
                            ->dateTime(),
                        TextEntry::make('updated_at')
                            ->label('Diperbarui pada')
                            ->dateTime(),
                    ]),
            ]);
    }
}
