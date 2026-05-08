@php
    $d = $this->getData();

    $bars = [
        [
            'label'   => 'Collected',
            'value'   => '$' . number_format($d['cash_collected'], 0),
            'sub'     => '$' . number_format($d['total_invoiced'], 0) . ' invoiced',
            'pct'     => $d['collected_pct'],
            'color'   => '#22c55e',
            'bg'      => 'rgba(34,197,94,.15)',
        ],
        [
            'label'   => 'Outstanding',
            'value'   => '$' . number_format($d['outstanding'], 0),
            'sub'     => 'Awaiting payment',
            'pct'     => $d['outstanding_pct'],
            'color'   => '#ef4444',
            'bg'      => 'rgba(239,68,68,.12)',
        ],
        [
            'label'   => 'Overdue' . ($d['oldest_overdue_days'] > 0 ? ' ' . $d['oldest_overdue_days'] . 'd' : ''),
            'value'   => '$' . number_format($d['overdue_amount'], 0),
            'sub'     => $d['overdue_amount'] > 0 ? number_format($d['overdue_pct'], 0) . '% of outstanding' : 'None overdue',
            'pct'     => $d['overdue_pct'],
            'color'   => '#f59e0b',
            'bg'      => 'rgba(245,158,11,.12)',
        ],
    ];
@endphp

<x-filament-widgets::widget>
    <p class="text-gray-400 dark:text-gray-500"
       style="font-size:.7rem;font-weight:700;letter-spacing:.1em;text-transform:uppercase;margin-bottom:1rem;">
        Collection Health &mdash; {{ $d['month_name'] }}
    </p>

    <div style="display:flex;flex-direction:column;gap:1rem;">
        @foreach($bars as $bar)
        <div style="background:{{ $bar['bg'] }};border-radius:.75rem;padding:1rem 1.125rem;">
            <div style="display:flex;justify-content:space-between;align-items:baseline;margin-bottom:.5rem;">
                <span class="text-gray-600 dark:text-gray-300"
                      style="font-size:.72rem;font-weight:700;letter-spacing:.07em;text-transform:uppercase;">
                    {{ $bar['label'] }}
                </span>
                <div style="text-align:right;">
                    <span class="text-gray-900 dark:text-white"
                          style="font-size:1.1rem;font-weight:800;">{{ $bar['value'] }}</span>
                    <span class="text-gray-400 dark:text-gray-500"
                          style="font-size:.72rem;margin-left:.4rem;">{{ $bar['sub'] }}</span>
                </div>
            </div>
            <div style="height:8px;background:rgba(0,0,0,.08);border-radius:99px;overflow:hidden;">
                <div style="height:100%;width:{{ max(2, $bar['pct']) }}%;background:{{ $bar['color'] }};border-radius:99px;transition:width .4s ease;"></div>
            </div>
        </div>
        @endforeach
    </div>
</x-filament-widgets::widget>
