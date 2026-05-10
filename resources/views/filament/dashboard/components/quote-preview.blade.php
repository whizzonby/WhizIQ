@php
    // ── Data resolution ─────────────────────────────────────────────────────────
    // Supports both a persisted Quote model and a raw $data array (live preview).
    if (isset($quote) && $quote instanceof \App\Models\Quote) {
        $quoteNumber    = $quote->quote_number;
        $quoteDate      = $quote->quote_date?->format('M d, Y') ?? date('M d, Y');
        $validUntil     = $quote->valid_until?->format('M d, Y') ?? '';
        $currency       = $quote->currency ?? 'USD';
        $taxRate        = (float) ($quote->tax_rate ?? 0);
        $discountAmount = (float) ($quote->discount_amount ?? 0);
        $items          = $quote->items->toArray();
        $notes          = $quote->notes ?? '';
        $terms          = $quote->terms ?? '';
        $footer         = $quote->footer ?? '';

        $clientName    = $quote->client?->name ?? '';
        $clientEmail   = $quote->client?->email ?? '';
        $clientCompany = $quote->client?->company ?? '';

        $company = $quote->user?->companyProfile;
    } else {
        // Live-preview mode — $data array from the Livewire form
        $quoteNumber    = $data['quote_number'] ?? 'QUO-2026-00001';
        $quoteDate      = isset($data['quote_date']) ? \Carbon\Carbon::parse($data['quote_date'])->format('M d, Y') : date('M d, Y');
        $validUntil     = !empty($data['valid_until']) ? \Carbon\Carbon::parse($data['valid_until'])->format('M d, Y') : '';
        $currency       = $data['currency'] ?? 'USD';
        $taxRate        = (float) ($data['tax_rate'] ?? 0);
        $discountAmount = (float) ($data['discount_amount'] ?? 0);
        $items          = $data['items'] ?? [];
        $notes          = $data['notes'] ?? '';
        $terms          = $data['terms'] ?? '';
        $footer         = $data['footer'] ?? '';

        $clientName    = '';
        $clientEmail   = '';
        $clientCompany = '';

        if (!empty($data['invoice_client_id'])) {
            $client = \App\Models\InvoiceClient::find($data['invoice_client_id']);
            if ($client) {
                $clientName    = $client->name;
                $clientEmail   = $client->email ?? '';
                $clientCompany = $client->company ?? '';
            }
        }

        $company = auth()->user()?->companyProfile;
    }

    // ── Company info ─────────────────────────────────────────────────────────────
    $companyName    = $company?->company_name ?? auth()->user()?->name ?? 'Your Company';
    $companyTagline = $company?->tagline ?? '';
    $companyAddr1   = $company?->address_line1 ?? '';
    $companyAddr2   = $company?->address_line2 ?? '';
    $companyCity    = trim(implode(', ', array_filter([$company?->city, $company?->state, $company?->zip])));
    $companyCountry = $company?->country ?? '';
    $companyPhone   = $company?->phone ?? '';
    $companyEmail   = $company?->email ?? auth()->user()?->email ?? '';
    $companyWebsite = $company?->website ?? '';
    $logoUrl        = $company?->logo_url;

    // ── Totals ───────────────────────────────────────────────────────────────────
    $subtotal   = collect($items)->sum(fn($i) => (float)($i['amount'] ?? 0));
    $discounted = $subtotal - $discountAmount;
    $taxAmount  = ($discounted * $taxRate) / 100;
    $total      = $discounted + $taxAmount;

    // ── Template colours ─────────────────────────────────────────────────────────
    $colors = [
        'modern'  => ['primary' => '#3b82f6', 'accent' => '#60a5fa'],
        'elegant' => ['primary' => '#9333ea', 'accent' => '#a855f7'],
        'minimal' => ['primary' => '#64748b', 'accent' => '#94a3b8'],
        'vibrant' => ['primary' => '#10b981', 'accent' => '#34d399'],
    ];
    $templateColors = $colors[$template ?? 'modern'] ?? $colors['modern'];
    $pc = $primaryColor ?? $templateColors['primary'];
    $ac = $accentColor  ?? $templateColors['accent'];
@endphp

