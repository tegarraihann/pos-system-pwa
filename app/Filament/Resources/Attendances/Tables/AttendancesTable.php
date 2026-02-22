<?php

namespace App\Filament\Resources\Attendances\Tables;

use App\Models\Attendance;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AttendancesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('check_in_at', 'desc')
            ->columns([
                TextColumn::make('shift_date')
                    ->label('Tanggal Shift')
                    ->date()
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('Kasir')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status')
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
                TextColumn::make('check_in_at')
                    ->label('Check-in')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('check_out_at')
                    ->label('Check-out')
                    ->dateTime()
                    ->placeholder('-')
                    ->sortable(),
                TextColumn::make('work_minutes')
                    ->label('Durasi')
                    ->formatStateUsing(fn (?int $state): string => (string) ($state ?? 0) . ' menit')
                    ->sortable(),
                TextColumn::make('device_id')
                    ->label('Device')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        Attendance::STATUS_CHECKED_IN => 'Checked In',
                        Attendance::STATUS_CHECKED_OUT => 'Checked Out',
                    ]),
                SelectFilter::make('user_id')
                    ->label('Kasir')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
                Filter::make('date_range')
                    ->label('Rentang Tanggal')
                    ->form([
                        DatePicker::make('date_from')
                            ->label('Dari Tanggal')
                            ->native(false),
                        DatePicker::make('date_until')
                            ->label('Sampai Tanggal')
                            ->native(false),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['date_from'] ?? null,
                                fn (Builder $query, string $date): Builder => $query->whereDate('shift_date', '>=', $date)
                            )
                            ->when(
                                $data['date_until'] ?? null,
                                fn (Builder $query, string $date): Builder => $query->whereDate('shift_date', '<=', $date)
                            );
                    }),
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

