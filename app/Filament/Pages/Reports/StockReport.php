<?php

namespace App\Filament\Pages\Reports;

use App\Models\Ingredient;
use App\Models\MenuVariant;
use App\Models\StockLevel;
use BackedEnum;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\DB;

class StockReport extends BaseReportPage
{
    public static function getReportKey(): string
    {
        return 'stock';
    }

    protected static ?string $title = 'Laporan Stok';

    protected static ?string $navigationLabel = 'Laporan Stok';

    protected static ?int $navigationSort = 5;

    protected static ?string $slug = 'reports/stock';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArchiveBox;

    protected static ?string $reportPermission = 'ViewStockReport:Report';

    protected string $view = 'filament.pages.reports.stock-report';

    public string $stockType = 'all';

    public function usesDateFilters(): bool
    {
        return false;
    }

    /**
     * @return array<int, array{label: string, value: string, hint: string|null}>
     */
    public function getSummaryCards(): array
    {
        $lowMenuCount = (clone $this->lowMenuVariantsQuery())->count();
        $emptyMenuCount = (clone $this->emptyMenuVariantsQuery())->count();
        $lowIngredientCount = (clone $this->lowIngredientsQuery())->count();
        $emptyIngredientCount = (clone $this->emptyIngredientsQuery())->count();

        return [
            [
                'label' => 'Varian Menu Kritis',
                'value' => $this->formatNumber($lowMenuCount),
                'hint' => 'Varian stock-managed dengan stok <= reminder stock.',
            ],
            [
                'label' => 'Varian Menu Habis',
                'value' => $this->formatNumber($emptyMenuCount),
                'hint' => 'Varian stock-managed dengan stok 0 atau kurang.',
            ],
            [
                'label' => 'Bahan Baku Kritis',
                'value' => $this->formatNumber($lowIngredientCount),
                'hint' => 'Bahan baku dengan stok <= reminder stock.',
            ],
            [
                'label' => 'Bahan Baku Habis',
                'value' => $this->formatNumber($emptyIngredientCount),
                'hint' => 'Bahan baku dengan stok 0 atau kurang.',
            ],
        ];
    }

    /**
     * @return array{columns: array<int, string>, rows: array<int, array<int, string>>}
     */
    public function getMenuVariantTable(): array
    {
        $rows = $this->lowMenuVariantsQuery()
            ->with('menu')
            ->orderBy('stock')
            ->limit(25)
            ->get();

        return [
            'columns' => ['Menu', 'Varian', 'Stok', 'Reminder', 'Aktif'],
            'rows' => $rows->map(fn (MenuVariant $variant): array => [
                (string) ($variant->menu?->name ?? '-'),
                (string) $variant->kd_varian,
                $this->formatNumber($variant->stock, 0),
                $this->formatNumber($variant->reminder_stock, 0),
                $variant->is_active ? 'Aktif' : 'Nonaktif',
            ])->all(),
        ];
    }

    /**
     * @return array{columns: array<int, string>, rows: array<int, array<int, string>>}
     */
    public function getIngredientTable(): array
    {
        $rows = $this->lowIngredientsQuery()
            ->orderBy('on_hand')
            ->limit(25)
            ->get();

        return [
            'columns' => ['Bahan Baku', 'Stok', 'Unit', 'Reminder', 'Aktif'],
            'rows' => $rows->map(fn ($ingredient): array => [
                (string) $ingredient->name,
                $this->formatNumber($ingredient->on_hand, 3),
                (string) $ingredient->unit,
                $this->formatNumber($ingredient->reminder_stock, 3),
                $ingredient->is_active ? 'Aktif' : 'Nonaktif',
            ])->all(),
        ];
    }

    protected function afterResetFilters(): void
    {
        $this->stockType = 'all';
    }

    protected function getPdfData(): array
    {
        return [
            ...parent::getPdfData(),
            'summaryCards' => $this->getSummaryCards(),
            'menuTable' => $this->getMenuVariantTable(),
            'ingredientTable' => $this->getIngredientTable(),
            'notes' => 'Laporan stok menampilkan posisi stok saat export, bukan histori per tanggal.',
        ];
    }

    protected function getPdfFilterSummary(): array
    {
        return [
            'Jenis Stok' => match ($this->stockType) {
                'menu' => 'Varian Menu',
                'ingredient' => 'Bahan Baku',
                default => 'Semua',
            },
        ];
    }

    protected function getPdfView(): string
    {
        return 'pdf.reports.stock';
    }

    protected function getCustomPdfQueryParameters(): array
    {
        return [
            'stockType' => $this->stockType,
        ];
    }

    protected function applyCustomReportFilters(array $filters): void
    {
        $this->stockType = (string) ($filters['stockType'] ?? 'all');
    }

    protected function lowMenuVariantsQuery()
    {
        $query = MenuVariant::query()
            ->whereHas('menu', fn ($menuQuery) => $menuQuery->where('is_stock_managed', true))
            ->whereNotNull('reminder_stock')
            ->whereRaw('COALESCE(stock, 0) <= reminder_stock');

        if ($this->stockType === 'ingredient') {
            $query->whereRaw('1 = 0');
        }

        return $query;
    }

    protected function emptyMenuVariantsQuery()
    {
        $query = MenuVariant::query()
            ->whereHas('menu', fn ($menuQuery) => $menuQuery->where('is_stock_managed', true))
            ->whereRaw('COALESCE(stock, 0) <= 0');

        if ($this->stockType === 'ingredient') {
            $query->whereRaw('1 = 0');
        }

        return $query;
    }

    protected function lowIngredientsQuery()
    {
        $stockTotals = StockLevel::query()
            ->select('item_id', DB::raw('SUM(on_hand) as on_hand'))
            ->where('item_type', Ingredient::class)
            ->groupBy('item_id');

        $query = Ingredient::query()
            ->leftJoinSub($stockTotals, 'stock_totals', fn ($join) => $join->on('stock_totals.item_id', '=', 'ingredients.id'))
            ->select('ingredients.*')
            ->selectRaw('COALESCE(stock_totals.on_hand, 0) as on_hand')
            ->whereNotNull('ingredients.reminder_stock')
            ->whereRaw('COALESCE(stock_totals.on_hand, 0) <= ingredients.reminder_stock');

        if ($this->stockType === 'menu') {
            $query->whereRaw('1 = 0');
        }

        return $query;
    }

    protected function emptyIngredientsQuery()
    {
        $stockTotals = StockLevel::query()
            ->select('item_id', DB::raw('SUM(on_hand) as on_hand'))
            ->where('item_type', Ingredient::class)
            ->groupBy('item_id');

        $query = Ingredient::query()
            ->leftJoinSub($stockTotals, 'stock_totals', fn ($join) => $join->on('stock_totals.item_id', '=', 'ingredients.id'))
            ->whereRaw('COALESCE(stock_totals.on_hand, 0) <= 0');

        if ($this->stockType === 'menu') {
            $query->whereRaw('1 = 0');
        }

        return $query;
    }
}
