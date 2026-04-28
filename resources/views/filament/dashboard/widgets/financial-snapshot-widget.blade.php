@php
    $d = $this->getSnapshotData();

    $cards = [
        [
            'border'    => '#8b5cf6',
            'label'     => 'Cash Collected',
            'value'     => '$' . number_format($d['cash_collected'], 0),
            'sub'       => $d['cash_change_pct'] !== null
                ? ($d['cash_change_pct'] >= 0 ? '+' : '') . number_format($d['cash_change_pct'], 1) . '% vs last month'
                : 'This month',
            'sub_class' => ($d['cash_change_pct'] ?? 0) >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400',
        ],
        [
            'border'    => '#22c55e',
            'label'     => 'Actual Profit',
            'value'     => '$' . number_format($d['actual_profit'], 0),
            'sub'       => number_format($d['profit_margin'], 1) . '% margin',
            'sub_class' => $d['profit_margin'] >= 20
                ? 'text-green-600 dark:text-green-400'
                : ($d['profit_margin'] >= 10 ? 'text-amber-600 dark:text-amber-400' : 'text-red-600 dark:text-red-400'),
        ],
        [
            'border'    => '#ef4444',
            'label'     => 'Total Expenses',
            'value'     => '$' . number_format($d['total_expenses'], 0),
            'sub'       => $d['expenses_pct'] !== null
                ? ($d['expenses_pct'] >= 0 ? '+' : '') . number_format($d['expenses_pct'], 1) . '% vs last month'
                : 'This month',
            'sub_class' => ($d['expenses_pct'] ?? 0) <= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400',
        ],
        [
            'border'    => '#f59e0b',
            'label'     => 'Outstanding',
            'value'     => '$' . number_format($d['outstanding'], 0),
            'sub'       => $d['outstanding_count'] . ' ' . Str::plural('invoice', $d['outstanding_count']) . ' unpaid',
            'sub_class' => $d['outstanding_count'] > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-green-600 dark:text-green-400',
        ],
        [
            'border'    => '#14b8a6',
            'label'     => 'Cash vs Profit Gap',
            'value'     => '$' . number_format(abs($d['gap']), 0),
            'sub'       => $d['gap_label'],
            'sub_class' => 'text-gray-500 dark:text-gray-400',
        ],
    ];
@endphp

<x-filament-widgets::widget>
    <p class="text-gray-400 dark:text-gray-500" style="font-size:.7rem;font-weight:700;letter-spacing:.1em;text-transform:uppercase;margin-bottom:.875rem;">
        Financial Snapshot &mdash; {{ $d['month_name'] }}
    </p>

    <div style="display:grid;grid-template-columns:repeat(5,1fr);gap:.875rem;">
        @foreach($cards as $card)
        <div class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700"
             style="border-top:3px solid {{ $card['border'] }};border-radius:.75rem;padding:1.25rem 1.125rem;box-shadow:0 1px 3px rgba(0,0,0,.06);">
            <p class="text-gray-400 dark:text-gray-500" style="font-size:.65rem;font-weight:700;letter-spacing:.1em;text-transform:uppercase;margin-bottom:.625rem;">
                {{ $card['label'] }}
            </p>
            <p class="text-gray-900 dark:text-white" style="font-size:1.6rem;font-weight:800;letter-spacing:-.03em;line-height:1;margin-bottom:.5rem;">
                {{ $card['value'] }}
            </p>
            <p class="text-sm font-semibold {{ $card['sub_class'] }}">
                {{ $card['sub'] }}
            </p>
        </div>
        @endforeach
    </div>
</x-filament-widgets::widget>
