<!DOCTYPE html>
<html lang="id">
<head>
    @include('pdf.reports.partials.styles')
</head>
<body>
    <div class="report-shell">
        @include('pdf.reports.partials.header')
        @include('pdf.reports.partials.stats', ['summaryCards' => $summaryCards])
        @include('pdf.reports.partials.table', ['title' => 'Ringkasan per Akun Beban', 'table' => $accountSummaryTable, 'numericColumns' => [1, 2]])
        @include('pdf.reports.partials.table', ['title' => 'Detail Beban Operasional', 'table' => $detailsTable, 'numericColumns' => [4]])
        <div class="note-box">
            Laporan ini hanya menampilkan beban operasional yang sudah dicatat secara manual melalui modul beban operasional.
        </div>
    </div>
    @include('pdf.reports.partials.footer')
</body>
</html>
