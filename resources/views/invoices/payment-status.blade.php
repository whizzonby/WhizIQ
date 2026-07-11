<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice {{ $invoice->invoice_number }} Payment</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-slate-50 text-slate-900">
    <main class="mx-auto flex min-h-screen max-w-xl items-center px-6 py-12">
        <section class="w-full rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            @php
                $isPaid = $invoice->status === 'paid' || (float) $invoice->balance_due <= 0;
                $title = match (true) {
                    $isPaid => 'Payment received',
                    $status === 'success' => 'Payment processing',
                    $status === 'cancelled' => 'Payment cancelled',
                    $status === 'unavailable' => 'Payment unavailable',
                    default => 'Invoice payment',
                };
                $message = match (true) {
                    $isPaid => 'Thank you. This invoice is marked as paid.',
                    $status === 'success' => 'Thank you. Your payment is being confirmed and the invoice will update shortly.',
                    $status === 'cancelled' => 'No payment was taken. You can return to the payment link when you are ready.',
                    $status === 'unavailable' => 'Online payment is not available for this invoice right now. Please contact the business for payment options.',
                    default => 'Review this invoice payment status below.',
                };
            @endphp

            <p class="text-sm font-semibold uppercase tracking-wide text-slate-500">Invoice #{{ $invoice->invoice_number }}</p>
            <h1 class="mt-2 text-2xl font-bold">{{ $title }}</h1>
            <p class="mt-3 text-sm leading-6 text-slate-600">{{ $message }}</p>

            <dl class="mt-6 grid gap-3 rounded-lg bg-slate-50 p-4 text-sm">
                <div class="flex items-center justify-between gap-4">
                    <dt class="text-slate-500">Client</dt>
                    <dd class="font-semibold">{{ $invoice->client?->name ?? 'Client' }}</dd>
                </div>
                <div class="flex items-center justify-between gap-4">
                    <dt class="text-slate-500">Total</dt>
                    <dd class="font-semibold">{{ $invoice->currency }} {{ number_format((float) $invoice->total_amount, 2) }}</dd>
                </div>
                <div class="flex items-center justify-between gap-4">
                    <dt class="text-slate-500">Balance due</dt>
                    <dd class="font-semibold">{{ $invoice->currency }} {{ number_format((float) $invoice->balance_due, 2) }}</dd>
                </div>
            </dl>

            @if(! $isPaid && $status === 'cancelled')
                <a href="{{ app(\App\Services\ClientInvoicePaymentService::class)->signedPaymentUrl($invoice) }}"
                   class="mt-6 inline-flex w-full items-center justify-center rounded-lg bg-blue-600 px-4 py-3 text-sm font-semibold text-white hover:bg-blue-700">
                    Try payment again
                </a>
            @endif
        </section>
    </main>
</body>
</html>
