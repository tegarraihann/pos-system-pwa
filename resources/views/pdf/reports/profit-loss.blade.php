<!DOCTYPE html>
<html lang="id">
<head>
    @include('pdf.reports.partials.styles')
</head>
<body>
    <div class="report-shell">
        @include('pdf.reports.partials.header')
        @include('pdf.reports.partials.stats', ['summaryCards' => $summaryCards])
        @include('pdf.reports.partials.table', ['title' => 'Struktur Laba Rugi', 'table' => $statementTable, 'numericColumns' => [1, 2]])
        @include('pdf.reports.partials.table', ['title' => 'Kontribusi Laba Kotor per Sumber Order', 'table' => $sourceTable, 'numericColumns' => [1, 2, 3]])
        @include('pdf.reports.partials.table', ['title' => 'Komposisi Beban Operasional', 'table' => $expenseTable, 'numericColumns' => [1]])
        <div class="note-box">
            Laba rugi tahap ini menghitung penjualan bersih, HPP, dan beban operasional yang tercatat. Pendapatan atau beban di luar modul ini belum masuk perhitungan.
        </div>
    </div>
    @include('pdf.reports.partials.footer')
</body>
</html>
