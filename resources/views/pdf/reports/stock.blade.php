<!DOCTYPE html>
<html lang="id">
<head>
    @include('pdf.reports.partials.styles')
</head>
<body>
    <div class="report-shell">
        @include('pdf.reports.partials.header')
        @include('pdf.reports.partials.stats', ['summaryCards' => $summaryCards])
        @include('pdf.reports.partials.table', ['title' => 'Varian Menu Stok Kritis', 'table' => $menuTable, 'numericColumns' => [2, 3]])
        @include('pdf.reports.partials.table', ['title' => 'Bahan Baku Stok Kritis', 'table' => $ingredientTable, 'numericColumns' => [1, 3]])
        <div class="note-box">
            {{ $notes }}
        </div>
    </div>
    @include('pdf.reports.partials.footer')
</body>
</html>
