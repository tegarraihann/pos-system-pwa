<?php

namespace App\Filament\Resources\PublicOrders\Tables;

use App\Models\Order;
use App\Services\OrderAccountingService;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PublicOrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->poll('10s')
            ->defaultSort('ordered_at', 'desc')
            ->recordUrl(null)
            ->columns([
                TextColumn::make('queue_number')
                    ->label('Antrian')
                    ->sortable()
                    ->badge()
                    ->color('gray'),
                TextColumn::make('order_number')
                    ->label('Nomor Order')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('ordered_at')
                    ->label('Waktu')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('table_number')
                    ->label('Meja')
                    ->searchable(),
                TextColumn::make('guest_name')
                    ->label('Pemesan')
                    ->searchable()
                    ->placeholder('-'),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        Order::STATUS_PAID => 'success',
                        Order::STATUS_PROCESSING => 'info',
                        Order::STATUS_SERVED => 'gray',
                        Order::STATUS_EXPIRED, Order::STATUS_FAILED, Order::STATUS_CANCELED => 'danger',
                        default => 'warning',
                    })
                    ->formatStateUsing(fn (string $state): string => Order::statusOptions()[$state] ?? $state),
                TextColumn::make('grand_total')
                    ->label('Grand Total')
                    ->money('IDR', locale: 'id')
                    ->sortable(),
                TextColumn::make('paid_total')
                    ->label('Terbayar')
                    ->money('IDR', locale: 'id')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(Order::statusOptions()),
            ])
            ->recordActions([
                Action::make('startProcessing')
                    ->label('Mulai Proses')
                    ->color('info')
                    ->icon('heroicon-o-play')
                    ->visible(fn ($record): bool => $record->status === Order::STATUS_PAID)
                    ->authorize(fn (): bool => auth()->user()?->can('Process:PublicOrder') ?? false)
                    ->requiresConfirmation()
                    ->successNotificationTitle('Pesanan masuk proses')
                    ->action(fn ($record): bool => $record->update(['status' => Order::STATUS_PROCESSING])),
                Action::make('markServed')
                    ->label('Tandai Selesai')
                    ->color('success')
                    ->icon('heroicon-o-check-circle')
                    ->visible(fn ($record): bool => $record->status === Order::STATUS_PROCESSING)
                    ->authorize(fn (): bool => auth()->user()?->can('Process:PublicOrder') ?? false)
                    ->requiresConfirmation()
                    ->successNotificationTitle('Pesanan ditandai selesai')
                    ->action(function ($record): bool {
                        app(OrderAccountingService::class)->markAsServed($record);

                        return true;
                    }),
                Action::make('cancelOrder')
                    ->label('Batalkan')
                    ->color('danger')
                    ->icon('heroicon-o-x-circle')
                    ->visible(fn ($record): bool => ! in_array($record->status, [
                        Order::STATUS_SERVED,
                        Order::STATUS_CANCELED,
                        Order::STATUS_EXPIRED,
                    ], true))
                    ->authorize(fn (): bool => auth()->user()?->can('Cancel:PublicOrder') ?? false)
                    ->requiresConfirmation()
                    ->successNotificationTitle('Pesanan dibatalkan')
                    ->form([
                        Textarea::make('cancel_reason')
                            ->label('Alasan Pembatalan')
                            ->required()
                            ->rows(3),
                    ])
                    ->action(fn ($record, array $data): bool => $record->update([
                        'status' => Order::STATUS_CANCELED,
                        'cancel_reason' => $data['cancel_reason'],
                        'canceled_at' => now(),
                    ])),
                ViewAction::make()
                    ->label('Detail'),
            ]);
    }
}
