<?php

namespace App\Filament\Resources\Orders\RelationManagers;

use App\Models\MenuVariant;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class OrderItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Item Order')
                    ->columns(2)
                    ->schema([
                        Select::make('menu_variant_id')
                            ->label('Varian Menu')
                            ->options(fn (): array => MenuVariant::query()
                                ->with('menu')
                                ->orderBy('kd_varian')
                                ->get()
                                ->mapWithKeys(fn (MenuVariant $variant) => [
                                    $variant->id => (($variant->menu?->name ?? '-') . ' - ' . $variant->kd_varian),
                                ])
                                ->all())
                            ->searchable()
                            ->required()
                            ->live()
                            ->afterStateUpdated(static function ($state, Set $set): void {
                                if (! $state) {
                                    $set('price', null);
                                    $set('item_name_snapshot', null);

                                    return;
                                }

                                $variant = MenuVariant::query()->with('menu')->find($state);
                                if (! $variant) {
                                    return;
                                }

                                $menuName = $variant->menu?->name ?? '-';
                                $set('price', $variant->price);
                                $set('item_name_snapshot', "{$menuName} - {$variant->kd_varian}");
                            }),
                        TextInput::make('item_name_snapshot')
                            ->label('Nama Item')
                            ->disabled()
                            ->dehydrated(true),
                        TextInput::make('qty')
                            ->label('Qty')
                            ->numeric()
                            ->minValue(0.001)
                            ->required(),
                        TextInput::make('price')
                            ->label('Harga')
                            ->prefix('Rp')
                            ->numeric()
                            ->minValue(0)
                            ->required(),
                        TextInput::make('discount_amount')
                            ->label('Diskon')
                            ->prefix('Rp')
                            ->numeric()
                            ->minValue(0)
                            ->default(0),
                        TextInput::make('total')
                            ->label('Total')
                            ->prefix('Rp')
                            ->disabled()
                            ->dehydrated(false)
                            ->helperText('Total dihitung otomatis.'),
                        Textarea::make('notes')
                            ->label('Catatan')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('item_name_snapshot')
                    ->label('Item')
                    ->searchable(),
                TextColumn::make('qty')
                    ->label('Qty')
                    ->sortable(),
                TextColumn::make('price')
                    ->label('Harga')
                    ->money('IDR', locale: 'id'),
                TextColumn::make('discount_amount')
                    ->label('Diskon')
                    ->money('IDR', locale: 'id'),
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
