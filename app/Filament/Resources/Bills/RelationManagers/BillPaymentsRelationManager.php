<?php

namespace App\Filament\Resources\Bills\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BillPaymentsRelationManager extends RelationManager
{
    protected static string $relationship = 'payments';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Payment Bill')
                    ->columns(2)
                    ->schema([
                        Select::make('method')
                            ->label('Metode')
                            ->options([
                                'cash' => 'Cash',
                                'card' => 'Card',
                                'ewallet' => 'E-Wallet',
                                'gateway' => 'Gateway',
                            ])
                            ->required(),
                        TextInput::make('amount')
                            ->label('Nominal')
                            ->prefix('Rp')
                            ->numeric()
                            ->minValue(0)
                            ->required(),
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'pending' => 'Pending',
                                'paid' => 'Paid',
                                'failed' => 'Failed',
                                'refunded' => 'Refunded',
                            ])
                            ->required(),
                        TextInput::make('gateway_provider')
                            ->label('Gateway')
                            ->maxLength(30),
                        TextInput::make('gateway_ref')
                            ->label('Ref Gateway')
                            ->maxLength(100),
                        DateTimePicker::make('paid_at')
                            ->label('Waktu Bayar'),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('method')
                    ->label('Metode'),
                TextColumn::make('amount')
                    ->label('Nominal')
                    ->money('IDR', locale: 'id'),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge(),
                TextColumn::make('paid_at')
                    ->label('Waktu Bayar')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ]);
    }
}
