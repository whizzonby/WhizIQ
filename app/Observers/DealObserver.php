<?php

namespace App\Observers;

use App\Models\ClientInvoice;
use App\Models\ClientInvoiceItem;
use App\Models\Deal;
use App\Models\InvoiceClient;
use App\Services\InvoicePDFService;
use Illuminate\Support\Facades\Log;

class DealObserver
{
    /**
     * When a deal is marked Won, auto-create an invoice from its products and email it.
     */
    public function updated(Deal $deal): void
    {
        if ($deal->wasChanged('stage')
            && $deal->stage === 'won'
            && $deal->getOriginal('stage') !== 'won'
        ) {
            $this->createInvoiceForDeal($deal);
        }
    }

    protected function createInvoiceForDeal(Deal $deal): void
    {
        try {
            $deal->loadMissing(['contact', 'products', 'user']);

            if (! $deal->contact || ! $deal->contact->email) {
                Log::warning('Deal won — skipping auto-invoice: no contact email', [
                    'deal_id' => $deal->id,
                ]);
                return;
            }

            if ($deal->products->isEmpty()) {
                Log::info('Deal won — skipping auto-invoice: no products on deal', [
                    'deal_id' => $deal->id,
                ]);
                return;
            }

            // Get or create an invoice client from the deal's contact
            $invoiceClient = InvoiceClient::firstOrCreate(
                [
                    'user_id' => $deal->user_id,
                    'email'   => $deal->contact->email,
                ],
                [
                    'name'         => $deal->contact->name,
                    'company_name' => $deal->contact->company ?? null,
                    'phone'        => $deal->contact->phone ?? null,
                ]
            );

            // Generate next invoice number
            $lastInvoice   = ClientInvoice::where('user_id', $deal->user_id)
                ->orderBy('id', 'desc')
                ->first();
            $invoiceNumber = $lastInvoice
                ? 'INV-' . str_pad((int) substr($lastInvoice->invoice_number, 4) + 1, 5, '0', STR_PAD_LEFT)
                : 'INV-00001';

            $subtotal = $deal->products->sum('line_total');

            $invoice = ClientInvoice::create([
                'user_id'           => $deal->user_id,
                'invoice_client_id' => $invoiceClient->id,
                'invoice_number'    => $invoiceNumber,
                'status'            => 'sent',
                'invoice_date'      => now(),
                'due_date'          => now()->addDays(14),
                'subtotal'          => $subtotal,
                'tax_rate'          => 0,
                'tax_amount'        => 0,
                'discount_amount'   => 0,
                'total_amount'      => $subtotal,
                'amount_paid'       => 0,
                'balance_due'       => $subtotal,
                'currency'          => $deal->currency ?? 'USD',
                'notes'             => "Invoice for deal: {$deal->title}",
            ]);

            foreach ($deal->products as $index => $product) {
                ClientInvoiceItem::create([
                    'client_invoice_id' => $invoice->id,
                    'description'       => $product->product_name,
                    'quantity'          => $product->quantity,
                    'unit_price'        => $product->unit_price,
                    'amount'            => $product->line_total,
                    'sort_order'        => $index,
                ]);
            }

            Log::info('Auto-invoice created for won deal', [
                'deal_id'        => $deal->id,
                'invoice_id'     => $invoice->id,
                'invoice_number' => $invoiceNumber,
            ]);

            // Email the invoice to the client
            try {
                $invoice->loadMissing('client');
                app(InvoicePDFService::class)->emailToClient($invoice);
            } catch (\Exception $emailException) {
                Log::warning('Failed to email invoice for won deal', [
                    'deal_id'    => $deal->id,
                    'invoice_id' => $invoice->id,
                    'error'      => $emailException->getMessage(),
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Failed to create invoice for won deal', [
                'deal_id' => $deal->id,
                'error'   => $e->getMessage(),
            ]);
        }
    }
}
