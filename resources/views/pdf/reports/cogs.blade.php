<!DOCTYPE html>
<html lang="id">
<head>
    @include('pdf.reports.partials.styles')
</head>
<body>
    <div class="report-shell">
        @include('pdf.reports.partials.header')
        @include('pdf.reports.partials.stats', ['summaryCards' => $summaryCards])
        @include('pdf.reports.partials.table', ['title' => 'Ringkasan HPP Harian', 'table' => $dailyTable, 'numericColumns' => [1, 2, 3, 4]])
        @include('pdf.reports.partials.table', ['title' => 'HPP dan Laba Kotor per Menu', 'table' => $productTable, 'numericColumns' => [1, 2, 3, 4, 5]])
        <div class="note-box">
            HPP dihitung dari resep dan harga beli bahan baku yang tersimpan pada saat order diselesaikan.
        </div>
    </div>
    @include('pdf.reports.partials.footer')
</body>
</html>
