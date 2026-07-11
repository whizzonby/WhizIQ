<?php

namespace App\Services;

use App\Models\ClientInvoice;
use App\Models\FollowUpReminder;
use App\Models\NotificationPreference;
use App\Models\User;
use App\Notifications\OverdueInvoiceNotification;
use App\Notifications\PaymentReminderNotification;
use App\Services\MessagingService;
use Carbon\Carbon;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class InvoiceReminderService
{
    /**
     * Send overdue invoice notifications to users
     */
    public function sendOverdueNotifications(): int
    {
        $sent = 0;

        // Get all invoices that just became overdue today
        $overdueInvoices = ClientInvoice::query()
            ->with(['client', 'user'])
            ->where('status', 'overdue')
            ->whereDate('due_date', '<', now())
            ->where(function ($query) {
                // Either never sent a notification, or last notification was more than 1 day ago
                $query->whereNull('last_reminder_sent_at')
                    ->orWhere('last_reminder_sent_at', '<', now()->subDay());
            })
            ->get();

        $messaging = app(MessagingService::class);

        foreach ($overdueInvoices as $invoice) {
            try {
                // Email alert to business owner
                $invoice->user->notify(new OverdueInvoiceNotification($invoice));

                // SMS alert to business owner
                $daysOverdue = (int) now()->diffInDays($invoice->due_date);
                $messaging->sendOverdueInvoiceAlert(
                    $invoice->user,
                    $invoice->client->name ?? 'Client',
                    (float) $invoice->total_amount,
                    $invoice->invoice_number,
                    $daysOverdue
                );

                // SMS reminder to the client
                $prefs = NotificationPreference::forUser($invoice->user_id);
                if ($prefs->client_invoice_reminders && $invoice->client?->phone) {
                    $messaging->sendInvoiceReminderToClient(
                        $invoice->client->phone,
                        $invoice->client->name ?? 'there',
                        $invoice->invoice_number,
                        (float) $invoice->total_amount,
                        $invoice->due_date->format('j M Y')
                    );
                }

                $this->createCollectionFollowUp($invoice, 'overdue');

                $sent++;
                Log::info("Overdue notification sent for invoice #{$invoice->invoice_number}");
            } catch (\Exception $e) {
                Log::error("Failed to send overdue notification for invoice #{$invoice->invoice_number}: {$e->getMessage()}");
            }
        }

        return $sent;
    }

    /**
     * Send payment reminders based on due dates
     */
    public function sendScheduledReminders(): int
    {
        $sent = 0;

        // First reminder: 3 days before due date
        $sent += $this->sendRemindersForDueDate(now()->addDays(3), 'first');

        // Second reminder: 1 day after due date
        $sent += $this->sendRemindersForDueDate(now()->subDay(), 'second');

        // Final reminder: 7 days after due date
        $sent += $this->sendRemindersForDueDate(now()->subDays(7), 'final');

        return $sent;
    }

    /**
     * Send reminders for invoices due on a specific date
     */
    protected function sendRemindersForDueDate(Carbon $dueDate, string $reminderType): int
    {
        $sent = 0;

        $invoices = ClientInvoice::query()
            ->with(['client', 'user'])
            ->whereIn('status', ['sent', 'partial', 'overdue'])
            ->whereDate('due_date', $dueDate->toDateString())
            ->where(function ($query) {
                // Don't send if already sent a reminder in the last 24 hours
                $query->whereNull('last_reminder_sent_at')
                    ->orWhere('last_reminder_sent_at', '<', now()->subHours(24));
            })
            ->get();

        foreach ($invoices as $invoice) {
            if ($invoice->canSendReminder()) {
                try {
                    $this->sendReminderToClient($invoice, $reminderType);
                    $invoice->recordReminderSent();
                    $this->createCollectionFollowUp($invoice, $reminderType);
                    $sent++;

                    Log::info("Payment reminder ({$reminderType}) sent for invoice #{$invoice->invoice_number}");
                } catch (\Exception $e) {
                    Log::error("Failed to send reminder for invoice #{$invoice->invoice_number}: {$e->getMessage()}");
                }
            }
        }

        return $sent;
    }

    /**
     * Send a reminder to a specific client
     */
    public function sendReminderToClient(ClientInvoice $invoice, string $reminderType = 'standard'): bool
    {
        $sent = false;

        try {
            // Email to client
            if ($invoice->client->email) {
                $clientNotifiable = new class($invoice->client->email, $invoice->client->name) {
                    use Notifiable;

                    public function __construct(
                        public string $email,
                        public string $name
                    ) {}

                    public function routeNotificationForMail(): string
                    {
                        return $this->email;
                    }
                };

                $clientNotifiable->notify(new PaymentReminderNotification($invoice, $reminderType));
                $sent = true;
            }

            // SMS to client (if owner has client invoice reminders enabled)
            $prefs = NotificationPreference::forUser($invoice->user_id);
            if ($prefs->client_invoice_reminders && $invoice->client?->phone) {
                app(MessagingService::class)->sendInvoiceReminderToClient(
                    $invoice->client->phone,
                    $invoice->client->name ?? 'there',
                    $invoice->invoice_number,
                    (float) $invoice->total_amount,
                    $invoice->due_date->format('j M Y')
                );
                $sent = true;
            }

            return $sent;
        } catch (\Exception $e) {
            Log::error("Failed to send reminder to client for invoice #{$invoice->invoice_number}: {$e->getMessage()}");
            return false;
        }
    }

    /**
     * Send manual reminder for a specific invoice
     */
    public function sendManualReminder(ClientInvoice $invoice): bool
    {
        if (!$invoice->canSendReminder()) {
            return false;
        }

        $reminderType = match(true) {
            $invoice->days_overdue > 7 => 'final',
            $invoice->days_overdue > 0 => 'second',
            default => 'first',
        };

        $result = $this->sendReminderToClient($invoice, $reminderType);

        if ($result) {
            $invoice->recordReminderSent();
            $this->createCollectionFollowUp($invoice, $reminderType);
        }

        return $result;
    }

    public function sendPaymentLink(ClientInvoice $invoice, ?string $recipientEmail = null, ?string $message = null): bool
    {
        $invoice->loadMissing(['client', 'user']);

        if ($invoice->status === 'paid' || (float) $invoice->balance_due <= 0) {
            return false;
        }

        $recipientEmail = $recipientEmail ?: $invoice->client?->email;

        if (! $recipientEmail || ! filter_var($recipientEmail, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        try {
            $paymentUrl = app(ClientInvoicePaymentService::class)->signedPaymentUrl($invoice, true);
            $message = $message ?: "You can pay invoice {$invoice->invoice_number} securely using the link below.";

            \Mail::send('invoices.payment-link-email', [
                'invoice' => $invoice,
                'client' => $invoice->client,
                'paymentUrl' => $paymentUrl,
                'emailMessage' => $message,
            ], function ($mail) use ($invoice, $recipientEmail) {
                $mail->to($recipientEmail, $invoice->client?->name)
                    ->subject("Payment link for invoice {$invoice->invoice_number}");
            });

            $invoice->markPaymentLinkEmailed();
            $this->createCollectionFollowUp($invoice, 'payment_link');

            return true;
        } catch (\Exception $e) {
            Log::error("Failed to send payment link for invoice #{$invoice->invoice_number}: {$e->getMessage()}");

            return false;
        }
    }

    /**
     * Get invoices that need reminders
     */
    public function getInvoicesNeedingReminders(): Collection
    {
        return ClientInvoice::query()
            ->with(['client', 'user'])
            ->whereIn('status', ['sent', 'partial', 'overdue'])
            ->where(function ($query) {
                $query->where('due_date', '<=', now()->addDays(3))
                    ->orWhere('status', 'overdue');
            })
            ->where(function ($query) {
                $query->whereNull('last_reminder_sent_at')
                    ->orWhere('last_reminder_sent_at', '<', now()->subHours(24));
            })
            ->orderBy('due_date', 'asc')
            ->get();
    }

    /**
     * Create an owner follow-up task so unpaid invoices do not disappear after reminders.
     */
    public function createCollectionFollowUp(ClientInvoice $invoice, string $reminderType = 'standard'): ?FollowUpReminder
    {
        $invoice->loadMissing(['client.contact']);

        $contact = $invoice->client?->contact;

        if (! $contact) {
            return null;
        }

        $priority = match($reminderType) {
            'final', 'overdue', 'payment_failed', 'payment_link' => 'high',
            default => 'medium',
        };

        $remindAt = match($reminderType) {
            'payment_failed', 'payment_link' => now()->addHours(4),
            'final' => now()->addDay(),
            'overdue', 'second' => now()->addDays(2),
            default => now()->addDays(4),
        };

        $title = "Collect invoice {$invoice->invoice_number}";

        $existingReminder = FollowUpReminder::where('user_id', $invoice->user_id)
            ->where('contact_id', $contact->id)
            ->where('title', $title)
            ->whereIn('status', ['pending', 'sent'])
            ->first();

        if ($existingReminder) {
            return $existingReminder;
        }

        $reminder = FollowUpReminder::create([
            'user_id' => $invoice->user_id,
            'contact_id' => $contact->id,
            'title' => $title,
            'description' => "Invoice {$invoice->invoice_number} has a balance due of {$invoice->currency} {$invoice->balance_due}. Follow up if the client has not paid.",
            'remind_at' => $remindAt,
            'priority' => $priority,
            'status' => 'pending',
        ]);

        $contact->logInteraction(
            type: 'task',
            description: "Payment follow-up created for invoice {$invoice->invoice_number}.",
            interactionDate: now(),
            subject: 'Invoice collection follow-up',
            outcome: 'follow_up_needed'
        );

        return $reminder;
    }
}
