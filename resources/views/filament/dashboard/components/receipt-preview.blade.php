@php
    // ── Data resolution ─────────────────────────────────────────────────────────
    // Supports both a persisted Receipt model and a raw $data array (live preview).
    if (isset($receipt) && $receipt instanceof \App\Models\Receipt) {
        $receiptNumber  = $receipt->receipt_number;
        $receiptDate    = $receipt->receipt_date?->format('M d, Y') ?? date('M d, Y');
        $currency       = $receipt->currency ?? 'USD';
        $taxRate        = (float) ($receipt->tax_rate ?? 0);
        $discountAmount = (float) ($receipt->discount_amount ?? 0);
        $amountTendered = (float) ($receipt->amount_tendered ?? 0);
        $changeDue      = (float) ($receipt->change_due ?? 0);
        $paymentMethod  = $receipt->payment_method_label ?? ucfirst($receipt->payment_method ?? 'Cash');
        $transactionRef = $receipt->transaction_reference ?? '';
        $notes          = $receipt->notes ?? '';
        $items          = $receipt->items ?? [];

        $customerName    = $receipt->client?->name ?? $receipt->customer_name ?? '';
        $customerEmail   = $receipt->client?->email ?? $receipt->customer_email ?? '';
        $customerPhone   = $receipt->client?->phone ?? $receipt->customer_phone ?? '';

        $businessName    = $receipt->user?->businessProfile?->biz_trading_name
                        ?? $receipt->user?->businessProfile?->biz_registered_name
                        ?? $receipt->user?->name ?? 'Your Business';
        $businessEmail   = $receipt->user?->email ?? '';
    } else {
        // Live-preview mode — $data array from the Livewire form
        $receiptNumber  = $data['receipt_number'] ?? 'REC-00001';
        $receiptDate    = isset($data['receipt_date']) ? \Carbon\Carbon::parse($data['receipt_date'])->format('M d, Y') : date('M d, Y');
        $currency       = $data['currency'] ?? 'USD';
        $taxRate        = (float) ($data['tax_rate'] ?? 0);
        $discountAmount = (float) ($data['discount_amount'] ?? 0);
        $amountTendered = (float) ($data['amount_tendered'] ?? 0);
        $paymentMethod  = match ($data['payment_method'] ?? 'cash') {
            'credit_card'   => 'Credit Card',
            'debit_card'    => 'Debit Card',
            'bank_transfer' => 'Bank Transfer',
            'check'         => 'Check',
            'paypal'        => 'PayPal',
            'stripe'        => 'Stripe',
            'other'         => 'Other',
            default         => 'Cash',
        };
        $transactionRef = $data['transaction_reference'] ?? '';
        $notes          = $data['notes'] ?? '';
        $items          = $data['items'] ?? [];

        $customerName  = '';
        $customerEmail = '';
        $customerPhone = '';

        if (!empty($data['invoice_client_id'])) {
            $client = \App\Models\InvoiceClient::find($data['invoice_client_id']);
            $customerName  = $client?->name ?? '';
            $customerEmail = $client?->email ?? '';
            $customerPhone = $client?->phone ?? '';
        }

        if (empty($customerName)) {
            $customerName  = $data['customer_name'] ?? '';
            $customerEmail = $data['customer_email'] ?? '';
            $customerPhone = $data['customer_phone'] ?? '';
        }

        $user = auth()->user();
        $businessName  = $user?->businessProfile?->biz_trading_name
                      ?? $user?->businessProfile?->biz_registered_name
                      ?? $user?->name ?? 'Your Business';
        $businessEmail = $user?->email ?? '';

        $changeDue = $amountTendered > 0
            ? max(0, $amountTendered - array_sum(array_column($items, 'amount')))
            : 0;
    }

    // ── Totals ───────────────────────────────────────────────────────────────────
    $subtotal  = collect($items)->sum(fn($i) => (float) ($i['amount'] ?? 0));
    $discounted = $subtotal - $discountAmount;
    $taxAmount  = ($discounted * $taxRate) / 100;
    $total      = $discounted + $taxAmount;

    $currencyModel = \App\Models\Currency::where('code', $currency)->first();
    $sym = $currencyModel?->symbol ?: $currency . ' ';

    $fmt = fn($n) => $sym . number_format((float) $n, 2);

    $isThermal = ($template ?? 'standard') === 'thermal';
@endphp

