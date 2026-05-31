@props([
    'stats' => [],
])

<div class="report-stat-grid">
    @foreach ($stats as $stat)
        <div class="report-stat-card">
            <p class="report-stat-label">{{ $stat['label'] }}</p>
            <p class="report-stat-value">{{ $stat['value'] }}</p>
            @if (filled($stat['hint'] ?? null))
                <p class="report-stat-hint">{{ $stat['hint'] }}</p>
            @endif
        </div>
    @endforeach
</div>