<div class="bg-white rounded-lg shadow-xl overflow-hidden" style="min-height:700px; max-width:800px; margin:0 auto;">

    {{-- ── Header ────────────────────────────────────────────────────────────── --}}
    <div class="relative overflow-hidden" style="background: linear-gradient(135deg, {{ $pc }} 0%, {{ $ac }} 100%);">
        <div class="absolute inset-0 opacity-10">
            <svg class="w-full h-full" xmlns="http://www.w3.org/2000/svg">
                <defs>
                    <pattern id="qgrid" width="40" height="40" patternUnits="userSpaceOnUse">
                        <path d="M 40 0 L 0 0 0 40" fill="none" stroke="white" stroke-width="1"/>
                    </pattern>
                </defs>
                <rect width="100%" height="100%" fill="url(#qgrid)"/>
            </svg>
        </div>

        <div class="relative px-8 py-10">
            <div class="flex justify-between items-start">
                {{-- Company branding --}}
                <div>
                    @if($logoUrl)
                        <img src="{{ $logoUrl }}" alt="{{ $companyName }}"
                             class="h-14 max-w-xs object-contain mb-3 rounded"
                             style="filter: brightness(0) invert(1); opacity:.9;">
                    @else
                        <div class="flex items-center gap-3 mb-2">
                            <div class="w-12 h-12 rounded-full bg-white/20 flex items-center justify-center">
                                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-white font-bold text-xl leading-tight">{{ $companyName }}</p>
                                @if($companyTagline)<p class="text-white/70 text-xs">{{ $companyTagline }}</p>@endif
                            </div>
                        </div>
                    @endif

                    {{-- QUOTE label --}}
                    <div class="mt-2">
                        <h1 class="text-4xl font-bold text-white tracking-wider">QUOTE</h1>
                        <p class="text-white/70 text-xs mt-0.5 uppercase tracking-widest">/ Estimate</p>
                    </div>
                </div>

                {{-- Quote number + dates block --}}
                <div class="text-right space-y-2">
                    <div class="bg-white/20 backdrop-blur-sm rounded-lg px-4 py-2 border border-white/30 inline-block">
                        <p class="text-white/70 text-xs uppercase tracking-wide mb-1">Quote No.</p>
                        <p class="text-white font-bold text-xl">#{{ $quoteNumber }}</p>
                    </div>
                    <div class="text-white/80 text-sm space-y-1">
                        <div>Date: <span class="font-semibold">{{ $quoteDate }}</span></div>
                        @if($validUntil)
                        <div>Valid Until: <span class="font-semibold">{{ $validUntil }}</span></div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="px-8 py-8">

        {{-- ── From / To ──────────────────────────────────────────────────────── --}}
        <div class="grid grid-cols-2 gap-8 mb-8">

            {{-- From (company) --}}
            <div>
                <p class="text-xs uppercase tracking-wider text-gray-500 mb-3 font-semibold">From</p>
                <div class="bg-gray-50 rounded-lg p-4 border border-gray-200 space-y-0.5">
                    @if($logoUrl)
                        <img src="{{ $logoUrl }}" alt="{{ $companyName }}" class="h-8 object-contain mb-2">
                    @endif
                    <p class="font-bold text-gray-900">{{ $companyName }}</p>
                    @if($companyTagline)<p class="text-gray-500 text-xs italic">{{ $companyTagline }}</p>@endif
                    @if($companyAddr1)<p class="text-gray-600 text-sm">{{ $companyAddr1 }}</p>@endif
                    @if($companyAddr2)<p class="text-gray-600 text-sm">{{ $companyAddr2 }}</p>@endif
                    @if($companyCity)<p class="text-gray-600 text-sm">{{ $companyCity }}</p>@endif
                    @if($companyCountry)<p class="text-gray-600 text-sm">{{ $companyCountry }}</p>@endif
                    @if($companyPhone)<p class="text-gray-600 text-sm">{{ $companyPhone }}</p>@endif
                    @if($companyEmail)<p class="text-gray-600 text-sm">{{ $companyEmail }}</p>@endif
                    @if($companyWebsite)<p class="text-gray-600 text-sm">{{ $companyWebsite }}</p>@endif
                </div>
            </div>

            {{-- To (client) --}}
            <div>
                <p class="text-xs uppercase tracking-wider text-gray-500 mb-3 font-semibold">Prepared For</p>
                <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                    @if($clientName)
                        <p class="font-semibold text-gray-900 text-lg mb-1">{{ $clientName }}</p>
                        @if($clientEmail)<p class="text-gray-600 text-sm">{{ $clientEmail }}</p>@endif
                        @if($clientCompany)<p class="text-gray-600 text-sm">{{ $clientCompany }}</p>@endif
                    @else
                        <p class="text-gray-400 italic text-sm">Select a client to display their details here.</p>
                    @endif
                </div>

                {{-- Quote meta --}}
                <div class="mt-4 space-y-2">
                    <div class="flex justify-between py-2 border-b border-gray-200">
                        <span class="text-gray-600 font-medium text-sm">Currency:</span>
                        <span class="text-gray-900 font-semibold text-sm">{{ $currency }}</span>
                    </div>
                    @if($validUntil)
                    <div class="flex justify-between py-2">
                        <span class="text-gray-600 font-medium text-sm">Expiry:</span>
                        <span class="text-gray-900 font-semibold text-sm">{{ $validUntil }}</span>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- ── Items Table ─────────────────────────────────────────────────────── --}}
        <div class="mb-8">
            <div class="overflow-hidden rounded-lg border border-gray-200">
                <table class="w-full">
                    <thead>
                        <tr style="background-color: {{ $pc }};">
                            <th class="text-left py-3 px-4 text-white font-semibold text-sm">Description</th>
                            <th class="text-right py-3 px-4 text-white font-semibold text-sm w-24">Qty</th>
                            <th class="text-right py-3 px-4 text-white font-semibold text-sm w-32">Rate</th>
                            <th class="text-right py-3 px-4 text-white font-semibold text-sm w-32">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $index => $item)
                            @php $isEven = ($loop->index % 2 === 0); @endphp
                            <tr class="border-b border-gray-200 {{ $isEven ? 'bg-white' : 'bg-gray-50' }}">
                                <td class="py-4 px-4">
                                    <p class="font-medium text-gray-900">{{ $item['description'] ?? '' }}</p>
                                    @if(!empty($item['details']))
                                        <p class="text-sm text-gray-500 mt-1">{{ $item['details'] }}</p>
                                    @endif
                                </td>
                                <td class="text-right py-4 px-4 text-gray-700">{{ number_format((float)($item['quantity'] ?? 1), 2) }}</td>
                                <td class="text-right py-4 px-4 text-gray-700">{{ $currency }} {{ number_format((float)($item['unit_price'] ?? 0), 2) }}</td>
                                <td class="text-right py-4 px-4 font-semibold text-gray-900">{{ $currency }} {{ number_format((float)($item['amount'] ?? 0), 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-8 px-4 text-center text-gray-400">
                                    <p class="font-medium">No items yet</p>
                                    <p class="text-sm mt-1">Add items to see them here</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ── Totals ───────────────────────────────────────────────────────────── --}}
        <div class="flex justify-end mb-8">
            <div class="w-full max-w-sm">
                <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
                    <div class="space-y-3">
                        <div class="flex justify-between text-gray-700">
                            <span class="font-medium">Subtotal:</span>
                            <span class="font-semibold">{{ $currency }} {{ number_format($subtotal, 2) }}</span>
                        </div>
                        @if($discountAmount > 0)
                        <div class="flex justify-between text-green-600">
                            <span class="font-medium">Discount:</span>
                            <span class="font-semibold">−{{ $currency }} {{ number_format($discountAmount, 2) }}</span>
                        </div>
                        @endif
                        @if($taxRate > 0)
                        <div class="flex justify-between text-gray-700">
                            <span class="font-medium">Tax ({{ number_format($taxRate, 1) }}%):</span>
                            <span class="font-semibold">{{ $currency }} {{ number_format($taxAmount, 2) }}</span>
                        </div>
                        @endif
                        <div class="border-t-2 border-gray-300 pt-3">
                            <div class="flex justify-between items-center">
                                <span class="text-lg font-bold text-gray-900">Quote Total:</span>
                                <span class="text-3xl font-bold" style="color: {{ $pc }};">
                                    {{ $currency }} {{ number_format($total, 2) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Notes & Terms ────────────────────────────────────────────────────── --}}
        @if($notes || $terms)
        <div class="grid grid-cols-1 gap-4 mb-8">
            @if($notes)
            <div class="bg-blue-50 border-l-4 rounded-r-lg p-4" style="border-color: {{ $pc }};">
                <p class="text-xs uppercase tracking-wider text-gray-600 mb-2 font-semibold">Notes</p>
                <p class="text-gray-700 text-sm leading-relaxed">{{ $notes }}</p>
            </div>
            @endif
            @if($terms)
            <div class="bg-amber-50 border-l-4 border-amber-500 rounded-r-lg p-4">
                <p class="text-xs uppercase tracking-wider text-gray-600 mb-2 font-semibold">Terms & Conditions</p>
                <p class="text-gray-700 text-sm leading-relaxed">{{ $terms }}</p>
            </div>
            @endif
        </div>
        @endif

        {{-- ── Footer ───────────────────────────────────────────────────────────── --}}
        <div class="text-center pt-8 border-t-2 border-gray-200">
            @if($footer)
                <p class="text-gray-600 text-sm">{{ $footer }}</p>
            @else
                <p class="text-gray-600 text-sm">Thank you for considering our services!</p>
            @endif
            <p class="text-gray-400 text-xs mt-2">Generated with WhizIQ Quote Builder</p>
        </div>
    </div>
</div>
