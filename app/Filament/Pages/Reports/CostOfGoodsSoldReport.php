<?php

namespace App\Filament\Pages\Reports;

use App\Models\Order;
use App\Models\OrderItem;
use BackedEnum;
use Carbon\Carbon;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;

class CostOfGoodsSoldReport extends BaseReportPage
{
    protected static ?string $title = 'Laporan HPP';

    protected static ?string $navigationLabel = 'Laporan HPP';

    protected static ?int $navigationSort = 6;

    protected static ?string $slug = 'reports/cogs';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalculator;

    protected static ?string $reportPermission = 'ViewCogsReport:Report';

    protected string $view = 'filament.pages.reports.cogs-report';

    public string $orderSource = 'all';

    public static function getReportKey(): string
    {
        return 'cogs';
    }

    public function getSummaryCards(): array
    {
        $orders = $this->baseOrdersQuery()->get();
        $netSales = (float) $orders->sum(fn (Order $order): float => ((float) $order->subtotal - (float) $order->discount_total));
        $cogsTotal = (float) $orders->sum('cogs_total');
        $grossProfit = $netSales - $cogsTotal;
        $cogsRatio = $netSales > 0 ? ($cogsTotal / $netSales) * 100 : 0;

        return [
            [
                'label' => 'Penjualan Bersih',
                'value' => $this->formatMoney($netSales),
                'hint' => 'Penjualan setelah diskon, sebelum pajak dan service.',
            ],
            [
                'label' => 'Harga Pokok Penjualan',
                'value' => $this->formatMoney($cogsTotal),
                'hint' => 'Akumulasi biaya bahan baku dari order selesai.',
            ],
            [
                'label' => 'Laba Kotor',
                'value' => $this->formatMoney($grossProfit),
                'hint' => 'Selisih antara penjualan bersih dan HPP.',
            ],
            [
                'label' => 'Rasio HPP',
                'value' => $this->formatNumber($cogsRatio, 2) . '%',
                'hint' => 'Persentase HPP terhadap penjualan bersih.',
            ],
        ];
    }

    public function getDailyBreakdown(): array
    {
        $rows = $this->baseOrdersQuery()
            ->selectRaw('DATE(ordered_at) as report_date')
            ->selectRaw('SUM(subtotal - discount_total) as net_sales_total')
            ->selectRaw('SUM(cogs_total) as cogs_total')
            ->selectRaw('SUM(gross_profit_total) as gross_profit_total')
            ->groupBy('report_date')
            ->orderByDesc('report_date')
            ->limit(31)
            ->get();

        return [
            'columns' => ['Tanggal', 'Penjualan Bersih', 'HPP', 'Laba Kotor', 'Margin Kotor'],
            'rows' => $rows->map(function ($row): array {
                $netSales = (float) $row->net_sales_total;
                $grossMargin = $netSales > 0 ? (((float) $row->gross_profit_total / $netSales) * 100) : 0;

                return [
                    Carbon::parse($row->report_date)->translatedFormat('d M Y'),
                    $this->formatMoney($netSales),
                    $this->formatMoney($row->cogs_total),
                    $this->formatMoney($row->gross_profit_total),
                    $this->formatNumber($grossMargin, 2) . '%',
                ];
            })->all(),
        ];
    }

    public function getProductBreakdown(): array
    {
        $rows = OrderItem::query()
            ->select('item_name_snapshot')
            ->selectRaw('SUM(qty) as qty_sold')
            ->selectRaw('SUM(net_sales_snapshot) as net_sales_total')
            ->selectRaw('SUM(cost_snapshot) as cogs_total')
            ->selectRaw('SUM(gross_profit_snapshot) as gross_profit_total')
            ->whereIn('order_id', $this->baseOrdersQuery()->select('id'))
            ->groupBy('item_name_snapshot')
            ->orderByDesc('gross_profit_total')
            ->limit(15)
            ->get();

        return [
            'columns' => ['Menu', 'Qty', 'Penjualan Bersih', 'HPP', 'Laba Kotor', 'Margin'],
            'rows' => $rows->map(function ($row): array {
                $netSales = (float) $row->net_sales_total;
                $margin = $netSales > 0 ? (((float) $row->gross_profit_total / $netSales) * 100) : 0;

                return [
                    (string) $row->item_name_snapshot,
                    $this->formatNumber($row->qty_sold, 0),
                    $this->formatMoney($netSales),
                    $this->formatMoney($row->cogs_total),
                    $this->formatMoney($row->gross_profit_total),
                    $this->formatNumber($margin, 2) . '%',
                ];
            })->all(),
        ];
    }

    protected function afterResetFilters(): void
    {
        $this->orderSource = 'all';
    }

    protected function getPdfData(): array
    {
        return [
            ...parent::getPdfData(),
            'summaryCards' => $this->getSummaryCards(),
            'dailyTable' => $this->getDailyBreakdown(),
            'productTable' => $this->getProductBreakdown(),
        ];
    }

    protected function getPdfFilterSummary(): array
    {
        return [
            'Sumber Order' => $this->orderSource === 'all'
                ? 'Semua'
                : (Order::sourceOptions()[$this->orderSource] ?? $this->orderSource),
        ];
    }

    protected function getPdfOrientation(): string
    {
        return 'landscape';
    }

    protected function getPdfView(): string
    {
        return 'pdf.reports.cogs';
    }

    protected function getCustomPdfQueryParameters(): array
    {
        return [
            'orderSource' => $this->orderSource,
        ];
    }

    protected function applyCustomReportFilters(array $filters): void
    {
        $this->orderSource = (string) ($filters['orderSource'] ?? 'all');
    }

    protected function baseOrdersQuery(): Builder
    {
        $query = Order::query()
            ->where('status', Order::STATUS_SERVED)
            ->whereNotNull('cost_accounted_at');

        $this->applyDateRange($query, 'ordered_at');

        if ($this->orderSource !== 'all') {
            $query->where('order_source', $this->orderSource);
        }

        return $query;
    }
}
