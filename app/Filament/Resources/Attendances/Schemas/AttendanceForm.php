<?php

namespace App\Filament\Resources\Attendances\Schemas;

use App\Models\Attendance;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AttendanceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Data Absensi')
                    ->description('Semua field ditampilkan dalam satu kolom agar input lebih fokus.')
                    ->columnSpanFull()
                    ->columns(1)
                    ->schema([
                        Select::make('user_id')
                            ->label('Kasir')
                            ->relationship('user', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->columnSpanFull(),
                        DatePicker::make('shift_date')
                            ->label('Tanggal Shift')
                            ->required()
                            ->native(false)
                            ->columnSpanFull(),
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                Attendance::STATUS_CHECKED_IN => 'Checked In',
                                Attendance::STATUS_CHECKED_OUT => 'Checked Out',
                            ])
                            ->required()
                            ->native(false)
                            ->columnSpanFull(),
                        DateTimePicker::make('check_in_at')
                            ->label('Waktu Check-in')
                            ->required()
                            ->seconds(false)
                            ->native(false)
                            ->columnSpanFull(),
                        TextInput::make('check_in_lat')
                            ->label('Latitude Check-in')
                            ->numeric()
                            ->step('0.0000001')
                            ->columnSpanFull(),
                        TextInput::make('check_in_lng')
                            ->label('Longitude Check-in')
                            ->numeric()
                            ->step('0.0000001')
                            ->columnSpanFull(),
                        DateTimePicker::make('check_out_at')
                            ->label('Waktu Check-out')
                            ->seconds(false)
                            ->native(false)
                            ->columnSpanFull(),
                        TextInput::make('check_out_lat')
                            ->label('Latitude Check-out')
                            ->numeric()
                            ->step('0.0000001')
                            ->columnSpanFull(),
                        TextInput::make('check_out_lng')
                            ->label('Longitude Check-out')
                            ->numeric()
                            ->step('0.0000001')
                            ->columnSpanFull(),
                        TextInput::make('device_id')
                            ->label('Device ID')
                            ->maxLength(120)
                            ->columnSpanFull(),
                        TextInput::make('work_minutes')
                            ->label('Total Menit Kerja')
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}

