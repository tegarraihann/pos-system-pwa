<?php

namespace App\Filament\Resources\OperatingExpenses;

use App\Filament\Resources\BaseResource;
use App\Filament\Resources\OperatingExpenses\Pages\CreateOperatingExpense;
use App\Filament\Resources\OperatingExpenses\Pages\EditOperatingExpense;
use App\Filament\Resources\OperatingExpenses\Pages\ListOperatingExpenses;
use App\Models\ChartOfAccount;
use App\Models\OperatingExpense;
use BackedEnum;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class OperatingExpenseResource extends BaseResource
{
    protected static ?string $model = OperatingExpense::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static string|UnitEnum|null $navigationGroup = 'Financial management';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'title';

    public static function getNavigationLabel(): string
    {
        return 'Beban Operasional';
    }

    public static function getModelLabel(): string
    {
        return 'Beban Operasional';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Beban Operasional';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Informasi Beban')
                ->description('Catat pengeluaran operasional di akun yang sesuai agar masuk ke laporan laba rugi.')
                ->columnSpanFull()
                ->columns(2)
                ->schema([
                    DateTimePicker::make('expense_date')
                        ->label('Tanggal Beban')
                        ->default(now())
                        ->seconds(false)
                        ->required(),
                    Select::make('chart_of_account_id')
                        ->label('Akun Beban')
                        ->options(fn (): array => ChartOfAccount::query()
                            ->where('category', ChartOfAccount::CATEGORY_EXPENSE)
                            ->where('is_active', true)
                            ->orderBy('code')
                            ->get()
                            ->mapWithKeys(fn (ChartOfAccount $account): array => [
                                $account->id => "{$account->code} - {$account->name}",
                            ])
                            ->all())
                        ->searchable()
                        ->required(),
                    TextInput::make('title')
                        ->label('Judul Beban')
                        ->required()
                        ->maxLength(150),
                    TextInput::make('amount')
                        ->label('Nominal')
                        ->prefix('Rp')
                        ->numeric()
                        ->minValue(0)
                        ->required(),
                    Select::make('payment_method')
                        ->label('Metode Pembayaran')
                        ->options(OperatingExpense::paymentMethodOptions()),
                    TextInput::make('reference_number')
                        ->label('Nomor Referensi')
                        ->maxLength(100)
                        ->placeholder('Contoh: INV-001'),
                    Textarea::make('description')
                        ->label('Deskripsi')
                        ->rows(3)
                        ->columnSpanFull(),
                    Textarea::make('notes')
                        ->label('Catatan')
                        ->rows(3)
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('expense_date', 'desc')
            ->columns([
                TextColumn::make('expense_date')
                    ->label('Tanggal')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('account.code')
                    ->label('Kode Akun')
                    ->sortable(),
                TextColumn::make('account.name')
                    ->label('Nama Akun')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('title')
                    ->label('Judul')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('payment_method')
                    ->label('Metode')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => $state
                        ? (OperatingExpense::paymentMethodOptions()[$state] ?? strtoupper($state))
                        : '-'),
                TextColumn::make('amount')
                    ->label('Nominal')
                    ->money('IDR', locale: 'id')
                    ->sortable(),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOperatingExpenses::route('/'),
            'create' => CreateOperatingExpense::route('/create'),
            'edit' => EditOperatingExpense::route('/{record}/edit'),
        ];
    }
}
