<x-filament-panels::page>
    <div class="space-y-6">

        {{-- ── Form ──────────────────────────────────────────────────────────── --}}
        <div class="space-y-6">

            {{-- Template + colour bar --}}
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

            {{-- Form fields --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6">
                <form wire:submit="save">
                    {{ $this->form }}

                    <div class="mt-6 flex gap-3">
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
        </div>

        {{-- ── Live Preview ───────────────────────────────────────────────────── --}}
        <div>
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl overflow-hidden">

                {{-- Preview header --}}
                <div class="bg-gradient-to-r from-gray-900 to-gray-700 px-6 py-4 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <x-heroicon-o-eye class="w-5 h-5 text-gray-300" />
                        <span class="text-white font-semibold">Live Preview</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="text-xs text-gray-300">
                            {{ $template === 'thermal' ? 'Thermal / POS' : 'Standard (A4)' }}
                        </span>
                        <span class="px-3 py-1 bg-green-500/20 text-green-400 rounded-full text-xs font-medium">
                            Real-time
                        </span>
                    </div>
                </div>

                {{-- Receipt preview --}}
                <div class="p-8 bg-gray-50 dark:bg-gray-900 min-h-[600px]" id="receipt-preview">
                    @include('filament.dashboard.components.receipt-preview', [
                        'data'         => $this->data,
                        'template'     => $template,
                        'primaryColor' => $primaryColor,
                        'forPdf'       => false,
                    ])
                </div>

                {{-- Preview footer --}}
                <div class="bg-gray-100 dark:bg-gray-800 px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-600 dark:text-gray-400">
                            Template: <span class="font-semibold">{{ ucfirst($template) }}</span>
                        </span>
                        <div class="w-4 h-4 rounded-full border border-gray-300" style="background-color: {{ $primaryColor }}"></div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <style>
        #receipt-preview {
            transition: opacity 0.15s ease-in-out;
        }
    </style>
</x-filament-panels::page>
