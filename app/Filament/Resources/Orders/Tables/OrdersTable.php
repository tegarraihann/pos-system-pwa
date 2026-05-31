<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Models\Order;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('ordered_at', 'desc')
            ->columns([
                TextColumn::make('order_number')
                    ->label('Nomor')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('ordered_at')
                    ->label('Waktu')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('order_type')
                    ->label('Tipe')
                    ->formatStateUsing(static fn (string $state): string => Order::typeOptions()[$state] ?? $state),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(static fn (string $state): string => Order::statusOptions()[$state] ?? $state),
                TextColumn::make('order_source')
                    ->label('Sumber')
                    ->badge()
                    ->formatStateUsing(static fn (string $state): string => Order::sourceOptions()[$state] ?? $state),
                TextColumn::make('customer.name')
                    ->label('Customer')
                    ->placeholder('-'),
                TextColumn::make('guest_name')
                    ->label('Guest')
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('stockLocation.name')
                    ->label('Lokasi')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('items_count')
                    ->label('Item')
                    ->counts('items'),
                TextColumn::make('grand_total')
                    ->label('Grand Total')
                    ->money('IDR', locale: 'id')
                    ->sortable(),
                TextColumn::make('paid_total')
                    ->label('Dibayar')
                    ->money('IDR', locale: 'id')
                    ->sortable(),
                TextColumn::make('queue_number')
                    ->label('Antrian')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(Order::statusOptions()),
                SelectFilter::make('order_source')
                    ->label('Sumber')
                    ->options(Order::sourceOptions()),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make()
                    ->visible(fn ($record): bool => $record->order_source !== Order::SOURCE_PUBLIC_QR),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
