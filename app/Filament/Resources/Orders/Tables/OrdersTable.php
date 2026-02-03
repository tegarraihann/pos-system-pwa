<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Models\Order;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
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
                    ->formatStateUsing(static fn (string $state): string => match ($state) {
                        Order::TYPE_DINE_IN => 'Dine In',
                        Order::TYPE_TAKE_AWAY => 'Take Away',
                        Order::TYPE_DELIVERY => 'Delivery',
                        default => $state,
                    }),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(static fn (string $state): string => match ($state) {
                        Order::STATUS_DRAFT => 'Draft',
                        Order::STATUS_RECEIVED => 'Received',
                        Order::STATUS_QUEUED => 'Queued',
                        Order::STATUS_PREPARING => 'Preparing',
                        Order::STATUS_READY => 'Ready',
                        Order::STATUS_SERVED => 'Served',
                        Order::STATUS_CANCELED => 'Canceled',
                        default => $state,
                    }),
                TextColumn::make('customer.name')
                    ->label('Customer')
                    ->placeholder('-'),
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
                //
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
