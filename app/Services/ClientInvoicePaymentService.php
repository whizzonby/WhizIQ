<?php

namespace App\Services;

use App\Models\ClientInvoice;
use Illuminate\Support\Facades\URL;
use RuntimeException;
use Stripe\StripeClient;

class ClientInvoicePaymentService
{
    public function signedPaymentUrl(ClientInvoice $invoice, bool $markGenerated = false): string
    {
        if ($markGenerated) {
            $invoice->markPaymentLinkGenerated();
        }

        return URL::signedRoute('invoice.client.pay', ['invoice' => $invoice]);
    }

    public function signedStatusUrl(ClientInvoice $invoice, string $status): string
    {
        return URL::signedRoute('invoice.client.payment-status', [
            'invoice' => $invoice,
            'status' => $status,
        ]);
    }

    public function createStripeCheckoutUrl(ClientInvoice $invoice): string
    {
        $invoice->loadMissing(['client', 'user']);

        if ($invoice->status === 'paid' || (float) $invoice->balance_due <= 0) {
            return $this->signedStatusUrl($invoice, 'paid');
        }

        if (! config('services.stripe.secret_key')) {
            throw new RuntimeException('Stripe is not configured.');
        }

        $stripe = new StripeClient(config('services.stripe.secret_key'));
        $amount = max(1, (int) round(((float) $invoice->balance_due) * 100));
        $metadata = $this->metadata($invoice);

        $sessionPayload = [
            'mode' => 'payment',
            'success_url' => $this->signedStatusUrl($invoice, 'success'),
            'cancel_url' => $this->signedStatusUrl($invoice, 'cancelled'),
            'metadata' => $metadata,
            'payment_intent_data' => [
                'metadata' => $metadata,
            ],
            'line_items' => [
                [
                    'quantity' => 1,
                    'price_data' => [
                        'currency' => strtolower($invoice->currency ?? 'usd'),
                        'unit_amount' => $amount,
                        'product_data' => [
                            'name' => 'Invoice ' . $invoice->invoice_number,
                            'description' => $invoice->client?->name
                                ? 'Payment for ' . $invoice->client->name
                                : 'Client invoice payment',
                        ],
                    ],
                ],
            ],
        ];

        if ($invoice->client?->email) {
            $sessionPayload['customer_email'] = $invoice->client->email;
        }

        $session = $stripe->checkout->sessions->create($sessionPayload);

        return $session->url;
    }

    protected function metadata(ClientInvoice $invoice): array
    {
        return [
            'client_invoice_id' => (string) $invoice->id,
            'client_invoice_number' => (string) $invoice->invoice_number,
            'invoice_id' => (string) $invoice->id,
            'invoice_number' => (string) $invoice->invoice_number,
            'user_id' => (string) $invoice->user_id,
            'owner_user_id' => (string) $invoice->user_id,
        ];
    }
}
