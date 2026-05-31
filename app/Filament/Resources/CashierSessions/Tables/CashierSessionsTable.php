<?php

namespace App\Filament\Resources\CashierSessions\Tables;

use App\Models\CashierSession;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CashierSessionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('opened_at', 'desc')
            ->columns([
                TextColumn::make('opened_at')
                    ->label('Dibuka')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('Kasir')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status')
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
                TextColumn::make('opening_cash')
                    ->label('Modal Awal')
                    ->money('IDR', locale: 'id')
                    ->sortable(),
                TextColumn::make('expected_cash')
                    ->label('Kas Sistem')
                    ->money('IDR', locale: 'id')
                    ->sortable(),
                TextColumn::make('actual_cash')
                    ->label('Kas Aktual')
                    ->money('IDR', locale: 'id')
                    ->placeholder('-')
                    ->sortable(),
                TextColumn::make('difference_amount')
                    ->label('Selisih')
                    ->money('IDR', locale: 'id')
                    ->placeholder('-')
                    ->sortable(),
                TextColumn::make('payments_count')
                    ->label('Pembayaran')
                    ->counts('payments'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        CashierSession::STATUS_OPEN => 'Open',
                        CashierSession::STATUS_CLOSED => 'Closed',
                    ]),
                SelectFilter::make('user_id')
                    ->label('Kasir')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                ViewAction::make(),
            ]);
    }
}
