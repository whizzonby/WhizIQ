<x-filament-panels::page>
    <div class="space-y-6">
        <div class="border-b border-gray-200 dark:border-gray-700 pb-6">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-10 h-10 rounded-lg bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center">
                    <x-heroicon-o-building-office class="w-6 h-6 text-indigo-600 dark:text-indigo-400" />
                </div>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Company Profile</h2>
            </div>
            <p class="text-gray-600 dark:text-gray-400 ml-13">Your logo and address will automatically appear on all invoices, quotes, and receipts.</p>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6">
            <form wire:submit="save">
                {{ $this->form }}

                <div class="mt-6">
                    <x-filament::button type="submit" icon="heroicon-o-check-circle" color="primary">
                        Save Company Profile
                    </x-filament::button>
                </div>
            </form>
        </div>
    </div>
</x-filament-panels::page>
