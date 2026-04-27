<x-filament-widgets::widget>
    <div class="rounded-2xl border border-primary-200 dark:border-primary-800 bg-gradient-to-r from-primary-50 to-white dark:from-primary-950 dark:to-gray-900 p-6">
        <div class="flex items-start justify-between mb-4">
            <div>
                <h2 class="text-lg font-bold text-gray-900 dark:text-white">Welcome — let's get you set up!</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Complete these quick steps to start running your business from WhizIQ.</p>
            </div>
            {{-- This banner auto-hides after 7 days, no dismiss button needed --}}
        </div>

        <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
            @foreach ($this->getSteps() as $step)
            <a href="{{ $step['url'] }}"
               class="group flex flex-col items-center gap-2 rounded-xl border border-gray-200 dark:border-gray-700 p-4 text-center hover:border-primary-400 hover:shadow-sm transition-all {{ $step['bg'] }} dark:bg-white/5">
                <div class="w-10 h-10 rounded-full flex items-center justify-center bg-white dark:bg-gray-800 shadow-sm">
                    <x-filament::icon
                        :icon="$step['icon']"
                        class="w-5 h-5 {{ $step['color'] }}"
                    />
                </div>
                <span class="text-xs font-medium text-gray-700 dark:text-gray-300 leading-tight group-hover:text-primary-700 dark:group-hover:text-primary-400">
                    {{ $step['label'] }}
                </span>
            </a>
            @endforeach
        </div>
    </div>
</x-filament-widgets::widget>
