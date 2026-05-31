<?php

namespace App\Filament\Resources\OrderingQrs;

use App\Filament\Resources\BaseResource;
use App\Filament\Resources\OrderingQrs\Pages\CreateOrderingQr;
use App\Filament\Resources\OrderingQrs\Pages\EditOrderingQr;
use App\Filament\Resources\OrderingQrs\Pages\ListOrderingQrs;
use App\Filament\Resources\OrderingQrs\Pages\ViewOrderingQr;
use App\Filament\Resources\OrderingQrs\Schemas\OrderingQrForm;
use App\Filament\Resources\OrderingQrs\Schemas\OrderingQrInfolist;
use App\Filament\Resources\OrderingQrs\Tables\OrderingQrsTable;
use App\Models\OrderingQr;
use BackedEnum;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class OrderingQrResource extends BaseResource
{
    protected static ?string $model = OrderingQr::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedQrCode;

    protected static string|UnitEnum|null $navigationGroup = 'POS management';

    protected static ?int $navigationSort = 6;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getNavigationLabel(): string
    {
        return 'QR Pemesanan';
    }

    public static function getModelLabel(): string
    {
        return 'QR Pemesanan';
    }

    public static function getPluralModelLabel(): string
    {
        return 'QR Pemesanan';
    }

    public static function form(Schema $schema): Schema
    {
        return OrderingQrForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return OrderingQrInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OrderingQrsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOrderingQrs::route('/'),
            'create' => CreateOrderingQr::route('/create'),
            'view' => ViewOrderingQr::route('/{record}'),
            'edit' => EditOrderingQr::route('/{record}/edit'),
        ];
    }
}
