<?php

namespace App\Services;

use App\Models\Receipt;
use Spatie\Browsershot\Browsershot;
use Illuminate\Support\Facades\Log;

class ReceiptPDFService
{
    public function generate(Receipt $receipt): string
    {
        $receipt->loadMissing(['user', 'client']);

        $html = view('filament.dashboard.components.receipt-preview', [
            'receipt'       => $receipt,
            'template'      => $receipt->template ?? 'standard',
            'primaryColor'  => $receipt->primary_color ?? '#10b981',
            'forPdf'        => true,
        ])->render();

        $fullHtml = <<<HTML
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <script src="https://cdn.tailwindcss.com"></script>
            <style>
                * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
                body { margin: 0; padding: 0; background: white; }
            </style>
        </head>
        <body class="bg-white">
            {$html}
        </body>
        </html>
        HTML;

        return Browsershot::html($fullHtml)
            ->noSandbox()
            ->waitUntilNetworkIdle()
            ->format($receipt->template === 'thermal' ? 'A6' : 'A4')
            ->portrait()
            ->margins(8, 8, 8, 8)
            ->pdf();
    }

    public function download(Receipt $receipt): \Symfony\Component\HttpFoundation\Response
    {
        $pdf = $this->generate($receipt);
        $filename = $receipt->receipt_number . '.pdf';

        return response($pdf, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    public function stream(Receipt $receipt): \Symfony\Component\HttpFoundation\Response
    {
        $pdf = $this->generate($receipt);
        $filename = $receipt->receipt_number . '.pdf';

        return response($pdf, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => "inline; filename=\"{$filename}\"",
        ]);
    }
}
