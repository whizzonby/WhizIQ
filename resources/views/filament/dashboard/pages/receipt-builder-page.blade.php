<x-filament-panels::page>
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">

        {{-- ── Left: Form ──────────────────────────────────────────────────── --}}
        <div class="space-y-4">

            {{-- Template toggle --}}
            <div class="flex items-center gap-3 p-3 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Style:</span>

                <button wire:click="$set('template', 'standard')"
                        class="px-3 py-1.5 rounded-lg text-sm font-medium transition-all
                               {{ $template === 'standard'
                                   ? 'bg-emerald-600 text-white shadow'
                                   : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 hover:bg-gray-200' }}">
                    Standard (A4)
                </button>

                <button wire:click="$set('template', 'thermal')"
                        class="px-3 py-1.5 rounded-lg text-sm font-medium transition-all
                               {{ $template === 'thermal'
                                   ? 'bg-emerald-600 text-white shadow'
                                   : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 hover:bg-gray-200' }}">
                    Thermal (POS)
                </button>

                <div class="ml-auto flex items-center gap-2">
                    <label class="text-sm text-gray-600 dark:text-gray-400">Colour:</label>
                    <input type="color"
                           wire:model.live="primaryColor"
                           value="{{ $primaryColor }}"
                           class="w-8 h-8 rounded cursor-pointer border-0 bg-transparent">
                </div>
            </div>

            {{-- Form --}}
            <form wire:submit="save">
                {{ $this->form }}
                <div class="mt-4 flex gap-3">
                    <x-filament::button type="submit" icon="heroicon-o-document-check" color="primary">
                        Save Receipt
                    </x-filament::button>

                    @if($receiptId)
                    <x-filament::button wire:click="downloadPdf" icon="heroicon-o-arrow-down-tray" color="success" type="button">
                        Download PDF
                    </x-filament::button>
                    @endif
                </div>
            </form>
        </div>

        {{-- ── Right: Live Preview ─────────────────────────────────────────── --}}
        <div class="xl:sticky xl:top-6 xl:self-start">
            <div class="bg-gray-100 dark:bg-gray-900 rounded-xl p-4 border border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">
                        Live Preview
                    </h3>
                    <span class="text-xs text-gray-400">
                        {{ $template === 'thermal' ? 'Thermal / POS' : 'Standard (A4)' }}
                    </span>
                </div>

                <div class="overflow-auto" style="max-height:80vh;">
                    @include('filament.dashboard.components.receipt-preview', [
                        'data'         => $this->data,
                        'template'     => $template,
                        'primaryColor' => $primaryColor,
                        'forPdf'       => false,
                    ])
                </div>
            </div>
        </div>

    </div>
</x-filament-panels::page>
