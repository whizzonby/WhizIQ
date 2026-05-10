<x-filament-panels::page>
    <div class="space-y-6">
        {{-- ── Page Header ──────────────────────────────────────────────────── --}}
        <div class="border-b border-gray-200 dark:border-gray-700 pb-6">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-10 h-10 rounded-lg bg-violet-100 dark:bg-violet-900/30 flex items-center justify-center">
                    <x-heroicon-o-document-currency-dollar class="w-6 h-6 text-violet-600 dark:text-violet-400" />
                </div>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Quote Builder</h2>
            </div>
            <p class="text-gray-600 dark:text-gray-400 ml-13">Create professional quotes and estimates with live preview — convert to invoice in one click.</p>
        </div>

        {{-- ── Form + Preview grid ──────────────────────────────────────────── --}}
        <div class="space-y-6">

            {{-- Form --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6">
                {{ $this->form }}
            </div>

            {{-- Action Buttons --}}
            <div class="flex flex-wrap gap-3">
                <x-filament::button
                    wire:click="saveDraft"
                    color="gray"
                    icon="heroicon-o-document"
                    class="flex-1"
                >
                    Save as Draft
                </x-filament::button>

                <x-filament::button
                    wire:click="saveAndSend"
                    color="success"
                    icon="heroicon-o-paper-airplane"
                    class="flex-1"
                >
                    Save & Send
                </x-filament::button>

                @if($quoteId)
                <x-filament::button
                    wire:click="convertToInvoice"
                    color="warning"
                    icon="heroicon-o-document-plus"
                    class="flex-1"
                >
                    Convert to Invoice
                </x-filament::button>
                @endif
            </div>

            {{-- View All Quotes link --}}
            <div class="text-center pt-2 border-t border-gray-200 dark:border-gray-700">
                <a href="{{ route('filament.dashboard.resources.quotes.index') }}"
                   class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 inline-flex items-center gap-2 transition-colors">
                    <x-heroicon-o-arrow-left class="w-4 h-4" />
                    View All Quotes
                </a>
            </div>
        </div>

        {{-- ── Live Preview ──────────────────────────────────────────────────── --}}
        <div>
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl overflow-hidden">
                {{-- Preview Header --}}
                <div class="bg-gradient-to-r from-gray-900 to-gray-700 px-6 py-4 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <x-heroicon-o-eye class="w-5 h-5 text-gray-300" />
                        <span class="text-white font-semibold">Live Preview</span>
                    </div>
                    <span class="px-3 py-1 bg-green-500/20 text-green-400 rounded-full text-xs font-medium">Real-time</span>
                </div>

                {{-- Quote Preview --}}
                <div class="p-8 bg-gray-50 dark:bg-gray-900 min-h-[800px]" id="quote-preview">
                    @include('filament.dashboard.components.quote-preview', [
                        'data'         => $data,
                        'template'     => $template,
                        'primaryColor' => $primaryColor,
                        'accentColor'  => $accentColor,
                    ])
                </div>

                {{-- Preview Footer --}}
                <div class="bg-gray-100 dark:bg-gray-800 px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-600 dark:text-gray-400">
                            Template: <span class="font-semibold">{{ ucfirst($template) }}</span>
                        </span>
                        <div class="flex items-center gap-2">
                            <div class="w-4 h-4 rounded-full border border-gray-300" style="background-color: {{ $primaryColor }}"></div>
                            <div class="w-4 h-4 rounded-full border border-gray-300" style="background-color: {{ $accentColor }}"></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tips --}}
            <div class="mt-6 bg-violet-50 dark:bg-violet-900/20 rounded-lg p-4 border border-violet-200 dark:border-violet-800">
                <div class="flex items-start gap-3">
                    <x-heroicon-o-light-bulb class="w-5 h-5 text-violet-600 dark:text-violet-400 shrink-0 mt-0.5" />
                    <div class="text-sm">
                        <p class="font-semibold text-violet-900 dark:text-violet-100 mb-1">Tips:</p>
                        <ul class="text-violet-700 dark:text-violet-300 space-y-1 list-disc list-inside">
                            <li>Set up your <a href="{{ route('filament.dashboard.pages.company-profile-page') }}" class="underline font-medium">Company Profile</a> to show your logo &amp; address on every document</li>
                            <li>Once the client accepts, use "Convert to Invoice" to create a pre-filled invoice</li>
                            <li>Quote status updates automatically when you send or convert</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        #quote-preview { transition: opacity 0.15s ease-in-out; }
    </style>

    @push('scripts')
    <script>
        document.addEventListener('livewire:initialized', () => {
            Livewire.hook('morph.updated', () => {
                const el = document.getElementById('quote-preview');
                if (el) {
                    el.style.opacity = '0.7';
                    setTimeout(() => el.style.opacity = '1', 150);
                }
            });
        });
    </script>
    @endpush
</x-filament-panels::page>
