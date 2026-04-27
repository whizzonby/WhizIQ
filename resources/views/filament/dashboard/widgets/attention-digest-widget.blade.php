<x-filament-widgets::widget>
    @php
        $data = $this->getDigestData();
        $hasItems = $data['total_items'] > 0 || $data['upcoming_appointments'] > 0;
    @endphp

    <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm overflow-hidden">
        {{-- Header --}}
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 dark:border-gray-700">
            <div class="flex items-center gap-2">
                @if($data['total_items'] > 0)
                    <span class="flex h-2.5 w-2.5 relative">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-red-500"></span>
                    </span>
                @else
                    <span class="inline-flex h-2.5 w-2.5 rounded-full bg-green-400"></span>
                @endif
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">What Needs Your Attention</h3>
            </div>
            @if($data['total_items'] > 0)
                <span class="inline-flex items-center rounded-full bg-red-50 dark:bg-red-900/30 px-2.5 py-0.5 text-xs font-semibold text-red-700 dark:text-red-400">
                    {{ $data['total_items'] }} {{ Str::plural('item', $data['total_items']) }}
                </span>
            @else
                <span class="inline-flex items-center rounded-full bg-green-50 dark:bg-green-900/30 px-2.5 py-0.5 text-xs font-semibold text-green-700 dark:text-green-400">
                    All clear
                </span>
            @endif
        </div>

        @if(!$hasItems)
            {{-- All clear state --}}
            <div class="flex flex-col items-center justify-center py-10 text-center px-4">
                <div class="rounded-full bg-green-50 dark:bg-green-900/20 p-3 mb-3">
                    <x-heroicon-o-check-circle class="h-8 w-8 text-green-500" />
                </div>
                <p class="text-sm font-medium text-gray-700 dark:text-gray-300">You're on top of everything</p>
                <p class="text-xs text-gray-400 mt-1">No overdue invoices, follow-ups, or stale deals</p>
            </div>
        @else
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 divide-x divide-gray-100 dark:divide-gray-700">

                {{-- Overdue Invoices --}}
                <a href="{{ route('filament.dashboard.resources.client-invoices.index') }}"
                   class="flex flex-col items-center justify-center p-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors group {{ $data['overdue_invoices_count'] > 0 ? 'bg-red-50/50 dark:bg-red-900/10' : '' }}">
                    <div class="rounded-full p-2 mb-2 {{ $data['overdue_invoices_count'] > 0 ? 'bg-red-100 dark:bg-red-900/30' : 'bg-gray-100 dark:bg-gray-700' }}">
                        <x-heroicon-o-document-text class="h-5 w-5 {{ $data['overdue_invoices_count'] > 0 ? 'text-red-600 dark:text-red-400' : 'text-gray-400' }}" />
                    </div>
                    <span class="text-2xl font-bold {{ $data['overdue_invoices_count'] > 0 ? 'text-red-600 dark:text-red-400' : 'text-gray-400' }}">
                        {{ $data['overdue_invoices_count'] }}
                    </span>
                    <span class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 text-center">Overdue Invoices</span>
                    @if($data['overdue_invoices_count'] > 0)
                        <span class="text-xs font-medium text-red-600 dark:text-red-400 mt-1">${{ number_format($data['overdue_invoices_total'], 0) }}</span>
                    @endif
                </a>

                {{-- Follow-ups Due --}}
                <a href="{{ route('filament.dashboard.resources.contacts.index') }}"
                   class="flex flex-col items-center justify-center p-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors {{ $data['follow_ups_due'] > 0 ? 'bg-orange-50/50 dark:bg-orange-900/10' : '' }}">
                    <div class="rounded-full p-2 mb-2 {{ $data['follow_ups_due'] > 0 ? 'bg-orange-100 dark:bg-orange-900/30' : 'bg-gray-100 dark:bg-gray-700' }}">
                        <x-heroicon-o-phone class="h-5 w-5 {{ $data['follow_ups_due'] > 0 ? 'text-orange-600 dark:text-orange-400' : 'text-gray-400' }}" />
                    </div>
                    <span class="text-2xl font-bold {{ $data['follow_ups_due'] > 0 ? 'text-orange-600 dark:text-orange-400' : 'text-gray-400' }}">
                        {{ $data['follow_ups_due'] }}
                    </span>
                    <span class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 text-center">Follow-ups Due</span>
                </a>

                {{-- Stale Deals --}}
                <a href="{{ route('filament.dashboard.resources.deals.index') }}"
                   class="flex flex-col items-center justify-center p-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors {{ $data['stale_deals'] > 0 ? 'bg-yellow-50/50 dark:bg-yellow-900/10' : '' }}">
                    <div class="rounded-full p-2 mb-2 {{ $data['stale_deals'] > 0 ? 'bg-yellow-100 dark:bg-yellow-900/30' : 'bg-gray-100 dark:bg-gray-700' }}">
                        <x-heroicon-o-briefcase class="h-5 w-5 {{ $data['stale_deals'] > 0 ? 'text-yellow-600 dark:text-yellow-400' : 'text-gray-400' }}" />
                    </div>
                    <span class="text-2xl font-bold {{ $data['stale_deals'] > 0 ? 'text-yellow-600 dark:text-yellow-400' : 'text-gray-400' }}">
                        {{ $data['stale_deals'] }}
                    </span>
                    <span class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 text-center">Stale Deals</span>
                    @if($data['stale_deals'] > 0)
                        <span class="text-xs text-yellow-600 dark:text-yellow-400 mt-1">14+ days no update</span>
                    @endif
                </a>

                {{-- Tasks Due Today --}}
                <a href="{{ route('filament.dashboard.resources.tasks.index') }}"
                   class="flex flex-col items-center justify-center p-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors {{ $data['tasks_due_today'] > 0 ? 'bg-blue-50/50 dark:bg-blue-900/10' : '' }}">
                    <div class="rounded-full p-2 mb-2 {{ $data['tasks_due_today'] > 0 ? 'bg-blue-100 dark:bg-blue-900/30' : 'bg-gray-100 dark:bg-gray-700' }}">
                        <x-heroicon-o-clipboard-document-check class="h-5 w-5 {{ $data['tasks_due_today'] > 0 ? 'text-blue-600 dark:text-blue-400' : 'text-gray-400' }}" />
                    </div>
                    <span class="text-2xl font-bold {{ $data['tasks_due_today'] > 0 ? 'text-blue-600 dark:text-blue-400' : 'text-gray-400' }}">
                        {{ $data['tasks_due_today'] }}
                    </span>
                    <span class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 text-center">Tasks Due Today</span>
                </a>

                {{-- Overdue Tasks --}}
                <a href="{{ route('filament.dashboard.resources.tasks.index') }}"
                   class="flex flex-col items-center justify-center p-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors {{ $data['overdue_tasks'] > 0 ? 'bg-red-50/50 dark:bg-red-900/10' : '' }}">
                    <div class="rounded-full p-2 mb-2 {{ $data['overdue_tasks'] > 0 ? 'bg-red-100 dark:bg-red-900/30' : 'bg-gray-100 dark:bg-gray-700' }}">
                        <x-heroicon-o-exclamation-triangle class="h-5 w-5 {{ $data['overdue_tasks'] > 0 ? 'text-red-600 dark:text-red-400' : 'text-gray-400' }}" />
                    </div>
                    <span class="text-2xl font-bold {{ $data['overdue_tasks'] > 0 ? 'text-red-600 dark:text-red-400' : 'text-gray-400' }}">
                        {{ $data['overdue_tasks'] }}
                    </span>
                    <span class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 text-center">Overdue Tasks</span>
                </a>

                {{-- Upcoming Appointments --}}
                <a href="{{ route('filament.dashboard.pages.appointment-calendar') }}"
                   class="flex flex-col items-center justify-center p-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors {{ $data['upcoming_appointments'] > 0 ? 'bg-purple-50/50 dark:bg-purple-900/10' : '' }}">
                    <div class="rounded-full p-2 mb-2 {{ $data['upcoming_appointments'] > 0 ? 'bg-purple-100 dark:bg-purple-900/30' : 'bg-gray-100 dark:bg-gray-700' }}">
                        <x-heroicon-o-calendar-days class="h-5 w-5 {{ $data['upcoming_appointments'] > 0 ? 'text-purple-600 dark:text-purple-400' : 'text-gray-400' }}" />
                    </div>
                    <span class="text-2xl font-bold {{ $data['upcoming_appointments'] > 0 ? 'text-purple-600 dark:text-purple-400' : 'text-gray-400' }}">
                        {{ $data['upcoming_appointments'] }}
                    </span>
                    <span class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 text-center">Appointments Today</span>
                </a>

            </div>
        @endif
    </div>
</x-filament-widgets::widget>
