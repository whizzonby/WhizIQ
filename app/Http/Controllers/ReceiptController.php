<?php

namespace App\Http\Controllers;

use App\Models\Receipt;
use App\Services\ReceiptPDFService;

class ReceiptController extends Controller
{
    public function downloadPDF(Receipt $receipt)
    {
        if ($receipt->user_id !== auth()->id()) {
            abort(403, 'You do not have permission to download this receipt.');
        }

        try {
            return app(ReceiptPDFService::class)->download($receipt);
        } catch (\Exception $e) {
            \Log::error('Receipt PDF download failed', [
                'receipt_id' => $receipt->id,
                'user_id'    => auth()->id(),
                'error'      => $e->getMessage(),
                'file'       => $e->getFile(),
                'line'       => $e->getLine(),
            ]);

            abort(500, 'Failed to generate PDF. Please try again.');
        }
    }
}
