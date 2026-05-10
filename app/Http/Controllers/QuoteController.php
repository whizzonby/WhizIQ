<?php

namespace App\Http\Controllers;

use App\Models\Quote;
use App\Services\QuotePDFService;

class QuoteController extends Controller
{
    public function downloadPDF(Quote $quote)
    {
        if ($quote->user_id !== auth()->id()) {
            abort(403, 'You do not have permission to download this quote.');
        }

        try {
            return app(QuotePDFService::class)->download($quote);
        } catch (\Exception $e) {
            \Log::error('Quote PDF download failed', [
                'quote_id' => $quote->id,
                'user_id'  => auth()->id(),
                'error'    => $e->getMessage(),
                'file'     => $e->getFile(),
                'line'     => $e->getLine(),
            ]);

            abort(500, 'Failed to generate PDF. Please try again.');
        }
    }
}
