<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Link for Invoice {{ $invoice->invoice_number }}</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background-color: #0066cc; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0;">
        <h1 style="margin: 0; font-size: 24px;">Payment Link</h1>
    </div>

    <div style="background-color: #f9f9f9; padding: 30px; border: 1px solid #e0e0e0; border-top: none; border-radius: 0 0 8px 8px;">
        <p style="font-size: 16px; margin-top: 0;">Dear {{ $client?->name ?? 'there' }},</p>

        <p style="font-size: 14px; line-height: 1.8;">{{ $emailMessage }}</p>

        <div style="background-color: white; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #0066cc;">
            <p style="margin: 0 0 8px; font-size: 14px;"><strong>Invoice:</strong> {{ $invoice->invoice_number }}</p>
            <p style="margin: 0; font-size: 14px;"><strong>Balance due:</strong> {{ $invoice->currency }} {{ number_format($invoice->balance_due, 2) }}</p>
        </div>

        <div style="text-align: center; margin: 24px 0;">
            <a href="{{ $paymentUrl }}"
               style="display: inline-block; background-color: #0066cc; color: #ffffff; text-decoration: none; padding: 12px 22px; border-radius: 8px; font-weight: bold; font-size: 14px;">
                Pay {{ $invoice->currency }} {{ number_format($invoice->balance_due, 2) }}
            </a>
        </div>

        <p style="font-size: 13px; color: #666;">
            If the button does not work, copy and paste this link into your browser:<br>
            <a href="{{ $paymentUrl }}" style="color: #0066cc; word-break: break-all;">{{ $paymentUrl }}</a>
        </p>

        <p style="font-size: 14px; margin-bottom: 0;">
            Best regards,<br>
            <strong>{{ $invoice->user?->name }}</strong>
        </p>
    </div>
</body>
</html>
