<?php

namespace App\Filament\Pages\Reports;

use App\Models\Order;
use App\Models\OrderItem;
use BackedEnum;
use Carbon\Carbon;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;

class SalesReport extends BaseReportPage
{
    public static function getReportKey(): string
    {
        return 'sales';
    }

    protected static ?string $title = 'Laporan Penjualan';

    protected static ?string $navigationLabel = 'Laporan Penjualan';

    protected static ?int $navigationSort = 1;

    protected static ?string $slug = 'reports/sales';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBarSquare;

    protected static ?string $reportPermission = 'ViewSalesReport:Report';

    protected string $view = 'filament.pages.reports.sales-report';

    public string $orderSource = 'all';

    public string $paymentMethod = 'all';

    /**
     * @return array<int, array{label: string, value: string, hint: string|null}>
     */
    public function getSummaryCards(): array
    {
        $ordersQuery = $this->baseOrdersQuery();

        $transactionCount = (clone $ordersQuery)->count();
        $grossSales = (float) (clone $ordersQuery)->sum('paid_total');
        $itemQty = (float) OrderItem::query()
            ->whereIn('order_id', $this->baseOrdersQuery()->select('id'))
            ->sum('qty');
        $averageTicket = $transactionCount > 0 ? ($grossSales / $transactionCount) : 0;

        return [
            [
                'label' => 'Omzet Dibayar',
                'value' => $this->formatMoney($grossSales),
                'hint' => 'Berdasarkan pembayaran sukses pada periode filter.',
            ],
            [
                'label' => 'Transaksi Selesai Bayar',
                'value' => $this->formatNumber($transactionCount),
                'hint' => 'POS internal dan QR publik yang sudah dibayar.',
            ],
            [
                'label' => 'Item Terjual',
                'value' => $this->formatNumber($itemQty, 0),
                'hint' => 'Akumulasi kuantitas item dari order terbayar.',
            ],
            [
                'label' => 'Rata-rata Nilai Order',
                'value' => $this->formatMoney($averageTicket),
                'hint' => 'Nilai rata-rata per transaksi terbayar.',
            ],
        ];
    }

    /**
     * @return array{columns: array<int, string>, rows: array<int, array<int, string>>}
     */
    public function getDailyBreakdown(): array
    {
        $rows = $this->baseOrdersQuery()
            ->selectRaw('DATE(ordered_at) as report_date')
            ->selectRaw('COUNT(*) as order_count')
            ->selectRaw('SUM(paid_total) as omzet')
            ->selectRaw('AVG(paid_total) as avg_ticket')
            ->selectRaw("SUM(CASE WHEN order_source = '" . Order::SOURCE_POS . "' THEN 1 ELSE 0 END) as pos_count")
            ->selectRaw("SUM(CASE WHEN order_source = '" . Order::SOURCE_PUBLIC_QR . "' THEN 1 ELSE 0 END) as qr_count")
            ->groupBy('report_date')
            ->orderByDesc('report_date')
            ->limit(31)
            ->get();

        return [
            'columns' => ['Tanggal', 'Transaksi', 'Omzet', 'Rata-rata', 'POS', 'QR'],
            'rows' => $rows->map(fn ($row): array => [
                Carbon::parse($row->report_date)->translatedFormat('d M Y'),
                $this->formatNumber($row->order_count),
                $this->formatMoney($row->omzet),
                $this->formatMoney($row->avg_ticket),
                $this->formatNumber($row->pos_count),
                $this->formatNumber($row->qr_count),
            ])->all(),
        ];
    }

    /**
     * @return array{columns: array<int, string>, rows: array<int, array<int, string>>}
     */
    public function getTopItems(): array
    {
        $rows = OrderItem::query()
            ->select('item_name_snapshot')
            ->selectRaw('SUM(qty) as qty_sold')
            ->selectRaw('SUM(total) as gross_sales')
            ->whereIn('order_id', $this->baseOrdersQuery()->select('id'))
            ->groupBy('item_name_snapshot')
            ->orderByDesc('qty_sold')
            ->limit(10)
            ->get();

        return [
            'columns' => ['Menu', 'Qty Terjual', 'Nilai Penjualan'],
            'rows' => $rows->map(fn ($row): array => [
                (string) $row->item_name_snapshot,
                $this->formatNumber($row->qty_sold, 0),
                $this->formatMoney($row->gross_sales),
            ])->all(),
        ];
    }

    protected function afterResetFilters(): void
    {
        $this->orderSource = 'all';
        $this->paymentMethod = 'all';
    }

    protected function getPdfData(): array
    {
        return [
            ...parent::getPdfData(),
            'summaryCards' => $this->getSummaryCards(),
            'dailyTable' => $this->getDailyBreakdown(),
            'topItemsTable' => $this->getTopItems(),
        ];
    }

    protected function getPdfFilterSummary(): array
    {
        return [
            'Sumber Order' => $this->orderSource === 'all'
                ? 'Semua'
                : (Order::sourceOptions()[$this->orderSource] ?? $this->orderSource),
            'Metode Bayar' => match ($this->paymentMethod) {
                'all' => 'Semua',
                Order::PAYMENT_CASH => 'Cash',
                Order::PAYMENT_MIDTRANS => 'Midtrans',
                default => $this->paymentMethod,
            },
        ];
    }

    protected function getPdfView(): string
    {
        return 'pdf.reports.sales';
    }

    protected function getCustomPdfQueryParameters(): array
    {
        return [
            'orderSource' => $this->orderSource,
            'paymentMethod' => $this->paymentMethod,
        ];
    }

    protected function applyCustomReportFilters(array $filters): void
    {
        $this->orderSource = (string) ($filters['orderSource'] ?? 'all');
        $this->paymentMethod = (string) ($filters['paymentMethod'] ?? 'all');
    }

    protected function baseOrdersQuery(): Builder
    {
        $query = Order::query()
            ->where('paid_total', '>', 0)
            ->whereNotIn('status', [
                Order::STATUS_PENDING_PAYMENT,
                Order::STATUS_PENDING_CONFIRMATION,
                Order::STATUS_EXPIRED,
                Order::STATUS_FAILED,
                Order::STATUS_CANCELED,
            ]);

        $this->applyDateRange($query, 'ordered_at');

        if ($this->orderSource !== 'all') {
            $query->where('order_source', $this->orderSource);
        }

        if ($this->paymentMethod !== 'all') {
            $query->where('payment_method', $this->paymentMethod);
        }

        return $query;
    }
}
