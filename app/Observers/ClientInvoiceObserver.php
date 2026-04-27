<?php

namespace App\Observers;

use App\Models\ClientInvoice;
use App\Services\MessagingService;
use Illuminate\Support\Facades\Log;

class ClientInvoiceObserver
{
    public function __construct(protected MessagingService $messaging) {}

    /**
     * Fire a payment-received SMS to the owner whenever an invoice
     * transitions from any non-paid status → 'paid'.
     */
    public function updated(ClientInvoice $invoice): void
    {
        if (! $invoice->wasChanged('status')) {
            return;
        }

        $previousStatus = $invoice->getOriginal('status');

        if ($invoice->status === 'paid' && $previousStatus !== 'paid') {
            try {
                $invoice->loadMissing(['user', 'client']);

                if ($invoice->user) {
                    $this->messaging->sendPaymentReceivedAlert(
                        $invoice->user,
                        $invoice->client?->name ?? 'Client',
                        (float) $invoice->total_amount,
                        $invoice->invoice_number
                    );
                }
            } catch (\Exception $e) {
                Log::error('Failed to send payment received SMS', [
                    'invoice_id' => $invoice->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }
}
