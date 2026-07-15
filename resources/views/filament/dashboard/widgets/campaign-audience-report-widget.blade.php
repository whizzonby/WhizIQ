@php
    $report = $this->getReportData();
    $recommended = $report['recommended'] ?? null;
    $audiences = $report['audiences'] ?? [];
    $openSlotSignal = $report['open_slot_signal'] ?? null;
@endphp

<x-filament-widgets::widget>
    <div class="space-y-6">
        @if($recommended)
            <x-filament::section>
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-primary-600 dark:text-primary-400">
                            Recommended next campaign
                        </p>
                        <h2 class="mt-1 text-lg font-semibold text-gray-950 dark:text-white">
                            {{ $recommended['name'] }}
                        </h2>
                        <p class="mt-1 max-w-3xl text-sm text-gray-600 dark:text-gray-300">
                            {{ $recommended['reason'] }}
                        </p>
                    </div>

                    <a href="{{ route('filament.dashboard.pages.email-composer-page', ['audience' => $recommended['slug'] ?? null, 'campaign' => $recommended['campaign_preset'] ?? null]) }}"
                       class="inline-flex items-center justify-center rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-primary-500 dark:bg-primary-500 dark:hover:bg-primary-400">
                        Review campaign
                    </a>
                </div>
            </x-filament::section>
        @endif

        @if($openSlotSignal)
            <x-filament::section>
                <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-950 dark:text-white">Booking availability signal</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-300">
                            {{ $openSlotSignal['slot_count'] ?? 0 }} open slots on {{ $openSlotSignal['label'] ?? 'the next 7 days' }}.
                        </p>
                    </div>
                    <span class="w-fit rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-700 dark:bg-gray-800 dark:text-gray-200">
                        Used by calendar-gap campaigns
                    </span>
                </div>
            </x-filament::section>
        @endif

        <x-filament::section>
            <x-slot name="heading">Audience performance</x-slot>
            <x-slot name="description">Campaign audiences ranked by recommendation score and recent performance.</x-slot>

            <div class="grid gap-4 xl:grid-cols-2">
                @forelse($audiences as $audience)
                    <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-900">
                        <div class="flex items-start justify-between gap-4">
                            <div class="min-w-0">
                                <h3 class="font-semibold text-gray-950 dark:text-white">{{ $audience['name'] }}</h3>
                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">{{ $audience['description'] }}</p>
                            </div>

                            <span class="rounded-lg bg-primary-50 px-2.5 py-1 text-xs font-semibold text-primary-700 dark:bg-primary-950 dark:text-primary-300">
                                Score {{ $audience['score'] ?? 0 }}
                            </span>
                        </div>

                        <div class="mt-4 grid grid-cols-2 gap-3 md:grid-cols-5">
                            @foreach([
                                'Contacts' => $audience['contact_count'] ?? 0,
                                'Sent' => $audience['sent'] ?? 0,
                                'Booked' => $audience['booked'] ?? 0,
                                'Rate' => ($audience['conversion_rate'] ?? 0) . '%',
                                'Collected' => $audience['formatted_collected'] ?? '$0.00',
                            ] as $label => $value)
                                <div class="rounded-lg bg-gray-50 p-3 dark:bg-gray-800">
                                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ $label }}</p>
                                    <p class="mt-1 text-sm font-semibold text-gray-950 dark:text-white">{{ $value }}</p>
                                </div>
                            @endforeach
                        </div>

                        @if(! empty($audience['learning_signals']))
                            <div class="mt-4 flex flex-wrap gap-2">
                                @foreach(array_slice($audience['learning_signals'], 0, 3) as $signal)
                                    <span class="rounded-full bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-200">
                                        {{ ucfirst($signal) }}
                                    </span>
                                @endforeach
                            </div>
                        @endif

                        <div class="mt-4 flex flex-wrap gap-2">
                            <a href="{{ route('filament.dashboard.pages.email-composer-page', ['audience' => $audience['slug'] ?? null, 'campaign' => $audience['campaign_preset'] ?? null]) }}"
                               class="text-sm font-semibold text-primary-600 hover:text-primary-500 dark:text-primary-400 dark:hover:text-primary-300">
                                Open composer
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="rounded-lg border border-dashed border-gray-300 p-6 text-sm text-gray-600 dark:border-gray-700 dark:text-gray-300">
                        No campaign audience data yet.
                    </div>
                @endforelse
            </div>
        </x-filament::section>
    </div>
</x-filament-widgets::widget>
