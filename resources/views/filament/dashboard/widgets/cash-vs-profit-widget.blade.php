<x-filament-widgets::widget>
    @php
        $data = $this->getFinancialSnapshot();
        $profit = $data['actual_profit'];
        $profitPositive = $profit >= 0;
        $margin = $data['profit_margin'];
        $marginColor = $margin >= 20 ? 'text-green-600 dark:text-green-400' : ($margin >= 10 ? 'text-yellow-600 dark:text-yellow-400' : 'text-red-600 dark:text-red-400');
    @endphp

    <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm overflow-hidden">

        {{-- Header --}}
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 dark:border-gray-700">
            <div>
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Financial Snapshot</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ $data['month_name'] }}</p>
            </div>
            <a href="{{ route('filament.dashboard.pages.analytics-dashboard') }}"
               class="text-xs font-medium text-primary-600 dark:text-primary-400 hover:underline">
                Full Analytics →
            </a>
        </div>

        {{-- Main callout: Cash vs Profit --}}
        <div class="grid grid-cols-1 md:grid-cols-2 divide-y md:divide-y-0 md:divide-x divide-gray-100 dark:divide-gray-700">

            {{-- Cash Collected --}}
            <div class="px-6 py-5">
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1">
                    Cash Collected This Month
                </p>
                <p class="text-3xl font-bold text-gray-900 dark:text-white">
                    ${{ number_format($data['cash_collected'], 0) }}
                </p>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    Payments received from clients
                </p>
            </div>

            {{-- Actual Profit --}}
            <div class="px-6 py-5 {{ $profitPositive ? 'bg-green-50/40 dark:bg-green-900/10' : 'bg-red-50/40 dark:bg-red-900/10' }}">
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1">
                    Actual Profit This Month
                </p>
                <p class="text-3xl font-bold {{ $profitPositive ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                    {{ $profitPositive ? '' : '-' }}${{ number_format(abs($profit), 0) }}
                </p>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    Revenue minus all expenses &bull;
                    <span class="{{ $marginColor }} font-medium">{{ number_format($margin, 1) }}% margin</span>
                </p>
            </div>
        </div>

        {{-- Revenue & Expenses breakdown --}}
        <div class="grid grid-cols-2 md:grid-cols-3 gap-0 border-t border-gray-100 dark:border-gray-700 divide-x divide-gray-100 dark:divide-gray-700">

            <div class="px-5 py-4">
                <p class="text-xs text-gray-500 dark:text-gray-400">Total Revenue</p>
                <p class="text-lg font-semibold text-primary-600 dark:text-primary-400 mt-0.5">
                    ${{ number_format($data['total_revenue'], 0) }}
                </p>
                <p class="text-xs text-gray-400 mt-0.5">All income sources</p>
            </div>

            <div class="px-5 py-4">
                <p class="text-xs text-gray-500 dark:text-gray-400">Total Expenses</p>
                <p class="text-lg font-semibold text-red-500 dark:text-red-400 mt-0.5">
                    ${{ number_format($data['total_expenses'], 0) }}
                </p>
                <p class="text-xs text-gray-400 mt-0.5">All outgoings</p>
            </div>

            <div class="px-5 py-4 col-span-2 md:col-span-1 {{ $data['cash_collected'] > $profit ? 'bg-amber-50/40 dark:bg-amber-900/10' : '' }}">
                <p class="text-xs text-gray-500 dark:text-gray-400">Cash vs Profit Gap</p>
                @php $gap = $data['cash_collected'] - $profit; @endphp
                <p class="text-lg font-semibold {{ $gap > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-gray-400' }} mt-0.5">
                    ${{ number_format(abs($gap), 0) }}
                    {{ $gap > 0 ? 'more cash than profit' : 'less cash than profit' }}
                </p>
                @if($gap > 0)
                    <p class="text-xs text-amber-600 dark:text-amber-400 mt-0.5">
                        Expenses reduced your profit below cash received
                    </p>
                @endif
            </div>
        </div>
    </div>
</x-filament-widgets::widget>
