<?php

namespace App\Filament\Resources\Bills\RelationManagers;

use App\Models\OrderItem;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BillItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Item Bill')
                    ->columns(2)
                    ->schema([
                        Select::make('order_item_id')
                            ->label('Item Order')
                            ->options(function (): array {
                                $orderId = $this->getOwnerRecord()->order_id;

                                return OrderItem::query()
                                    ->where('order_id', $orderId)
                                    ->orderBy('id')
                                    ->pluck('item_name_snapshot', 'id')
                                    ->all();
                            })
                            ->searchable()
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, Set $set): void {
                                if (! $state) {
                                    $set('qty', null);
                                    $set('total', null);

                                    return;
                                }

                                $item = OrderItem::query()->find($state);
                                if (! $item) {
                                    return;
                                }

                                $set('qty', $item->qty);
                                $set('total', $item->total);
                            }),
                        TextInput::make('qty')
                            ->label('Qty')
                            ->numeric()
                            ->minValue(0.001)
                            ->required(),
                        TextInput::make('total')
                            ->label('Total')
                            ->prefix('Rp')
                            ->numeric()
                            ->minValue(0)
                            ->required(),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('orderItem.item_name_snapshot')
                    ->label('Item')
                    ->searchable(),
                TextColumn::make('qty')
                    ->label('Qty'),
                TextColumn::make('total')
                    ->label('Total')
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
