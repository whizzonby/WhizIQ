<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Goal Header --}}
        <div class="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700 bg-gradient-to-br from-white to-gray-50 dark:from-gray-800 dark:to-gray-900 shadow-sm">
            <div class="p-6">
                <div class="flex items-center gap-4">
                    <x-filament::icon icon="heroicon-s-flag" class="h-10 w-10 text-primary-600" />
                    <div class="flex-1">
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-1">{{ $record->title }}</h2>
                        <p class="text-base text-gray-600 dark:text-gray-400">{{ $record->progress_percentage }}% complete • {{ $record->days_remaining }} days left</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Check-in Form --}}
        <form wire:submit="submit">
            <div class="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm">
                <div class="p-6">
                    {{ $this->form }}
                </div>
            </div>

            <div class="flex items-center justify-between pt-4">
                <x-filament::button
                    color="gray"
                    tag="a"
                    :href="$this->getResource()::getUrl('view', ['record' => $record])"
                    size="lg"
                >
                    Cancel
                </x-filament::button>

                <x-filament::button type="submit" size="lg">
                    Save Check-in
                </x-filament::button>
            </div>
        </form>
    </div>
</x-filament-panels::page>
