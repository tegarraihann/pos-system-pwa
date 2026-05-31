<?php

namespace App\Http\Controllers;

use App\Filament\Pages\Reports\AttendanceReport;
use App\Filament\Pages\Reports\BaseReportPage;
use App\Filament\Pages\Reports\CashierSessionReport;
use App\Filament\Pages\Reports\CostOfGoodsSoldReport;
use App\Filament\Pages\Reports\OperatingExpenseReport;
use App\Filament\Pages\Reports\PaymentReport;
use App\Filament\Pages\Reports\ProfitLossReport;
use App\Filament\Pages\Reports\SalesReport;
use App\Filament\Pages\Reports\StockReport;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ReportPdfController extends Controller
{
    public function __invoke(Request $request, string $report): Response
    {
        $pageClass = $this->resolveReportPageClass($report);

        abort_unless($pageClass && is_subclass_of($pageClass, BaseReportPage::class), 404);
        abort_unless($pageClass::canAccess(), 403);

        /** @var BaseReportPage $page */
        $page = app($pageClass);
        $page->mount();
        $page->applyReportFilters($request->query());

        return $page->downloadPdf();
    }

    protected function resolveReportPageClass(string $report): ?string
    {
        return [
            SalesReport::getReportKey() => SalesReport::class,
            PaymentReport::getReportKey() => PaymentReport::class,
            CashierSessionReport::getReportKey() => CashierSessionReport::class,
            AttendanceReport::getReportKey() => AttendanceReport::class,
            StockReport::getReportKey() => StockReport::class,
            CostOfGoodsSoldReport::getReportKey() => CostOfGoodsSoldReport::class,
            OperatingExpenseReport::getReportKey() => OperatingExpenseReport::class,
            ProfitLossReport::getReportKey() => ProfitLossReport::class,
        ][$report] ?? null;
    }
}
