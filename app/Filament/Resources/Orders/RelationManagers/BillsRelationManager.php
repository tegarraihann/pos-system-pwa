<?php

namespace App\Filament\Resources\Orders\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BillsRelationManager extends RelationManager
{
    protected static string $relationship = 'bills';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Bill')
                    ->columns(2)
                    ->schema([
                        TextInput::make('bill_no')
                            ->label('Bill No')
                            ->required()
                            ->maxLength(20),
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'unpaid' => 'Unpaid',
                                'partial' => 'Partial',
                                'paid' => 'Paid',
                            ])
                            ->required(),
                        TextInput::make('subtotal')
                            ->label('Subtotal')
                            ->prefix('Rp')
                            ->numeric()
                            ->minValue(0),
                        TextInput::make('discount_total')
                            ->label('Diskon')
                            ->prefix('Rp')
                            ->numeric()
                            ->minValue(0),
                        TextInput::make('tax_total')
                            ->label('Pajak')
                            ->prefix('Rp')
                            ->numeric()
                            ->minValue(0),
                        TextInput::make('service_total')
                            ->label('Service')
                            ->prefix('Rp')
                            ->numeric()
                            ->minValue(0),
                        TextInput::make('grand_total')
                            ->label('Grand Total')
                            ->prefix('Rp')
                            ->numeric()
                            ->minValue(0),
                        TextInput::make('paid_total')
                            ->label('Dibayar')
                            ->prefix('Rp')
                            ->numeric()
                            ->minValue(0),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('bill_no')
                    ->label('Bill')
                    ->searchable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge(),
                TextColumn::make('grand_total')
                    ->label('Grand Total')
                    ->money('IDR', locale: 'id'),
                TextColumn::make('paid_total')
                    ->label('Dibayar')
                    ->money('IDR', locale: 'id'),
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
