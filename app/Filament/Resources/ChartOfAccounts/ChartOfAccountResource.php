<?php

namespace App\Filament\Resources\ChartOfAccounts;

use App\Filament\Resources\BaseResource;
use App\Filament\Resources\ChartOfAccounts\Pages\CreateChartOfAccount;
use App\Filament\Resources\ChartOfAccounts\Pages\EditChartOfAccount;
use App\Filament\Resources\ChartOfAccounts\Pages\ListChartOfAccounts;
use App\Models\ChartOfAccount;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class ChartOfAccountResource extends BaseResource
{
    protected static ?string $model = ChartOfAccount::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBookOpen;

    protected static string|UnitEnum|null $navigationGroup = 'Financial management';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getNavigationLabel(): string
    {
        return 'Master Akun';
    }

    public static function getModelLabel(): string
    {
        return 'Akun';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Master Akun';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Informasi Akun')
                ->description('Gunakan istilah akun yang konsisten dengan kebutuhan laporan keuangan.')
                ->columnSpanFull()
                ->columns(2)
                ->schema([
                    TextInput::make('code')
                        ->label('Kode Akun')
                        ->required()
                        ->maxLength(20)
                        ->unique(ignoreRecord: true),
                    TextInput::make('name')
                        ->label('Nama Akun')
                        ->required()
                        ->maxLength(150),
                    Select::make('category')
                        ->label('Kategori Akun')
                        ->options(ChartOfAccount::categoryOptions())
                        ->required(),
                    Select::make('normal_balance')
                        ->label('Saldo Normal')
                        ->options(ChartOfAccount::normalBalanceOptions())
                        ->required(),
                    Toggle::make('is_active')
                        ->label('Aktif')
                        ->default(true),
                    Toggle::make('is_system')
                        ->label('Akun Sistem')
                        ->default(false),
                    Textarea::make('description')
                        ->label('Deskripsi')
                        ->rows(3)
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('code')
            ->columns([
                TextColumn::make('code')
                    ->label('Kode')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('name')
                    ->label('Nama Akun')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('category')
                    ->label('Kategori')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => ChartOfAccount::categoryOptions()[$state] ?? $state),
                TextColumn::make('normal_balance')
                    ->label('Saldo Normal')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => ChartOfAccount::normalBalanceOptions()[$state] ?? $state),
                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
                IconColumn::make('is_system')
                    ->label('Sistem')
                    ->boolean(),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListChartOfAccounts::route('/'),
            'create' => CreateChartOfAccount::route('/create'),
            'edit' => EditChartOfAccount::route('/{record}/edit'),
        ];
    }
}