@if($isThermal)
{{-- ╔══════════════════════════════════════════════════════╗ --}}
{{-- ║              THERMAL / POS RECEIPT STYLE             ║ --}}
{{-- ╚══════════════════════════════════════════════════════╝ --}}
<div class="bg-white mx-auto font-mono text-sm" style="max-width:320px; padding:16px;">

    {{-- Header --}}
    <div class="text-center mb-4">
        <div class="w-10 h-10 rounded-full flex items-center justify-center mx-auto mb-2"
             style="background-color:{{ $primaryColor }}">
            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
        </div>
        <div class="font-bold text-lg">{{ strtoupper($businessName) }}</div>
        @if($businessEmail)<div class="text-gray-500 text-xs">{{ $businessEmail }}</div>@endif
        <div class="border-t border-dashed border-gray-400 my-3"></div>
        <div class="font-bold text-base">RECEIPT</div>
        <div class="text-gray-600 text-xs">{{ $receiptNumber }}</div>
        <div class="text-gray-600 text-xs">{{ $receiptDate }}</div>
    </div>

    {{-- Customer --}}
    @if($customerName)
    <div class="border-t border-dashed border-gray-400 py-2 text-xs">
        <div class="font-semibold">Bill To:</div>
        <div>{{ $customerName }}</div>
        @if($customerPhone)<div>{{ $customerPhone }}</div>@endif
    </div>
    @endif

    {{-- Items --}}
    <div class="border-t border-dashed border-gray-400 pt-2 mb-2">
        @foreach($items as $item)
        @if(!empty($item['description']))
        <div class="flex justify-between text-xs py-1">
            <div class="flex-1 pr-2">
                <div>{{ $item['description'] }}</div>
                @if((float)($item['quantity'] ?? 1) != 1)
                <div class="text-gray-500">{{ $item['quantity'] }} × {{ $fmt($item['unit_price'] ?? 0) }}</div>
                @endif
            </div>
            <div class="font-semibold whitespace-nowrap">{{ $fmt($item['amount'] ?? 0) }}</div>
        </div>
        @endif
        @endforeach
    </div>

    {{-- Totals --}}
    <div class="border-t border-dashed border-gray-400 pt-2 text-xs space-y-1">
        <div class="flex justify-between"><span>Subtotal</span><span>{{ $fmt($subtotal) }}</span></div>
        @if($discountAmount > 0)
        <div class="flex justify-between text-red-600"><span>Discount</span><span>−{{ $fmt($discountAmount) }}</span></div>
        @endif
        @if($taxRate > 0)
        <div class="flex justify-between"><span>Tax ({{ $taxRate }}%)</span><span>{{ $fmt($taxAmount) }}</span></div>
        @endif
        <div class="flex justify-between font-bold text-sm border-t border-dashed border-gray-400 pt-2 mt-1">
            <span>TOTAL</span><span>{{ $fmt($total) }}</span>
        </div>
        <div class="flex justify-between"><span>{{ $paymentMethod }}</span><span>{{ $fmt($total) }}</span></div>
        @if($amountTendered > 0)
        <div class="flex justify-between"><span>Tendered</span><span>{{ $fmt($amountTendered) }}</span></div>
        <div class="flex justify-between font-semibold"><span>Change</span><span>{{ $fmt($changeDue) }}</span></div>
        @endif
        @if($transactionRef)
        <div class="text-gray-500 text-center pt-1">Ref: {{ $transactionRef }}</div>
        @endif
    </div>

    {{-- Footer --}}
    @if($notes)
    <div class="border-t border-dashed border-gray-400 mt-3 pt-2 text-xs text-center text-gray-600">{{ $notes }}</div>
    @endif
    <div class="text-center text-xs text-gray-500 mt-3">Thank you for your business!</div>
    <div class="text-center text-xs text-gray-400 mt-1">— Powered by WhizIQ —</div>
</div>

