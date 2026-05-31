<!DOCTYPE html>
<html lang="id">
<head>
    @include('pdf.reports.partials.styles')
</head>
<body>
    <div class="report-shell">
        @include('pdf.reports.partials.header')
        @include('pdf.reports.partials.stats', ['summaryCards' => $summaryCards])
        @include('pdf.reports.partials.table', ['title' => 'Ringkasan Harian', 'table' => $dailyTable, 'numericColumns' => [1, 2, 3, 4, 5]])
        @include('pdf.reports.partials.table', ['title' => 'Top Menu Terjual', 'table' => $topItemsTable, 'numericColumns' => [1, 2]])
        <div class="note-box">
            Laporan penjualan hanya menghitung order yang sudah memiliki pembayaran sukses.
        </div>
    </div>
    @include('pdf.reports.partials.footer')
</body>
</html>
