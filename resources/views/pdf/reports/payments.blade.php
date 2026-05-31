<!DOCTYPE html>
<html lang="id">
<head>
    @include('pdf.reports.partials.styles')
</head>
<body>
    <div class="report-shell">
        @include('pdf.reports.partials.header')
        @include('pdf.reports.partials.stats', ['summaryCards' => $summaryCards])
        @include('pdf.reports.partials.table', ['title' => 'Ringkasan per Metode', 'table' => $methodSummaryTable, 'numericColumns' => [1, 2, 3, 4]])
        @include('pdf.reports.partials.table', ['title' => 'Pembayaran Terbaru', 'table' => $recentPaymentsTable, 'numericColumns' => [5]])
        <div class="note-box">
            Status payment berasal dari record pembayaran, sehingga cocok untuk audit transaksi Midtrans maupun tunai.
        </div>
    </div>
    @include('pdf.reports.partials.footer')
</body>
</html>
