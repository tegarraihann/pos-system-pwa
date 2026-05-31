<!DOCTYPE html>
<html lang="id">
<head>
    @include('pdf.reports.partials.styles')
</head>
<body>
    <div class="report-shell">
        @include('pdf.reports.partials.header')
        @include('pdf.reports.partials.stats', ['summaryCards' => $summaryCards])
        @include('pdf.reports.partials.table', ['title' => 'Daftar Absensi', 'table' => $attendanceTable, 'numericColumns' => [5]])
    </div>
    @include('pdf.reports.partials.footer')
</body>
</html>
