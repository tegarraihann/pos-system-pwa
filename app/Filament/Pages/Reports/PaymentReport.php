<?php

namespace App\Filament\Pages\Reports;

use App\Models\Order;
use App\Models\Payment;
use BackedEnum;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;

class PaymentReport extends BaseReportPage
{
    public static function getReportKey(): string
    {
        return 'payments';
    }

    protected static ?string $title = 'Laporan Pembayaran';

    protected static ?string $navigationLabel = 'Laporan Pembayaran';

    protected static ?int $navigationSort = 2;

    protected static ?string $slug = 'reports/payments';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCreditCard;

    protected static ?string $reportPermission = 'ViewPaymentReport:Report';

    protected string $view = 'filament.pages.reports.payment-report';

    public string $paymentStatus = 'all';

    public string $orderSource = 'all';

    public string $paymentMethod = 'all';

    /**
     * @return array<int, array{label: string, value: string, hint: string|null}>
     */
    public function getSummaryCards(): array
    {
        $paymentsQuery = $this->basePaymentsQuery();

        $paidAmount = (float) (clone $paymentsQuery)
            ->where('status', 'paid')
            ->sum('amount');
        $paidCount = (clone $paymentsQuery)
            ->where('status', 'paid')
            ->count();
        $pendingCount = (clone $paymentsQuery)
            ->where('status', 'pending')
            ->count();
        $issueCount = (clone $paymentsQuery)
            ->whereIn('status', ['failed', 'expired', 'canceled'])
            ->count();

        return [
            [
                'label' => 'Nominal Pembayaran Sukses',
                'value' => $this->formatMoney($paidAmount),
                'hint' => 'Akumulasi nominal payment dengan status paid.',
            ],
            [
                'label' => 'Pembayaran Sukses',
                'value' => $this->formatNumber($paidCount),
                'hint' => 'Jumlah record payment yang berhasil dibayar.',
            ],
            [
                'label' => 'Masih Pending',
                'value' => $this->formatNumber($pendingCount),
                'hint' => 'Cocok untuk memantau order QR yang belum settle.',
            ],
            [
                'label' => 'Gagal / Kadaluarsa',
                'value' => $this->formatNumber($issueCount),
                'hint' => 'Gabungan failed, expired, dan canceled.',
            ],
        ];
    }

    /**
     * @return array{columns: array<int, string>, rows: array<int, array<int, string>>}
     */
    public function getMethodSummary(): array
    {
        $rows = $this->basePaymentsQuery()
            ->select('method')
            ->selectRaw("SUM(CASE WHEN status = 'paid' THEN 1 ELSE 0 END) as paid_count")
            ->selectRaw("SUM(CASE WHEN status = 'paid' THEN amount ELSE 0 END) as paid_amount")
            ->selectRaw("SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_count")
            ->selectRaw("SUM(CASE WHEN status IN ('failed', 'expired', 'canceled') THEN 1 ELSE 0 END) as issue_count")
            ->groupBy('method')
            ->orderBy('method')
            ->get();

        return [
            'columns' => ['Metode', 'Paid', 'Nominal Paid', 'Pending', 'Masalah'],
            'rows' => $rows->map(fn ($row): array => [
                strtoupper((string) $row->method),
                $this->formatNumber($row->paid_count),
                $this->formatMoney($row->paid_amount),
                $this->formatNumber($row->pending_count),
                $this->formatNumber($row->issue_count),
            ])->all(),
        ];
    }

    /**
     * @return array{columns: array<int, string>, rows: array<int, array<int, string>>}
     */
    public function getRecentPayments(): array
    {
        $rows = $this->basePaymentsQuery()
            ->with('order')
            ->latest('created_at')
            ->limit(20)
            ->get();

        return [
            'columns' => ['Waktu', 'Order', 'Sumber', 'Metode', 'Status', 'Nominal'],
            'rows' => $rows->map(fn (Payment $payment): array => [
                optional($payment->created_at)->translatedFormat('d M Y H:i') ?? '-',
                (string) ($payment->order?->order_number ?? '-'),
                Order::sourceOptions()[$payment->order?->order_source ?? ''] ?? '-',
                strtoupper((string) $payment->method),
                $this->translatePaymentStatus((string) $payment->status),
                $this->formatMoney($payment->amount),
            ])->all(),
        ];
    }

    protected function afterResetFilters(): void
    {
        $this->paymentStatus = 'all';
        $this->orderSource = 'all';
        $this->paymentMethod = 'all';
    }

    protected function getPdfData(): array
    {
        return [
            ...parent::getPdfData(),
            'summaryCards' => $this->getSummaryCards(),
            'methodSummaryTable' => $this->getMethodSummary(),
            'recentPaymentsTable' => $this->getRecentPayments(),
        ];
    }

    protected function getPdfFilterSummary(): array
    {
        return [
            'Status Payment' => $this->paymentStatus === 'all' ? 'Semua' : $this->translatePaymentStatus($this->paymentStatus),
            'Sumber Order' => $this->orderSource === 'all'
                ? 'Semua'
                : (Order::sourceOptions()[$this->orderSource] ?? $this->orderSource),
            'Metode' => $this->paymentMethod === 'all' ? 'Semua' : strtoupper($this->paymentMethod),
        ];
    }

    protected function getPdfOrientation(): string
    {
        return 'landscape';
    }

    protected function getPdfView(): string
    {
        return 'pdf.reports.payments';
    }

    protected function getCustomPdfQueryParameters(): array
    {
        return [
            'paymentStatus' => $this->paymentStatus,
            'orderSource' => $this->orderSource,
            'paymentMethod' => $this->paymentMethod,
        ];
    }

    protected function applyCustomReportFilters(array $filters): void
    {
        $this->paymentStatus = (string) ($filters['paymentStatus'] ?? 'all');
        $this->orderSource = (string) ($filters['orderSource'] ?? 'all');
        $this->paymentMethod = (string) ($filters['paymentMethod'] ?? 'all');
    }

    protected function basePaymentsQuery(): Builder
    {
        $query = Payment::query()->whereHas('order');

        $this->applyDateRange($query, 'created_at');

        if ($this->paymentStatus !== 'all') {
            $query->where('status', $this->paymentStatus);
        }

        if ($this->paymentMethod !== 'all') {
            $query->where('method', $this->paymentMethod);
        }

        if ($this->orderSource !== 'all') {
            $query->whereHas('order', function (Builder $orderQuery): void {
                $orderQuery->where('order_source', $this->orderSource);
            });
        }

        return $query;
    }

    protected function translatePaymentStatus(string $status): string
    {
        return match ($status) {
            'paid' => 'Paid',
            'pending' => 'Pending',
            'expired' => 'Kadaluarsa',
            'failed' => 'Gagal',
            'canceled' => 'Dibatalkan',
            default => ucfirst($status),
        };
    }
}
