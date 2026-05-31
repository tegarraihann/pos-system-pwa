<?php

namespace App\Filament\Resources\CashierSessions;

use App\Filament\Resources\BaseResource;
use App\Filament\Resources\CashierSessions\Pages\ListCashierSessions;
use App\Filament\Resources\CashierSessions\Pages\ViewCashierSession;
use App\Filament\Resources\CashierSessions\Schemas\CashierSessionInfolist;
use App\Filament\Resources\CashierSessions\Tables\CashierSessionsTable;
use App\Models\CashierSession;
use BackedEnum;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class CashierSessionResource extends BaseResource
{
    protected static ?string $model = CashierSession::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static string|UnitEnum|null $navigationGroup = 'POS management';

    protected static ?int $navigationSort = 5;

    protected static ?string $recordTitleAttribute = 'id';

    public static function getNavigationLabel(): string
    {
        return 'Sesi Kasir';
    }

    public static function getModelLabel(): string
    {
        return 'Sesi Kasir';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Sesi Kasir';
    }

    public static function infolist(Schema $schema): Schema
    {
        return CashierSessionInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CashierSessionsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCashierSessions::route('/'),
            'view' => ViewCashierSession::route('/{record}'),
        ];
    }
}
