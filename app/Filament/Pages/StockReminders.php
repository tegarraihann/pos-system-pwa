<?php

namespace App\Filament\Pages;

use App\Filament\Resources\Ingredients\IngredientResource;
use App\Filament\Resources\MenuVariants\MenuVariantResource;
use App\Models\Ingredient;
use App\Models\MenuVariant;
use App\Services\StockReminderService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use UnitEnum;

class StockReminders extends Page
{
    protected static string|UnitEnum|null $navigationGroup = 'Inventory management';

    protected static ?string $title = 'Reminder Stok';

    protected static ?string $slug = 'stock-reminders';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBellAlert;

    protected static bool $shouldRegisterNavigation = false;

    protected string $view = 'filament.pages.stock-reminders';

    public string $status = 'all';

    public function mount(): void
    {
        $requestedStatus = (string) request()->query('status', 'all');

        $this->status = in_array($requestedStatus, ['all', 'out', 'low'], true)
            ? $requestedStatus
            : 'all';
    }

    public static function canAccess(): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        return $user->can('ViewAny:StockMovement');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public function getTitle(): string|Htmlable
    {
        return 'Reminder Stok';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('showAll')
                ->label('Semua')
                ->color($this->status === 'all' ? 'primary' : 'gray')
                ->url(static::getUrl(['status' => 'all'])),
            Action::make('showOut')
                ->label('Habis')
                ->color($this->status === 'out' ? 'danger' : 'gray')
                ->url(static::getUrl(['status' => 'out'])),
            Action::make('showLow')
                ->label('Menipis')
                ->color($this->status === 'low' ? 'warning' : 'gray')
                ->url(static::getUrl(['status' => 'low'])),
        ];
    }

    /**
     * @return array{
     *   total_items:int,
     *   low_count:int,
     *   out_count:int,
     *   impacted_count:int,
     *   items:\Illuminate\Support\Collection<int, array{
     *     item_type:string,
     *     item_id:int,
     *     name:string,
     *     stock:float,
     *     reminder_stock:float,
     *     status:string,
     *     type_label:string,
     *     detail_url:string
     *   }>
     * }
     */
    public function getSnapshot(): array
    {
        $snapshot = app(StockReminderService::class)->getSnapshot();

        $items = $snapshot['items']
            ->when($this->status !== 'all', fn ($collection) => $collection->where('status', $this->status))
            ->values()
            ->map(function (array $item): array {
                $isIngredient = $item['item_type'] === Ingredient::class;

                return [
                    ...$item,
                    'type_label' => $isIngredient ? 'Bahan Baku' : 'Varian Menu',
                    'detail_url' => $isIngredient
                        ? IngredientResource::getUrl('view', ['record' => $item['item_id']])
                        : MenuVariantResource::getUrl('view', ['record' => $item['item_id']]),
                ];
            });

        return [
            ...$snapshot,
            'items' => $items,
        ];
    }
}
