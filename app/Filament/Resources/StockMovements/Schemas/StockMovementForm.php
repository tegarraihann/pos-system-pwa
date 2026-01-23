<?php

namespace App\Filament\Resources\StockMovements\Schemas;

use App\Models\Ingredient;
use App\Models\MenuVariant;
use App\Models\StockMovement;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class StockMovementForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Pergerakan Stok')
                    ->columns(1)
                    ->schema([
                        Select::make('type')
                            ->label('Jenis Pergerakan')
                            ->options([
                                StockMovement::TYPE_IN => 'Stock In',
                                StockMovement::TYPE_OUT => 'Stock Out',
                                StockMovement::TYPE_ADJUSTMENT => 'Adjustment',
                                StockMovement::TYPE_TRANSFER => 'Transfer',
                            ])
                            ->required()
                            ->live()
                            ->columnSpanFull(),
                        DateTimePicker::make('movement_date')
                            ->label('Tanggal')
                            ->default(now())
                            ->required()
                            ->columnSpanFull(),
                        Select::make('from_location_id')
                            ->label(fn (Get $get): string => match ($get('type')) {
                                StockMovement::TYPE_TRANSFER => 'Lokasi Asal',
                                StockMovement::TYPE_OUT, StockMovement::TYPE_ADJUSTMENT => 'Lokasi',
                                default => 'Lokasi',
                            })
                            ->relationship(
                                name: 'fromLocation',
                                titleAttribute: 'name',
                                modifyQueryUsing: static fn ($query) => $query->where('is_active', true),
                            )
                            ->searchable()
                            ->preload()
                            ->visible(fn (Get $get): bool => in_array($get('type'), [
                                StockMovement::TYPE_OUT,
                                StockMovement::TYPE_TRANSFER,
                                StockMovement::TYPE_ADJUSTMENT,
                            ], true))
                            ->required(fn (Get $get): bool => in_array($get('type'), [
                                StockMovement::TYPE_OUT,
                                StockMovement::TYPE_TRANSFER,
                                StockMovement::TYPE_ADJUSTMENT,
                            ], true))
                            ->columnSpanFull(),
                        Select::make('to_location_id')
                            ->label(fn (Get $get): string => $get('type') === StockMovement::TYPE_TRANSFER ? 'Lokasi Tujuan' : 'Lokasi')
                            ->relationship(
                                name: 'toLocation',
                                titleAttribute: 'name',
                                modifyQueryUsing: static fn ($query) => $query->where('is_active', true),
                            )
                            ->searchable()
                            ->preload()
                            ->visible(fn (Get $get): bool => in_array($get('type'), [
                                StockMovement::TYPE_IN,
                                StockMovement::TYPE_TRANSFER,
                            ], true))
                            ->required(fn (Get $get): bool => in_array($get('type'), [
                                StockMovement::TYPE_IN,
                                StockMovement::TYPE_TRANSFER,
                            ], true))
                            ->different('from_location_id')
                            ->columnSpanFull(),
                        Select::make('adjustment_type')
                            ->label('Tipe Adjustment')
                            ->options([
                                StockMovement::ADJUSTMENT_INCREASE => 'Penambahan',
                                StockMovement::ADJUSTMENT_DECREASE => 'Pengurangan',
                            ])
                            ->visible(fn (Get $get): bool => $get('type') === StockMovement::TYPE_ADJUSTMENT)
                            ->required(fn (Get $get): bool => $get('type') === StockMovement::TYPE_ADJUSTMENT)
                            ->columnSpanFull(),
                        TextInput::make('reference_no')
                            ->label('Referensi')
                            ->maxLength(100)
                            ->columnSpanFull(),
                        Textarea::make('notes')
                            ->label('Catatan')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
                Section::make('Item')
                    ->schema([
                        Repeater::make('items')
                            ->relationship()
                            ->minItems(1)
                            ->columns(1)
                            ->schema([
                                Select::make('item_type')
                                    ->label('Tipe Item')
                                    ->options([
                                        Ingredient::class => 'Bahan Baku',
                                        MenuVariant::class => 'Varian Menu',
                                    ])
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(static function (Set $set): void {
                                        $set('item_id', null);
                                        $set('unit', null);
                                    })
                                    ->columnSpanFull(),
                                Select::make('item_id')
                                    ->label('Item')
                                    ->options(function (Get $get): array {
                                        $type = $get('item_type');

                                        if ($type === Ingredient::class) {
                                            return Ingredient::query()
                                                ->orderBy('name')
                                                ->get()
                                                ->mapWithKeys(fn (Ingredient $ingredient) => [
                                                    $ingredient->id => "{$ingredient->code} - {$ingredient->name}",
                                                ])
                                                ->all();
                                        }

                                        if ($type === MenuVariant::class) {
                                            return MenuVariant::query()
                                                ->with('menu')
                                                ->orderBy('kd_varian')
                                                ->get()
                                                ->mapWithKeys(fn (MenuVariant $variant) => [
                                                    $variant->id => (($variant->menu?->name ?? '-') . ' - ' . $variant->kd_varian),
                                                ])
                                                ->all();
                                        }

                                        return [];
                                    })
                                    ->searchable()
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(static function ($state, Get $get, Set $set): void {
                                        if (! $state) {
                                            $set('unit', null);

                                            return;
                                        }

                                        if ($get('item_type') === Ingredient::class) {
                                            $set('unit', Ingredient::query()->whereKey($state)->value('unit'));

                                            return;
                                        }

                                        if ($get('item_type') === MenuVariant::class) {
                                            $variant = MenuVariant::query()->with('menu')->find($state);
                                            $set('unit', $variant?->menu?->unit);
                                        }
                                    })
                                    ->columnSpanFull(),
                                TextInput::make('qty')
                                    ->label('Qty')
                                    ->numeric()
                                    ->minValue(0.001)
                                    ->required()
                                    ->columnSpanFull(),
                                TextInput::make('unit')
                                    ->label('Satuan')
                                    ->disabled()
                                    ->dehydrated(true)
                                    ->columnSpanFull(),
                                TextInput::make('cost')
                                    ->label('Harga')
                                    ->prefix('Rp')
                                    ->numeric()
                                    ->minValue(0)
                                    ->visible(fn (Get $get): bool => $get('item_type') === Ingredient::class)
                                    ->columnSpanFull(),
                            ]),
                    ]),
            ]);
    }
}
