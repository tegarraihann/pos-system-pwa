<?php

namespace App\Filament\Pages\Reports;

use App\Models\ChartOfAccount;
use App\Models\OperatingExpense;
use BackedEnum;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;

class OperatingExpenseReport extends BaseReportPage
{
    protected static ?string $title = 'Laporan Beban Operasional';

    protected static ?string $navigationLabel = 'Laporan Beban Operasional';

    protected static ?int $navigationSort = 7;

    protected static ?string $slug = 'reports/operating-expenses';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedWallet;

    protected static ?string $reportPermission = 'ViewExpenseReport:Report';

    protected string $view = 'filament.pages.reports.operating-expense-report';

    public string $accountId = 'all';

    public string $paymentMethod = 'all';

    public static function getReportKey(): string
    {
        return 'operating-expenses';
    }

    public function getSummaryCards(): array
    {
        $expensesQuery = $this->baseExpensesQuery();
        $totalExpense = (float) (clone $expensesQuery)->sum('amount');
        $entryCount = (clone $expensesQuery)->count();
        $averageExpense = $entryCount > 0 ? ($totalExpense / $entryCount) : 0;
        $largestExpense = (float) (clone $expensesQuery)->max('amount');

        return [
            [
                'label' => 'Total Beban Operasional',
                'value' => $this->formatMoney($totalExpense),
                'hint' => 'Akumulasi seluruh beban yang tercatat pada periode filter.',
            ],
            [
                'label' => 'Jumlah Pencatatan',
                'value' => $this->formatNumber($entryCount),
                'hint' => 'Total transaksi beban operasional yang dibukukan.',
            ],
            [
                'label' => 'Rata-rata Nominal',
                'value' => $this->formatMoney($averageExpense),
                'hint' => 'Rata-rata nominal per transaksi beban operasional.',
            ],
            [
                'label' => 'Beban Terbesar',
                'value' => $this->formatMoney($largestExpense),
                'hint' => 'Nominal tertinggi dari satu transaksi beban.',
            ],
        ];
    }

    public function getAccountSummary(): array
    {
        $rows = $this->baseExpensesQuery()
            ->select('chart_of_account_id')
            ->selectRaw('COUNT(*) as expense_count')
            ->selectRaw('SUM(amount) as total_amount')
            ->with('account')
            ->groupBy('chart_of_account_id')
            ->orderByDesc('total_amount')
            ->get();

        return [
            'columns' => ['Akun Beban', 'Jumlah Transaksi', 'Total Beban'],
            'rows' => $rows->map(fn (OperatingExpense $expense): array => [
                ($expense->account?->code ? $expense->account->code . ' - ' : '') . ($expense->account?->name ?? '-'),
                $this->formatNumber($expense->expense_count ?? 0),
                $this->formatMoney($expense->total_amount ?? 0),
            ])->all(),
        ];
    }

    public function getExpenseDetails(): array
    {
        $rows = $this->baseExpensesQuery()
            ->with('account')
            ->latest('expense_date')
            ->limit(30)
            ->get();

        return [
            'columns' => ['Tanggal', 'Akun', 'Judul', 'Metode', 'Nominal'],
            'rows' => $rows->map(fn (OperatingExpense $expense): array => [
                optional($expense->expense_date)->translatedFormat('d M Y H:i') ?? '-',
                ($expense->account?->code ? $expense->account->code . ' - ' : '') . ($expense->account?->name ?? '-'),
                $expense->title,
                $expense->payment_method
                    ? (OperatingExpense::paymentMethodOptions()[$expense->payment_method] ?? strtoupper($expense->payment_method))
                    : '-',
                $this->formatMoney($expense->amount),
            ])->all(),
        ];
    }

    protected function afterResetFilters(): void
    {
        $this->accountId = 'all';
        $this->paymentMethod = 'all';
    }

    protected function getPdfData(): array
    {
        return [
            ...parent::getPdfData(),
            'summaryCards' => $this->getSummaryCards(),
            'accountSummaryTable' => $this->getAccountSummary(),
            'detailsTable' => $this->getExpenseDetails(),
        ];
    }

    protected function getPdfFilterSummary(): array
    {
        $account = $this->accountId !== 'all'
            ? ChartOfAccount::query()->find($this->accountId)
            : null;

        return [
            'Akun Beban' => $account ? ($account->code . ' - ' . $account->name) : 'Semua',
            'Metode Pembayaran' => $this->paymentMethod === 'all'
                ? 'Semua'
                : (OperatingExpense::paymentMethodOptions()[$this->paymentMethod] ?? strtoupper($this->paymentMethod)),
        ];
    }

    protected function getPdfOrientation(): string
    {
        return 'landscape';
    }

    protected function getPdfView(): string
    {
        return 'pdf.reports.operating-expenses';
    }

    protected function getCustomPdfQueryParameters(): array
    {
        return [
            'accountId' => $this->accountId,
            'paymentMethod' => $this->paymentMethod,
        ];
    }

    protected function applyCustomReportFilters(array $filters): void
    {
        $this->accountId = (string) ($filters['accountId'] ?? 'all');
        $this->paymentMethod = (string) ($filters['paymentMethod'] ?? 'all');
    }

    protected function baseExpensesQuery(): Builder
    {
        $query = OperatingExpense::query()->whereHas('account');

        $this->applyDateRange($query, 'expense_date');

        if ($this->accountId !== 'all') {
            $query->where('chart_of_account_id', $this->accountId);
        }

        if ($this->paymentMethod !== 'all') {
            $query->where('payment_method', $this->paymentMethod);
        }

        return $query;
    }
}
