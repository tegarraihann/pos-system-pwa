@props([
    'title',
    'table',
    'numericColumns' => [],
])

<div class="section">
    <h2 class="section-title">{{ $title }}</h2>
    <table class="report-table">
        <thead>
            <tr>
                @foreach ($table['columns'] as $index => $column)
                    <th @class(['text-right' => in_array($index, $numericColumns, true)])>{{ $column }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse ($table['rows'] as $row)
                <tr>
                    @foreach ($row as $index => $cell)
                        <td @class(['text-right' => in_array($index, $numericColumns, true)])>{{ $cell }}</td>
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td colspan="{{ count($table['columns']) }}">Belum ada data untuk filter ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
