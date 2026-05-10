<?php

namespace App\Services;

use App\Models\Quote;
use Spatie\Browsershot\Browsershot;
use Illuminate\Support\Facades\Log;

class QuotePDFService
{
    public function generate(Quote $quote): string
    {
        $quote->loadMissing(['client', 'items', 'user', 'user.companyProfile']);

        $html = view('filament.dashboard.components.quote-preview', [
            'quote'        => $quote,
            'template'     => $quote->template ?? 'modern',
            'primaryColor' => $quote->primary_color ?? '#3b82f6',
            'accentColor'  => $quote->accent_color ?? '#10b981',
        ])->render();

        $fullHtml = <<<HTML
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <title>Quote {$quote->quote_number}</title>
            <script src="https://cdn.tailwindcss.com"></script>
            <style>
                * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
                @media print { body { margin: 0; padding: 0; } }
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
            ->format('A4')
            ->portrait()
            ->margins(10, 10, 10, 10)
            ->pdf();
    }

    public function download(Quote $quote): \Symfony\Component\HttpFoundation\Response
    {
        $pdf = $this->generate($quote);

        return response($pdf, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $quote->quote_number . '.pdf"',
        ]);
    }

    public function stream(Quote $quote): \Symfony\Component\HttpFoundation\Response
    {
        $pdf = $this->generate($quote);

        return response($pdf, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $quote->quote_number . '.pdf"',
        ]);
    }

    public function emailToClient(Quote $quote): bool
    {
        if (!$quote->client?->email) {
            return false;
        }

        try {
            $pdf = $this->generate($quote);

            \Illuminate\Support\Facades\Mail::send([], [], function ($message) use ($quote, $pdf) {
                $message->to($quote->client->email, $quote->client->name)
                    ->subject('Quote ' . $quote->quote_number . ' from ' . ($quote->user->companyProfile?->company_name ?? $quote->user->name))
                    ->setBody(
                        'Dear ' . $quote->client->name . ",\n\nPlease find your quote attached.\n\nQuote Number: " . $quote->quote_number . "\nTotal: " . $quote->currency . ' ' . number_format($quote->total_amount, 2) . "\n\nThank you for your interest.",
                        'text/plain'
                    )
                    ->attachData($pdf, $quote->quote_number . '.pdf', ['mime' => 'application/pdf']);
            });

            return true;
        } catch (\Exception $e) {
            Log::error('Quote email error', ['quote_id' => $quote->id, 'error' => $e->getMessage()]);
            return false;
        }
    }
}
