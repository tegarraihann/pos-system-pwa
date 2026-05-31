<?php

namespace App\Filament\Resources\HistoricalOrderImports;

use App\Filament\Resources\BaseResource;
use App\Filament\Resources\HistoricalOrderImports\Pages\ListHistoricalOrderImports;
use App\Filament\Resources\HistoricalOrderImports\Pages\ViewHistoricalOrderImport;
use App\Filament\Resources\HistoricalOrderImports\RelationManagers\HistoricalOrderImportItemsRelationManager;
use App\Filament\Resources\HistoricalOrderImports\Schemas\HistoricalOrderImportInfolist;
use App\Filament\Resources\HistoricalOrderImports\Tables\HistoricalOrderImportsTable;
use App\Models\HistoricalOrderImport;
use BackedEnum;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class HistoricalOrderImportResource extends BaseResource
{
    protected static ?string $model = HistoricalOrderImport::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArchiveBox;

    protected static string|UnitEnum|null $navigationGroup = 'Data migration';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'source_order_number';

    public static function getNavigationLabel(): string
    {
        return 'Review Histori';
    }

    public static function getModelLabel(): string
    {
        return 'Histori Impor';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Review Histori';
    }

    public static function infolist(Schema $schema): Schema
    {
        return HistoricalOrderImportInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return HistoricalOrderImportsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListHistoricalOrderImports::route('/'),
            'view' => ViewHistoricalOrderImport::route('/{record}'),
        ];
    }

    public static function getRelations(): array
    {
        return [
            HistoricalOrderImportItemsRelationManager::class,
        ];
    }
}
