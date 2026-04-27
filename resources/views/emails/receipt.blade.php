<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt {{ $receipt->receipt_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background: #f4f4f5;
            color: #18181b;
            line-height: 1.5;
            -webkit-font-smoothing: antialiased;
        }

        .wrapper {
            max-width: 600px;
            margin: 32px auto;
            background: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0,0,0,.08), 0 4px 16px rgba(0,0,0,.06);
        }

        /* ── Header ─────────────────────────────── */
        .header {
            background: #6f27e5;
            padding: 36px 40px 32px;
            color: #fff;
        }

        .header-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .business-name {
            font-size: 18px;
            font-weight: 700;
            letter-spacing: -.02em;
        }

        .receipt-badge {
            background: rgba(255,255,255,.15);
            border: 1px solid rgba(255,255,255,.25);
            border-radius: 6px;
            padding: 4px 12px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        .header-title {
            margin-top: 20px;
            font-size: 28px;
            font-weight: 800;
            letter-spacing: -.03em;
        }

        .header-meta {
            margin-top: 6px;
            font-size: 13px;
            opacity: .75;
        }

        /* ── Client block ────────────────────────── */
        .client-block {
            padding: 28px 40px;
            border-bottom: 1px solid #f1f1f1;
            display: flex;
            justify-content: space-between;
            gap: 24px;
        }

        .meta-group label {
            display: block;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: #a1a1aa;
            margin-bottom: 4px;
        }

        .meta-group p {
            font-size: 14px;
            font-weight: 600;
            color: #18181b;
        }

        .meta-group p.light {
            font-weight: 400;
            color: #52525b;
        }

        /* ── Items table ─────────────────────────── */
        .items-section {
            padding: 28px 40px;
        }

        .section-title {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: #a1a1aa;
            margin-bottom: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead th {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .06em;
            color: #a1a1aa;
            padding: 0 0 10px;
            border-bottom: 1px solid #e4e4e7;
        }

        thead th:last-child,
        tbody td:last-child {
            text-align: right;
        }

        thead th:nth-child(2),
        tbody td:nth-child(2) {
            text-align: center;
        }

        tbody td {
            padding: 12px 0;
            font-size: 14px;
            color: #18181b;
            border-bottom: 1px solid #f4f4f5;
            vertical-align: top;
        }

        tbody tr:last-child td {
            border-bottom: none;
        }

        tbody td.desc {
            font-weight: 500;
            padding-right: 16px;
        }

        tbody td.qty,
        tbody td.price {
            color: #52525b;
            white-space: nowrap;
        }

        tbody td.amount {
            font-weight: 700;
            white-space: nowrap;
        }

        /* ── Totals ──────────────────────────────── */
        .totals {
            padding: 0 40px 28px;
        }

        .totals-inner {
            margin-left: auto;
            max-width: 260px;
        }

        .totals-row {
            display: flex;
            justify-content: space-between;
            padding: 6px 0;
            font-size: 13px;
            color: #52525b;
        }

        .totals-row.total {
            border-top: 2px solid #18181b;
            margin-top: 8px;
            padding-top: 12px;
            font-size: 16px;
            font-weight: 800;
            color: #18181b;
            letter-spacing: -.01em;
        }

        /* ── Payment info ────────────────────────── */
        .payment-block {
            margin: 0 40px 28px;
            background: #f9fafb;
            border: 1px solid #e4e4e7;
            border-radius: 8px;
            padding: 16px 20px;
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .payment-icon {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: #dcfce7;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-size: 16px;
        }

        .payment-label {
            font-size: 12px;
            color: #71717a;
        }

        .payment-value {
            font-size: 14px;
            font-weight: 700;
            color: #18181b;
        }

        /* ── Notes ───────────────────────────────── */
        .notes-block {
            margin: 0 40px 28px;
            font-size: 13px;
            color: #71717a;
            line-height: 1.6;
        }

        /* ── Footer ──────────────────────────────── */
        .footer {
            background: #fafafa;
            border-top: 1px solid #f1f1f1;
            padding: 24px 40px;
            text-align: center;
        }

        .footer-thanks {
            font-size: 15px;
            font-weight: 700;
            color: #18181b;
            margin-bottom: 6px;
        }

        .footer-sub {
            font-size: 12px;
            color: #a1a1aa;
        }

        .footer-powered {
            margin-top: 20px;
            font-size: 11px;
            color: #d4d4d8;
        }

        @media (max-width: 600px) {
            .wrapper { margin: 0; border-radius: 0; }
            .header, .client-block, .items-section, .totals,
            .payment-block, .notes-block, .footer {
                padding-left: 20px;
                padding-right: 20px;
            }
            .client-block { flex-direction: column; gap: 16px; }
            .payment-block { margin-left: 20px; margin-right: 20px; }
            .notes-block { margin-left: 20px; margin-right: 20px; }
        }
    </style>
</head>
<body>
<div class="wrapper">

    {{-- ── Header ── --}}
    <div class="header">
        <div class="header-top">
            <span class="business-name">{{ $businessName }}</span>
            <span class="receipt-badge">Receipt</span>
        </div>
        <div class="header-title">{{ $receipt->receipt_number }}</div>
        <div class="header-meta">
            Issued {{ \Carbon\Carbon::parse($receipt->receipt_date)->format('d M Y') }}
        </div>
    </div>

    {{-- ── Client & metadata ── --}}
    <div class="client-block">
        <div class="meta-group">
            <label>Billed To</label>
            <p>{{ $receipt->customer_name ?? 'Customer' }}</p>
            @if($receipt->customer_email)
                <p class="light">{{ $receipt->customer_email }}</p>
            @endif
            @if($receipt->customer_phone)
                <p class="light">{{ $receipt->customer_phone }}</p>
            @endif
        </div>

        <div class="meta-group" style="text-align:right;">
            @if($receipt->linked_invoice_id && $receipt->linkedInvoice)
                <label>Invoice</label>
                <p>{{ $receipt->linkedInvoice->invoice_number }}</p>
            @endif
            @if($receipt->transaction_reference)
                <label style="margin-top:12px;">Reference</label>
                <p class="light" style="font-size:12px;">{{ $receipt->transaction_reference }}</p>
            @endif
        </div>
    </div>

    {{-- ── Line items ── --}}
    <div class="items-section">
        <p class="section-title">Items</p>
        <table>
            <thead>
                <tr>
                    <th style="text-align:left;">Description</th>
                    <th>Qty</th>
                    <th>Unit Price</th>
                    <th>Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach(($receipt->items ?? []) as $item)
                <tr>
                    <td class="desc">{{ $item['description'] ?? '—' }}</td>
                    <td class="qty">{{ number_format($item['quantity'] ?? 1, 0) }}</td>
                    <td class="price">
                        {{ $receipt->currency ?? 'USD' }}
                        {{ number_format($item['unit_price'] ?? 0, 2) }}
                    </td>
                    <td class="amount">
                        {{ $receipt->currency ?? 'USD' }}
                        {{ number_format($item['amount'] ?? 0, 2) }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- ── Totals ── --}}
    <div class="totals">
        <div class="totals-inner">
            <div class="totals-row">
                <span>Subtotal</span>
                <span>{{ $receipt->currency ?? 'USD' }} {{ number_format($receipt->subtotal, 2) }}</span>
            </div>

            @if($receipt->discount_amount > 0)
            <div class="totals-row">
                <span>Discount</span>
                <span style="color:#16a34a;">−{{ $receipt->currency ?? 'USD' }} {{ number_format($receipt->discount_amount, 2) }}</span>
            </div>
            @endif

            @if($receipt->tax_amount > 0)
            <div class="totals-row">
                <span>Tax ({{ number_format($receipt->tax_rate, 0) }}%)</span>
                <span>{{ $receipt->currency ?? 'USD' }} {{ number_format($receipt->tax_amount, 2) }}</span>
            </div>
            @endif

            <div class="totals-row total">
                <span>Total Paid</span>
                <span>{{ $receipt->currency ?? 'USD' }} {{ number_format($receipt->total_amount, 2) }}</span>
            </div>
        </div>
    </div>

    {{-- ── Payment method ── --}}
    <div class="payment-block">
        <div class="payment-icon">✓</div>
        <div>
            <div class="payment-label">Payment method</div>
            <div class="payment-value">{{ $receipt->payment_method_label }}</div>
        </div>
        <div style="margin-left:auto; text-align:right;">
            <div class="payment-label">Date</div>
            <div class="payment-value">{{ \Carbon\Carbon::parse($receipt->receipt_date)->format('d M Y') }}</div>
        </div>
    </div>

    {{-- ── Notes ── --}}
    @if($receipt->notes)
    <div class="notes-block">
        <strong>Note:</strong> {{ $receipt->notes }}
    </div>
    @endif

    {{-- ── Footer ── --}}
    <div class="footer">
        <p class="footer-thanks">Thank you for your payment 🎉</p>
        <p class="footer-sub">
            If you have any questions about this receipt, reply to this email
            or contact {{ $businessName }} directly.
        </p>
        <p class="footer-powered">Powered by {{ config('app.name') }}</p>
    </div>

</div>
</body>
</html>
