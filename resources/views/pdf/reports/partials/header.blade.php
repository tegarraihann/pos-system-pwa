<div class="report-header">
    <div class="report-app">{{ config('app.name', 'Pos System') }}</div>
    <h1 class="report-title">{{ $title }}</h1>
    <div class="report-subtitle">
        Periode: {{ $periodLabel }} | Dicetak: {{ $generatedAt->translatedFormat('d M Y H:i') }}
    </div>

    <table class="meta-grid">
        <tbody>
            <tr>
                <td class="meta-label">Dicetak Oleh</td>
                <td>{{ $generatedBy }}</td>
            </tr>
            @foreach ($filterSummary as $label => $value)
                <tr>
                    <td class="meta-label">{{ $label }}</td>
                    <td>{{ $value }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
