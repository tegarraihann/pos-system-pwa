<?php

namespace App\Filament\Pages\Reports;

use App\Models\OperatingExpense;
use App\Models\Order;
use BackedEnum;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;

class ProfitLossReport extends BaseReportPage
{
    protected static ?string $title = 'Laporan Laba Rugi';

    protected static ?string $navigationLabel = 'Laporan Laba Rugi';

    protected static ?int $navigationSort = 8;

    protected static ?string $slug = 'reports/profit-loss';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPresentationChartLine;

    protected static ?string $reportPermission = 'ViewProfitLossReport:Report';

    protected string $view = 'filament.pages.reports.profit-loss-report';

    public string $orderSource = 'all';

    public static function getReportKey(): string
    {
        return 'profit-loss';
    }

    public function getSummaryCards(): array
    {
        $statement = $this->buildStatement();

        return [
            [
                'label' => 'Penjualan Bersih',
                'value' => $this->formatMoney($statement['net_sales']),
                'hint' => 'Pendapatan penjualan setelah dikurangi diskon penjualan.',
            ],
            [
                'label' => 'Harga Pokok Penjualan',
                'value' => $this->formatMoney($statement['cogs_total']),
                'hint' => 'Biaya bahan baku yang terpakai untuk order selesai.',
            ],
            [
                'label' => 'Laba Kotor',
                'value' => $this->formatMoney($statement['gross_profit']),
                'hint' => 'Penjualan bersih setelah dikurangi harga pokok penjualan.',
            ],
            [
                'label' => 'Beban Operasional',
                'value' => $this->formatMoney($statement['operating_expenses']),
                'hint' => 'Beban yang tercatat pada periode yang sama.',
            ],
            [
                'label' => 'Laba Bersih',
                'value' => $this->formatMoney($statement['net_profit']),
                'hint' => 'Hasil akhir setelah laba kotor dikurangi beban operasional.',
            ],
        ];
    }

    public function getStatementTable(): array
    {
        $statement = $this->buildStatement();
        $netSales = $statement['net_sales'];

        $rows = collect([
            ['Penjualan Bersih', $statement['net_sales']],
            ['Harga Pokok Penjualan', $statement['cogs_total']],
            ['Laba Kotor', $statement['gross_profit']],
            ['Beban Operasional', $statement['operating_expenses']],
            ['Laba Bersih', $statement['net_profit']],
        ])->map(function (array $row) use ($netSales): array {
            $ratio = $netSales > 0 ? (((float) $row[1] / $netSales) * 100) : 0;

            return [
                $row[0],
                $this->formatMoney($row[1]),
                $this->formatNumber($ratio, 2) . '%',
            ];
        })->all();

        return [
            'columns' => ['Pos Laporan', 'Nominal', '% Penjualan Bersih'],
            'rows' => $rows,
        ];
    }

    public function getSourceBreakdown(): array
    {
        $rows = $this->baseOrdersQuery()
            ->select('order_source')
            ->selectRaw('SUM(subtotal - discount_total) as net_sales_total')
            ->selectRaw('SUM(cogs_total) as cogs_total')
            ->selectRaw('SUM(gross_profit_total) as gross_profit_total')
            ->groupBy('order_source')
            ->orderBy('order_source')
            ->get();

        return [
            'columns' => ['Sumber Order', 'Penjualan Bersih', 'HPP', 'Laba Kotor'],
            'rows' => $rows->map(fn ($row): array => [
                Order::sourceOptions()[$row->order_source] ?? $row->order_source,
                $this->formatMoney($row->net_sales_total),
                $this->formatMoney($row->cogs_total),
                $this->formatMoney($row->gross_profit_total),
            ])->all(),
        ];
    }

    public function getExpenseBreakdown(): array
    {
        $rows = $this->baseExpenseQuery()
            ->with('account')
            ->select('chart_of_account_id')
            ->selectRaw('SUM(amount) as total_amount')
            ->groupBy('chart_of_account_id')
            ->orderByDesc('total_amount')
            ->get();

        return [
            'columns' => ['Akun Beban', 'Total Beban'],
            'rows' => $rows->map(fn (OperatingExpense $expense): array => [
                ($expense->account?->code ? $expense->account->code . ' - ' : '') . ($expense->account?->name ?? '-'),
                $this->formatMoney($expense->total_amount ?? 0),
            ])->all(),
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
            'statementTable' => $this->getStatementTable(),
            'sourceTable' => $this->getSourceBreakdown(),
            'expenseTable' => $this->getExpenseBreakdown(),
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

    protected function getPdfView(): string
    {
        return 'pdf.reports.profit-loss';
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

    protected function baseExpenseQuery(): Builder
    {
        $query = OperatingExpense::query()->whereHas('account');

        $this->applyDateRange($query, 'expense_date');

        return $query;
    }

    protected function buildStatement(): array
    {
        $orders = $this->baseOrdersQuery()->get();
        $netSales = (float) $orders->sum(fn (Order $order): float => ((float) $order->subtotal - (float) $order->discount_total));
        $cogsTotal = (float) $orders->sum('cogs_total');
        $grossProfit = $netSales - $cogsTotal;
        $operatingExpenses = (float) $this->baseExpenseQuery()->sum('amount');
        $netProfit = $grossProfit - $operatingExpenses;

        return [
            'net_sales' => $netSales,
            'cogs_total' => $cogsTotal,
            'gross_profit' => $grossProfit,
            'operating_expenses' => $operatingExpenses,
            'net_profit' => $netProfit,
        ];
    }
}