@else
{{-- ╔══════════════════════════════════════════════════════╗ --}}
{{-- ║              STANDARD A4 RECEIPT STYLE               ║ --}}
{{-- ╚══════════════════════════════════════════════════════╝ --}}
<div class="bg-white rounded-xl shadow-xl overflow-hidden" style="max-width:680px; margin:0 auto;">

    {{-- Header bar --}}
    <div class="px-8 py-7" style="background:{{ $primaryColor }}">
        <div class="flex items-center justify-between">
            <div>
                <div class="flex items-center gap-3 mb-1">
                    <div class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                    <h1 class="text-3xl font-bold text-white tracking-wide">RECEIPT</h1>
                </div>
                <p class="text-white/80 text-sm">{{ $businessName }}</p>
                @if($businessEmail)<p class="text-white/60 text-xs">{{ $businessEmail }}</p>@endif
            </div>
            <div class="text-right">
                <div class="bg-white/20 rounded-lg px-4 py-3">
                    <p class="text-white/70 text-xs uppercase tracking-wider mb-1">Receipt No.</p>
                    <p class="text-white font-bold text-lg">{{ $receiptNumber }}</p>
                    <p class="text-white/80 text-sm mt-1">{{ $receiptDate }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="px-8 py-6">

        {{-- Customer + Payment info --}}
        <div class="grid grid-cols-2 gap-6 mb-6">
            <div class="bg-gray-50 rounded-lg p-4">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Received From</p>
                @if($customerName)
                <p class="font-semibold text-gray-800">{{ $customerName }}</p>
                @if($customerEmail)<p class="text-sm text-gray-500">{{ $customerEmail }}</p>@endif
                @if($customerPhone)<p class="text-sm text-gray-500">{{ $customerPhone }}</p>@endif
                @else
                <p class="text-gray-400 text-sm italic">Walk-in customer</p>
                @endif
            </div>
            <div class="bg-gray-50 rounded-lg p-4">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Payment Details</p>
                <div class="flex items-center gap-2 mb-1">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold text-white"
                          style="background-color:{{ $primaryColor }}">
                        {{ $paymentMethod }}
                    </span>
                </div>
                @if($transactionRef)
                <p class="text-xs text-gray-500 mt-1">Ref: {{ $transactionRef }}</p>
                @endif
                <p class="text-xs text-gray-500 mt-1">{{ $currency }}</p>
            </div>
        </div>

        {{-- Items table --}}
        <table class="w-full mb-6">
            <thead>
                <tr class="text-xs font-semibold text-gray-500 uppercase tracking-wider border-b-2 border-gray-200">
                    <th class="text-left pb-3">Description</th>
                    <th class="text-center pb-3 px-4">Qty</th>
                    <th class="text-right pb-3 px-4">Unit Price</th>
                    <th class="text-right pb-3">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $item)
                @if(!empty($item['description']))
                <tr class="border-b border-gray-100">
                    <td class="py-3 pr-4">
                        <p class="text-sm font-medium text-gray-800">{{ $item['description'] }}</p>
                        @if(!empty($item['details']))
                        <p class="text-xs text-gray-500 mt-0.5">{{ $item['details'] }}</p>
                        @endif
                    </td>
                    <td class="py-3 px-4 text-center text-sm text-gray-600">{{ $item['quantity'] ?? 1 }}</td>
                    <td class="py-3 px-4 text-right text-sm text-gray-600">{{ $fmt($item['unit_price'] ?? 0) }}</td>
                    <td class="py-3 text-right text-sm font-semibold text-gray-800">{{ $fmt($item['amount'] ?? 0) }}</td>
                </tr>
                @endif
                @endforeach
            </tbody>
        </table>

        {{-- Totals --}}
        <div class="flex justify-end mb-6">
            <div class="w-64">
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between text-gray-600">
                        <span>Subtotal</span><span>{{ $fmt($subtotal) }}</span>
                    </div>
                    @if($discountAmount > 0)
                    <div class="flex justify-between text-red-500">
                        <span>Discount</span><span>−{{ $fmt($discountAmount) }}</span>
                    </div>
                    @endif
                    @if($taxRate > 0)
                    <div class="flex justify-between text-gray-600">
                        <span>Tax ({{ $taxRate }}%)</span><span>{{ $fmt($taxAmount) }}</span>
                    </div>
                    @endif
                    <div class="border-t-2 border-gray-300 pt-2 mt-2">
                        <div class="flex justify-between font-bold text-base text-gray-900">
                            <span>Total Paid</span>
                            <span style="color:{{ $primaryColor }}">{{ $fmt($total) }}</span>
                        </div>
                    </div>
                    @if($amountTendered > 0)
                    <div class="flex justify-between text-gray-600 text-xs">
                        <span>Cash Tendered</span><span>{{ $fmt($amountTendered) }}</span>
                    </div>
                    <div class="flex justify-between font-semibold text-xs">
                        <span>Change Given</span><span>{{ $fmt($changeDue) }}</span>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Paid stamp --}}
        <div class="flex justify-center mb-6">
            <div class="border-4 rounded-lg px-8 py-2 rotate-[-3deg] inline-block"
                 style="border-color:{{ $primaryColor }}; color:{{ $primaryColor }}">
                <span class="text-2xl font-black tracking-widest uppercase">PAID</span>
            </div>
        </div>

        {{-- Notes --}}
        @if($notes)
        <div class="bg-gray-50 rounded-lg p-4 text-sm text-gray-600 border-l-4"
             style="border-color:{{ $primaryColor }}">
            {{ $notes }}
        </div>
        @endif

    </div>

    {{-- Footer --}}
    <div class="px-8 py-4 bg-gray-50 border-t border-gray-200 text-center">
        <p class="text-xs text-gray-400">Thank you for your business · Powered by WhizIQ</p>
    </div>
</div>
@endif
