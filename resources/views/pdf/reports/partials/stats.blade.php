@if (! empty($summaryCards ?? []))
    <table class="stats-table">
        <tbody>
            <tr>
                @foreach ($summaryCards as $card)
                    <td class="stat-card">
                        <div class="stat-label">{{ $card['label'] }}</div>
                        <div class="stat-value">{{ $card['value'] }}</div>
                        @if (filled($card['hint'] ?? null))
                            <div class="stat-hint">{{ $card['hint'] }}</div>
                        @endif
                    </td>
                @endforeach
            </tr>
        </tbody>
    </table>
@endif
