<div>
@if($show)
<div
    class="fixed inset-0 z-[9999] flex items-center justify-center p-4"
    style="background:rgba(0,0,0,.55);"
    x-data
    x-show="true"
    x-init="document.documentElement.style.overflow='hidden'; document.body.style.overflow='hidden';"
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0 scale-95"
    x-transition:enter-end="opacity-100 scale-100"
    @click.self="return false"
>
    <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-2xl w-full max-w-4xl flex flex-col ltr" style="max-height:92vh;" @click.stop>

        {{-- Header --}}
        <div class="flex items-center gap-3 px-6 pt-5 pb-4 border-b border-gray-100 dark:border-gray-700 shrink-0">
            <div class="w-9 h-9 rounded-xl bg-indigo-100 dark:bg-indigo-900 flex items-center justify-center shrink-0">
                <x-filament::icon icon="heroicon-o-sparkles" class="w-5 h-5 text-indigo-600 dark:text-indigo-400" />
            </div>
            <div>
                <p class="text-[10px] font-semibold tracking-widest uppercase text-gray-400 dark:text-gray-500 leading-none mb-0.5">Welcome to</p>
                <p class="text-lg font-bold text-gray-900 dark:text-white leading-none">{{ config('app.name') }}</p>
            </div>
        </div>

        {{-- Body --}}
        <div class="overflow-y-auto flex-1 px-6 py-4">

            {{-- Info banner --}}
            <div class="flex gap-3 bg-indigo-50 dark:bg-indigo-950 border-l-4 border-indigo-400 rounded-lg px-4 py-3 mb-4 text-sm text-gray-700 dark:text-gray-300">
                <x-filament::icon icon="heroicon-o-information-circle" class="w-5 h-5 text-indigo-500 shrink-0 mt-0.5" />
                <span>The automations need at least <strong>14 days of data</strong> to become fully actionable. Pick how you'd like to get there.</span>
            </div>

            {{-- Two-column layout --}}
            <div class="grid grid-cols-2 gap-5">

                {{-- Left column --}}
                <div class="flex flex-col gap-4">

                    {{-- Option selector --}}
                    <div>
                        <p class="text-[10px] font-bold tracking-widest uppercase text-gray-400 dark:text-gray-500 mb-2">How would you like to add your data?</p>
                        <div class="flex flex-col gap-2">

                            <button wire:click="select('backfill')"
                                class="w-full text-left flex items-center gap-3 rounded-xl border-2 px-3 py-2.5 transition-all
                                    {{ $selected === 'backfill' ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-950' : 'border-gray-200 dark:border-gray-700 hover:border-indigo-300 dark:hover:border-indigo-600' }}">
                                <span class="w-8 h-8 rounded-full bg-emerald-100 dark:bg-emerald-900 flex items-center justify-center shrink-0">
                                    <x-filament::icon icon="heroicon-o-clock" class="w-4 h-4 text-emerald-600 dark:text-emerald-400" />
                                </span>
                                <div class="flex-1 min-w-0">
                                    <p class="font-semibold text-sm text-gray-900 dark:text-white">Backfill from a prior year</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Historical records for richer insights</p>
                                </div>
                                <x-filament::icon icon="heroicon-o-information-circle" class="w-4 h-4 text-gray-300 dark:text-gray-600 shrink-0" />
                            </button>

                            <button wire:click="select('last14')"
                                class="w-full text-left flex items-center gap-3 rounded-xl border-2 px-3 py-2.5 transition-all
                                    {{ $selected === 'last14' ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-950' : 'border-gray-200 dark:border-gray-700 hover:border-indigo-300 dark:hover:border-indigo-600' }}">
                                <span class="w-8 h-8 rounded-full bg-blue-100 dark:bg-blue-900 flex items-center justify-center shrink-0">
                                    <x-filament::icon icon="heroicon-o-calendar-days" class="w-4 h-4 text-blue-600 dark:text-blue-400" />
                                </span>
                                <div class="flex-1 min-w-0">
                                    <p class="font-semibold text-sm text-gray-900 dark:text-white">Use the last 14 days</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Recent transactions, threshold fast</p>
                                </div>
                                <x-filament::icon icon="heroicon-o-information-circle" class="w-4 h-4 text-gray-300 dark:text-gray-600 shrink-0" />
                            </button>

                            <button wire:click="select('fresh')"
                                class="w-full text-left flex items-center gap-3 rounded-xl border-2 px-3 py-2.5 transition-all
                                    {{ $selected === 'fresh' ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-950' : 'border-gray-200 dark:border-gray-700 hover:border-indigo-300 dark:hover:border-indigo-600' }}">
                                <span class="w-8 h-8 rounded-full bg-orange-100 dark:bg-orange-900 flex items-center justify-center shrink-0">
                                    <x-filament::icon icon="heroicon-o-play" class="w-4 h-4 text-orange-500 dark:text-orange-400" />
                                </span>
                                <div class="flex-1 min-w-0">
                                    <p class="font-semibold text-sm text-gray-900 dark:text-white">Start fresh going forward</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Automations activate after 14 days</p>
                                </div>
                                <x-filament::icon icon="heroicon-o-information-circle" class="w-4 h-4 text-gray-300 dark:text-gray-600 shrink-0" />
                            </button>

                        </div>
                    </div>

                    {{-- Jump To --}}
                    <div>
                        <p class="text-[10px] font-bold tracking-widest uppercase text-gray-400 dark:text-gray-500 mb-2">Jump to</p>
                        <div class="grid grid-cols-2 gap-2">
                            <a href="{{ route('filament.dashboard.resources.expenses.index') }}"
                               class="flex items-center justify-between gap-1 rounded-xl border border-gray-200 dark:border-gray-700 px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                                <div class="flex items-center gap-1.5 min-w-0">
                                    <x-filament::icon icon="heroicon-o-receipt-percent" class="w-4 h-4 text-gray-400 shrink-0" />
                                    <span class="truncate">Expenses</span>
                                </div>
                                <x-filament::icon icon="heroicon-o-arrow-right" class="w-3.5 h-3.5 text-gray-400 shrink-0" />
                            </a>
                            <a href="{{ route('filament.dashboard.resources.revenue-sources.index') }}"
                               class="flex items-center justify-between gap-1 rounded-xl border border-gray-200 dark:border-gray-700 px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                                <div class="flex items-center gap-1.5 min-w-0">
                                    <x-filament::icon icon="heroicon-o-arrow-trending-up" class="w-4 h-4 text-gray-400 shrink-0" />
                                    <span class="truncate">Revenues</span>
                                </div>
                                <x-filament::icon icon="heroicon-o-arrow-right" class="w-3.5 h-3.5 text-gray-400 shrink-0" />
                            </a>
                            <a href="{{ route('filament.dashboard.pages.invoice-builder-page') }}"
                               class="flex items-center justify-between gap-1 rounded-xl border border-gray-200 dark:border-gray-700 px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                                <div class="flex items-center gap-1.5 min-w-0">
                                    <x-filament::icon icon="heroicon-o-document-text" class="w-4 h-4 text-gray-400 shrink-0" />
                                    <span class="truncate">Invoices &amp; receipts</span>
                                </div>
                                <x-filament::icon icon="heroicon-o-arrow-right" class="w-3.5 h-3.5 text-gray-400 shrink-0" />
                            </a>
                            <a href="{{ route('filament.dashboard.pages.booking-settings-page') }}"
                               class="flex items-center justify-between gap-1 rounded-xl border border-gray-200 dark:border-gray-700 px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                                <div class="flex items-center gap-1.5 min-w-0">
                                    <x-filament::icon icon="heroicon-o-calendar" class="w-4 h-4 text-gray-400 shrink-0" />
                                    <span class="truncate">Booking page</span>
                                </div>
                                <x-filament::icon icon="heroicon-o-arrow-right" class="w-3.5 h-3.5 text-gray-400 shrink-0" />
                            </a>
                        </div>
                    </div>

                </div>

                {{-- Right column: all three panels always visible --}}
                <div>
                    <p class="text-[10px] font-bold tracking-widest uppercase text-gray-400 dark:text-gray-500 mb-2">How to add data</p>
                    <div class="flex flex-col gap-3">

                        {{-- Backfill --}}
                        <div class="rounded-xl border border-gray-200 dark:border-gray-700 p-3.5">
                            <div class="flex items-center gap-2 mb-2">
                                <x-filament::icon icon="heroicon-o-information-circle" class="w-4 h-4 text-amber-500 shrink-0" />
                                <p class="font-semibold text-sm text-amber-600 dark:text-amber-400">Backfill from a prior year</p>
                            </div>
                            <ul class="space-y-1.5 text-sm text-gray-600 dark:text-gray-400">
                                <li class="flex items-start gap-2">
                                    <x-filament::icon icon="heroicon-o-table-cells" class="w-4 h-4 text-gray-400 shrink-0 mt-0.5" />
                                    <span>Import a <strong class="text-gray-800 dark:text-gray-200">CSV file</strong> from your accounting software or spreadsheets</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <x-filament::icon icon="heroicon-o-pencil" class="w-4 h-4 text-gray-400 shrink-0 mt-0.5" />
                                    <span><strong class="text-gray-800 dark:text-gray-200">Manual entry</strong> for individual past transactions</span>
                                </li>
                            </ul>
                        </div>

                        {{-- Last 14 days --}}
                        <div class="rounded-xl border border-gray-200 dark:border-gray-700 p-3.5">
                            <div class="flex items-center gap-2 mb-2">
                                <x-filament::icon icon="heroicon-o-information-circle" class="w-4 h-4 text-teal-500 shrink-0" />
                                <p class="font-semibold text-sm text-teal-600 dark:text-teal-400">Use the last 14 days</p>
                            </div>
                            <ul class="space-y-1.5 text-sm text-gray-600 dark:text-gray-400">
                                <li class="flex items-start gap-2">
                                    <x-filament::icon icon="heroicon-o-table-cells" class="w-4 h-4 text-gray-400 shrink-0 mt-0.5" />
                                    <span>Import a <strong class="text-gray-800 dark:text-gray-200">CSV</strong> exported from your bank or app</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <x-filament::icon icon="heroicon-o-pencil" class="w-4 h-4 text-gray-400 shrink-0 mt-0.5" />
                                    <span><strong class="text-gray-800 dark:text-gray-200">Manual entry</strong> — add each transaction one by one</span>
                                </li>
                            </ul>
                        </div>

                        {{-- Fresh --}}
                        <div class="rounded-xl border border-gray-200 dark:border-gray-700 p-3.5">
                            <div class="flex items-center gap-2 mb-2">
                                <x-filament::icon icon="heroicon-o-information-circle" class="w-4 h-4 text-orange-500 shrink-0" />
                                <p class="font-semibold text-sm text-orange-600 dark:text-orange-400">Start fresh going forward</p>
                            </div>
                            <ul class="space-y-1.5 text-sm text-gray-600 dark:text-gray-400">
                                <li class="flex items-start gap-2">
                                    <x-filament::icon icon="heroicon-o-pencil" class="w-4 h-4 text-gray-400 shrink-0 mt-0.5" />
                                    <span><strong class="text-gray-800 dark:text-gray-200">Manual entry</strong> — log new transactions as they happen</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <x-filament::icon icon="heroicon-o-table-cells" class="w-4 h-4 text-gray-400 shrink-0 mt-0.5" />
                                    <span><strong class="text-gray-800 dark:text-gray-200">CSV import</strong> anytime to speed things up</span>
                                </li>
                            </ul>
                        </div>

                    </div>
                </div>

            </div>
        </div>

        {{-- Footer --}}
        <div class="flex items-center justify-between px-6 py-4 border-t border-gray-100 dark:border-gray-700 shrink-0">
            <button wire:click="skip"
                    @click="document.documentElement.style.overflow=''; document.body.style.overflow='';"
                    class="text-sm text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                Skip for now
            </button>
            <button wire:click="getStarted"
                    @click="document.documentElement.style.overflow=''; document.body.style.overflow='';"
                    class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-sm px-5 py-2.5 transition-colors">
                Get started
                <x-filament::icon icon="heroicon-o-arrow-right" class="w-4 h-4" />
            </button>
        </div>

    </div>
</div>
@endif
</div>
