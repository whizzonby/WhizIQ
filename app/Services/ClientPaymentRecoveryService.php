<?php

namespace App\Services;

use App\Models\ClientInvoice;
use App\Models\ClientPaymentAttempt;
use App\Models\FollowUpReminder;
use Illuminate\Support\Collection;

class ClientPaymentRecoveryService
{
    public function recordFailedAttempt(ClientInvoice $invoice, array $data = []): ClientPaymentAttempt
    {
        $invoice->loadMissing(['client.contact']);

        $lookup = [
            'provider' => $data['provider'] ?? 'stripe',
            'provider_attempt_id' => $data['provider_attempt_id'] ?? null,
        ];

        $attributes = [
            'user_id' => $invoice->user_id,
            'client_invoice_id' => $invoice->id,
            'invoice_client_id' => $invoice->invoice_client_id,
            'contact_id' => $invoice->client?->contact_id,
            'amount' => $data['amount'] ?? $invoice->balance_due,
            'currency' => $data['currency'] ?? $invoice->currency ?? 'USD',
            'status' => 'failed',
            'failure_message' => $data['failure_message'] ?? null,
            'failed_at' => $data['failed_at'] ?? now(),
            'metadata' => $data['metadata'] ?? null,
        ];

        if ($lookup['provider_attempt_id']) {
            $existing = ClientPaymentAttempt::where($lookup)->first();

            // A late/replayed webhook for an attempt that's already been paid
            // off shouldn't resurrect it as "failed" - the client already
            // succeeded, so leave the recovered record alone.
            if ($existing && $existing->recovered_at) {
                return $existing;
            }

            $attempt = ClientPaymentAttempt::updateOrCreate($lookup, $attributes);
        } else {
            $attempt = ClientPaymentAttempt::create(array_merge($lookup, $attributes));
        }

        $this->createOwnerFollowUp($attempt);

        return $attempt;
    }

    public function recoverFailedAttempts(int $limit = 50): int
    {
        $attempts = ClientPaymentAttempt::query()
            ->with(['invoice.client.contact'])
            ->failed()
            ->unrecovered()
            ->where(function ($query) {
                $query->whereNull('recovery_reminder_sent_at')
                    ->orWhere('recovery_reminder_sent_at', '<', now()->subDay());
            })
            ->where('failed_at', '<=', now()->subMinutes(15))
            ->latest('failed_at')
            ->limit($limit)
            ->get();

        return $this->recoverAttempts($attempts);
    }

    public function recoverAttempt(ClientPaymentAttempt $attempt): bool
    {
        $attempt->loadMissing(['invoice.client.contact']);

        $invoice = $attempt->invoice;

        if (! $invoice || $invoice->status === 'paid') {
            $attempt->markRecovered();
            return false;
        }

        $sent = app(InvoiceReminderService::class)->sendReminderToClient($invoice, 'payment_failed');

        if ($sent) {
            $attempt->markRecoveryReminderSent();
            app(InvoiceReminderService::class)->createCollectionFollowUp($invoice, 'payment_failed');
            $this->createOwnerFollowUp($attempt);
        }

        return $sent;
    }

    protected function recoverAttempts(Collection $attempts): int
    {
        $sent = 0;

        foreach ($attempts as $attempt) {
            if ($this->recoverAttempt($attempt)) {
                $sent++;
            }
        }

        return $sent;
    }

    protected function createOwnerFollowUp(ClientPaymentAttempt $attempt): ?FollowUpReminder
    {
        $attempt->loadMissing(['invoice.client.contact']);

        $contact = $attempt->contact ?? $attempt->invoice?->client?->contact;

        if (! $contact) {
            return null;
        }

        $invoiceNumber = $attempt->invoice?->invoice_number ?? 'client invoice';
        $title = "Recover failed payment {$invoiceNumber}";

        return FollowUpReminder::firstOrCreate(
            [
                'user_id' => $attempt->user_id,
                'contact_id' => $contact->id,
                'title' => $title,
                'status' => 'pending',
            ],
            [
                'description' => $attempt->failure_message
                    ? "Client payment failed: {$attempt->failure_message}"
                    : 'Client payment failed. Follow up with the client or resend the payment link.',
                'remind_at' => now()->addHours(4),
                'priority' => 'high',
            ]
        );
    }
}
