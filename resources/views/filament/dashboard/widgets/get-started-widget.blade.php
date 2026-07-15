<x-filament-widgets::widget>
    <div class="rounded-lg border border-primary-200 bg-white p-6 shadow-sm dark:border-primary-800 dark:bg-gray-900 dark:text-gray-100">
        <div class="mb-4 flex flex-col gap-1 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h2 class="text-base font-semibold text-gray-950 dark:text-white">Launch your booking flow</h2>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    Complete these essentials so clients can book you and you can start operating from WhizIQ.
                </p>
            </div>
        </div>

        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($this->getSteps() as $step)
                <a href="{{ $step['url'] }}"
                   class="group flex items-center gap-3 rounded-lg border p-4 transition hover:border-primary-400 hover:shadow-sm {{ $step['complete'] ? 'border-success-200 bg-success-50 dark:border-success-800 dark:bg-success-950/20' : 'border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-800' }}">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-white shadow-sm dark:bg-gray-900">
                        <x-filament::icon
                            :icon="$step['complete'] ? 'heroicon-o-check-circle' : $step['icon']"
                            class="h-5 w-5 {{ $step['complete'] ? 'text-success-600 dark:text-success-400' : 'text-primary-600 dark:text-primary-400' }}"
                        />
                    </div>

                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-gray-950 group-hover:text-primary-700 dark:text-white dark:group-hover:text-primary-300">
                            {{ $step['label'] }}
                        </p>
                        <p class="mt-0.5 text-xs font-medium text-gray-600 dark:text-gray-300">
                            {{ $step['complete'] ? 'Done' : 'Set up now' }}
                        </p>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
</x-filament-widgets::widget>
