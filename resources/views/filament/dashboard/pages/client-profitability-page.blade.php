<x-filament-panels::page>
    @php
        $stats = $this->getSummaryStats();
    @endphp

    {{-- Summary stat cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 shadow-sm">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Total Clients Invoiced</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ $stats['total_clients'] }}</p>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 shadow-sm">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Total Invoiced</p>
            <p class="text-2xl font-bold text-primary-600 dark:text-primary-400 mt-1">${{ number_format($stats['total_invoiced'], 0) }}</p>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 shadow-sm">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Total Collected</p>
            <p class="text-2xl font-bold text-green-600 dark:text-green-400 mt-1">${{ number_format($stats['total_collected'], 0) }}</p>
            <p class="text-xs text-gray-500 mt-1">{{ $stats['collection_rate'] }}% collection rate</p>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 shadow-sm">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Outstanding</p>
            <p class="text-2xl font-bold {{ $stats['total_outstanding'] > 0 ? 'text-red-600 dark:text-red-400' : 'text-gray-400' }} mt-1">
                ${{ number_format($stats['total_outstanding'], 0) }}
            </p>
            @if($stats['total_outstanding'] > 0)
                <p class="text-xs text-red-500 mt-1">Awaiting payment</p>
            @endif
        </div>
    </div>

    {{-- Table --}}
    {{ $this->table }}
</x-filament-panels::page>
