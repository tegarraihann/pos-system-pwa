<!DOCTYPE html>
<html lang="id">
<head>
    @include('pdf.reports.partials.styles')
</head>
<body>
    <div class="report-shell">
        @include('pdf.reports.partials.header')
        @include('pdf.reports.partials.stats', ['summaryCards' => $summaryCards])
        @include('pdf.reports.partials.table', ['title' => 'Daftar Sesi Kasir', 'table' => $sessionsTable, 'numericColumns' => [4, 5, 6, 7]])
        <div class="note-box">
            Kolom selisih menunjukkan perbedaan kas aktual terhadap kas sistem pada saat sesi ditutup.
        </div>
    </div>
    @include('pdf.reports.partials.footer')
</body>
</html>
