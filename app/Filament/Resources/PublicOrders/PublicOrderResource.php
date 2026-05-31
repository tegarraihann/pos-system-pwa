<?php

namespace App\Filament\Resources\PublicOrders;

use App\Filament\Resources\BaseResource;
use App\Filament\Resources\Orders\Schemas\OrderInfolist;
use App\Filament\Resources\PublicOrders\Pages\ListPublicOrders;
use App\Filament\Resources\PublicOrders\Pages\ViewPublicOrder;
use App\Filament\Resources\PublicOrders\Tables\PublicOrdersTable;
use App\Models\Order;
use BackedEnum;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class PublicOrderResource extends BaseResource
{
    protected static ?string $model = Order::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDevicePhoneMobile;

    protected static string|UnitEnum|null $navigationGroup = 'POS management';

    protected static ?int $navigationSort = 7;

    protected static ?string $recordTitleAttribute = 'order_number';

    public static function getNavigationLabel(): string
    {
        return 'Antrean Pesanan';
    }

    public static function getModelLabel(): string
    {
        return 'Antrean Pesanan';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Antrean Pesanan';
    }

    public static function infolist(Schema $schema): Schema
    {
        return OrderInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PublicOrdersTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPublicOrders::route('/'),
            'view' => ViewPublicOrder::route('/{record}'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('order_source', Order::SOURCE_PUBLIC_QR);
    }

    protected static function getNavigationPermissionName(): string
    {
        return 'ViewAny:PublicOrder';
    }
}
