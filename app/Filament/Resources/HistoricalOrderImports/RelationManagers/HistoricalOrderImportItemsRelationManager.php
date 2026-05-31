<?php

namespace App\Filament\Resources\HistoricalOrderImports\RelationManagers;

use App\Models\HistoricalOrderImport;
use App\Models\MenuVariant;
use App\Services\HistoricalOrderImportReviewService;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class HistoricalOrderImportItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    protected static ?string $title = 'Item Review';

    protected function getReviewFormComponents(): array
    {
        return [
            Section::make('Review Item Histori')
                ->columnSpanFull()
                ->columns(2)
                ->schema([
                    Select::make('menu_variant_id')
                        ->label('Master Menu')
                        ->options(fn (): array => MenuVariant::query()
                            ->with('menu')
                            ->orderBy('kd_varian')
                            ->get()
                            ->mapWithKeys(fn (MenuVariant $variant) => [
                                $variant->id => (($variant->menu?->name ?? '-') . ' [' . $variant->kd_varian . ']'),
                            ])
                            ->all())
                        ->searchable()
                        ->preload(),
                    TextInput::make('normalized_item_name')
                        ->label('Nama Ternormalisasi')
                        ->disabled()
                        ->dehydrated(false),
                    TextInput::make('listed_qty')
                        ->label('Qty Tertulis')
                        ->disabled()
                        ->dehydrated(false),
                    TextInput::make('inferred_qty')
                        ->label('Qty Inferensi')
                        ->numeric()
                        ->minValue(0.001)
                        ->required(),
                    TextInput::make('unit_price')
                        ->label('Harga Master')
                        ->prefix('Rp')
                        ->disabled()
                        ->dehydrated(false),
                    TextInput::make('line_total_inferred')
                        ->label('Total Inferensi')
                        ->prefix('Rp')
                        ->disabled()
                        ->dehydrated(false),
                    Textarea::make('notes')
                        ->label('Catatan Review Item')
                        ->rows(3)
                        ->columnSpanFull(),
                ]),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('raw_item_name')
                    ->label('Raw Item')
                    ->searchable(),
                TextColumn::make('menuVariant.menu.name')
                    ->label('Master Menu')
                    ->placeholder('-')
                    ->searchable(),
                TextColumn::make('listed_qty')
                    ->label('Qty Tertulis'),
                TextColumn::make('inferred_qty')
                    ->label('Qty Inferensi'),
                TextColumn::make('unit_price')
                    ->label('Harga Master')
                    ->money('IDR', locale: 'id')
                    ->placeholder('-'),
                TextColumn::make('mapping_status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => HistoricalOrderImport::statusOptions()[$state] ?? ucfirst($state)),
            ])
            ->actions([
                Action::make('reviewItem')
                    ->label('Review Item')
                    ->icon('heroicon-o-pencil-square')
                    ->fillForm(fn ($record): array => [
                        'menu_variant_id' => $record->menu_variant_id,
                        'normalized_item_name' => $record->normalized_item_name,
                        'listed_qty' => $record->listed_qty,
                        'inferred_qty' => $record->inferred_qty,
                        'unit_price' => $record->unit_price,
                        'line_total_inferred' => $record->line_total_inferred,
                        'notes' => $record->notes,
                    ])
                    ->form($this->getReviewFormComponents())
                    ->action(function ($record, array $data): void {
                        $variant = filled($data['menu_variant_id'])
                            ? MenuVariant::query()->find($data['menu_variant_id'])
                            : null;

                        $record->update([
                            'menu_variant_id' => $variant?->id,
                            'inferred_qty' => $data['inferred_qty'],
                            'unit_price' => $variant?->price,
                            'notes' => $data['notes'],
                            'mapping_status' => $variant ? HistoricalOrderImport::STATUS_MATCHED : HistoricalOrderImport::STATUS_UNMATCHED,
                        ]);

                        app(HistoricalOrderImportReviewService::class)
                            ->refreshImport($this->getOwnerRecord()->refresh());
                    }),
            ]);
    }
}
