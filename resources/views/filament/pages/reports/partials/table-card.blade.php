@props([
    'title',
    'columns' => [],
    'rows' => [],
    'emptyMessage' => 'Belum ada data untuk filter ini.',
])

<div class="report-table-card">
    <div class="report-table-header">
        <h3 class="report-table-title">{{ $title }}</h3>
    </div>

    <div class="report-table-scroller">
        <table class="report-table">
            <thead>
                <tr>
                    @foreach ($columns as $column)
                        <th>{{ $column }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse ($rows as $row)
                    <tr>
                        @foreach ($row as $cell)
                            <td>{{ $cell }}</td>
                        @endforeach
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ max(count($columns), 1) }}" class="report-empty">
                            {{ $emptyMessage }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
