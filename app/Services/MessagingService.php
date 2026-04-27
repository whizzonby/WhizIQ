<?php

namespace App\Services;

use App\Models\NotificationPreference;
use App\Models\User;
use App\Models\WhatsAppAdminConfig;
use App\Models\WhatsAppConfiguration;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Twilio\Rest\Client as TwilioClient;

class MessagingService
{
    private ?TwilioClient $twilio = null;

    // ---------------------------------------------------------------
    // Public API — Owner notifications
    // ---------------------------------------------------------------

    /**
     * Send a message to the business owner via their preferred channel.
     */
    public function notifyOwner(User $user, string $message, ?string $forceChannel = null): bool
    {
        $prefs = NotificationPreference::forUser($user->id);

        if (! $prefs->hasOwnerPhone()) {
            Log::debug("MessagingService: owner {$user->id} has no phone configured, skipping.");
            return false;
        }

        $channel = $forceChannel ?? $prefs->preferred_channel;
        $phone   = $prefs->owner_phone;
        $sent    = false;

        if (in_array($channel, ['sms', 'both'])) {
            $sent = $this->sendSms($phone, $message) || $sent;
        }

        if (in_array($channel, ['whatsapp', 'both'])) {
            $sent = $this->sendWhatsApp($phone, $message, $user) || $sent;
        }

        return $sent;
    }

    /**
     * Send a message to a client via SMS.
     * Client WhatsApp messages require pre-approved Meta templates, so SMS is used here.
     */
    public function notifyClient(string $phone, string $message): bool
    {
        $phone = $this->normalizePhone($phone);

        if (! $phone) {
            Log::debug('MessagingService: client has no valid phone, skipping.');
            return false;
        }

        return $this->sendSms($phone, $message);
    }

    // ---------------------------------------------------------------
    // Convenience alert builders — Owner
    // ---------------------------------------------------------------

    public function sendPaymentReceivedAlert(User $user, string $clientName, float $amount, string $invoiceNumber): bool
    {
        $prefs = NotificationPreference::forUser($user->id);

        if (! $prefs->alert_payment_received) {
            return false;
        }

        $message = $this->formatOwnerMessage(
            "✅ Payment received\n"
            . "{$clientName} paid " . $this->formatMoney($amount) . " — {$invoiceNumber}"
        );

        return $this->notifyOwner($user, $message);
    }

    public function sendOverdueInvoiceAlert(User $user, string $clientName, float $amount, string $invoiceNumber, int $daysOverdue): bool
    {
        $prefs = NotificationPreference::forUser($user->id);

        if (! $prefs->alert_overdue_invoice) {
            return false;
        }

        $message = $this->formatOwnerMessage(
            "⚠️ Overdue invoice\n"
            . "{$invoiceNumber} — " . $this->formatMoney($amount) . " from {$clientName}\n"
            . "{$daysOverdue} " . ($daysOverdue === 1 ? 'day' : 'days') . " overdue"
        );

        return $this->notifyOwner($user, $message);
    }

    public function sendNewBookingAlert(User $user, string $clientName, string $serviceName, string $dateTime): bool
    {
        $prefs = NotificationPreference::forUser($user->id);

        if (! $prefs->alert_new_booking) {
            return false;
        }

        $message = $this->formatOwnerMessage(
            "📅 New booking\n"
            . "{$clientName} — {$serviceName}\n"
            . $dateTime
        );

        return $this->notifyOwner($user, $message);
    }

    public function sendFollowUpAlert(User $user, string $contactName, string $reminderTitle): bool
    {
        $prefs = NotificationPreference::forUser($user->id);

        if (! $prefs->alert_follow_up_due) {
            return false;
        }

        $message = $this->formatOwnerMessage(
            "📞 Follow-up due\n"
            . "{$contactName}: {$reminderTitle}"
        );

        return $this->notifyOwner($user, $message);
    }

    public function sendStaleDealAlert(User $user, int $dealCount): bool
    {
        $prefs = NotificationPreference::forUser($user->id);

        if (! $prefs->alert_stale_deals || $dealCount === 0) {
            return false;
        }

        $message = $this->formatOwnerMessage(
            "🔔 Stale deals\n"
            . $dealCount . ' ' . ($dealCount === 1 ? 'deal has' : 'deals have') . " not moved in 14+ days.\n"
            . 'Log in to review your pipeline.'
        );

        return $this->notifyOwner($user, $message);
    }

    // ---------------------------------------------------------------
    // Convenience message builders — Client-facing
    // ---------------------------------------------------------------

    public function sendAppointmentReminderToClient(string $phone, string $clientName, string $businessName, string $serviceName, string $dateTime, int $hoursUntil): bool
    {
        $when = $hoursUntil <= 2 ? "in {$hoursUntil} hour" . ($hoursUntil === 1 ? '' : 's') : 'tomorrow';

        $message = "Hi {$clientName}, reminder: your {$serviceName} appointment is {$when}"
            . " ({$dateTime}) with {$businessName}.\n"
            . 'Reply STOP to opt out.';

        return $this->notifyClient($phone, $message);
    }

    public function sendBookingConfirmationToClient(string $phone, string $clientName, string $businessName, string $serviceName, string $dateTime): bool
    {
        $message = "Hi {$clientName}, your {$serviceName} appointment on {$dateTime} with {$businessName} is confirmed. "
            . "See you then!\n"
            . 'Reply STOP to opt out.';

        return $this->notifyClient($phone, $message);
    }

    public function sendInvoiceReminderToClient(string $phone, string $clientName, string $invoiceNumber, float $amount, string $dueDate): bool
    {
        $message = "Hi {$clientName}, a friendly reminder that invoice {$invoiceNumber} "
            . "for " . $this->formatMoney($amount) . " was due on {$dueDate}. "
            . "Please arrange payment at your earliest convenience.\n"
            . 'Reply STOP to opt out.';

        return $this->notifyClient($phone, $message);
    }

    // ---------------------------------------------------------------
    // Core send methods
    // ---------------------------------------------------------------

    public function sendSms(string $to, string $message): bool
    {
        $to = $this->normalizePhone($to);

        if (! $to || ! $this->isTwilioConfigured()) {
            return false;
        }

        try {
            $this->getTwilio()->messages->create($to, [
                'from' => config('services.twilio.from'),
                'body' => $this->truncateSms($message),
            ]);

            Log::info('MessagingService: SMS sent', ['to' => $to]);
            return true;
        } catch (\Exception $e) {
            Log::error('MessagingService: SMS failed', ['to' => $to, 'error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Send a WhatsApp message.
     *
     * Priority:
     *  1. Meta WhatsApp Business API — uses the credentials saved in the
     *     dashboard WhatsApp Settings page (WhatsAppAdminConfig + WhatsAppConfiguration).
     *  2. Twilio WhatsApp — fallback if TWILIO_WHATSAPP_FROM is configured.
     *  3. Twilio SMS — last resort if neither WhatsApp provider is available.
     *
     * @param  string    $to        Recipient phone in any format (will be normalised)
     * @param  string    $message   Plain-text message body
     * @param  User|null $fromUser  The business-owner User whose WhatsApp config to use
     */
    public function sendWhatsApp(string $to, string $message, ?User $fromUser = null): bool
    {
        $to = $this->normalizePhone($to);

        if (! $to) {
            return false;
        }

        // 1. Try Meta WhatsApp Business API (from dashboard settings)
        if ($fromUser && $this->sendViaMetaWhatsApp($to, $message, $fromUser)) {
            return true;
        }

        // 2. Try Twilio WhatsApp
        $whatsappFrom = config('services.twilio.whatsapp_from');

        if ($this->isTwilioConfigured() && $whatsappFrom) {
            try {
                $this->getTwilio()->messages->create('whatsapp:' . $to, [
                    'from' => 'whatsapp:' . $whatsappFrom,
                    'body' => $message,
                ]);

                Log::info('MessagingService: WhatsApp sent via Twilio', ['to' => $to]);
                return true;
            } catch (\Exception $e) {
                Log::error('MessagingService: Twilio WhatsApp failed', ['to' => $to, 'error' => $e->getMessage()]);
            }
        }

        // 3. Fall back to SMS
        Log::debug('MessagingService: WhatsApp unavailable, falling back to SMS.', ['to' => $to]);
        return $this->sendSms($to, $message);
    }

    // ---------------------------------------------------------------
    // Configuration checks
    // ---------------------------------------------------------------

    public function isTwilioConfigured(): bool
    {
        return ! empty(config('services.twilio.sid'))
            && ! empty(config('services.twilio.token'))
            && ! empty(config('services.twilio.from'));
    }

    public function isMetaWhatsAppConfigured(?User $user = null): bool
    {
        $adminConfig = WhatsAppAdminConfig::getConfig();

        if (! $adminConfig || ! $adminConfig->isConfigured() || ! $adminConfig->is_active) {
            return false;
        }

        if ($user) {
            $userConfig = WhatsAppConfiguration::forUser($user->id);
            return $userConfig && $userConfig->isConfigured();
        }

        return true;
    }

    // ---------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------

    /**
     * Normalise a phone number to E.164 format.
     */
    public function normalizePhone(?string $phone): ?string
    {
        if (empty($phone)) {
            return null;
        }

        $stripped = preg_replace('/[^\d+]/', '', $phone);

        if (str_starts_with($stripped, '+')) {
            return strlen($stripped) >= 8 ? $stripped : null;
        }

        if (str_starts_with($stripped, '00')) {
            return '+' . substr($stripped, 2);
        }

        if (strlen($stripped) === 10) {
            return '+1' . $stripped;
        }

        if (strlen($stripped) === 11 && str_starts_with($stripped, '1')) {
            return '+' . $stripped;
        }

        return '+' . $stripped;
    }

    /**
     * Send via Meta WhatsApp Business API using dashboard-configured credentials.
     */
    private function sendViaMetaWhatsApp(string $to, string $message, User $user): bool
    {
        try {
            $adminConfig = WhatsAppAdminConfig::getConfig();

            if (! $adminConfig || ! $adminConfig->isConfigured() || ! $adminConfig->is_active) {
                return false;
            }

            $userConfig = WhatsAppConfiguration::forUser($user->id);

            if (! $userConfig || ! $userConfig->isConfigured()) {
                return false;
            }

            $apiVersion = $adminConfig->api_version ?? 'v21.0';
            $apiUrl     = "https://graph.facebook.com/{$apiVersion}/{$userConfig->phone_number_id}/messages";

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $adminConfig->access_token,
                'Content-Type'  => 'application/json',
            ])->post($apiUrl, [
                'messaging_product' => 'whatsapp',
                'to'                => preg_replace('/[^\d]/', '', $to), // digits only for Meta
                'type'              => 'text',
                'text'              => ['body' => $message],
            ]);

            if ($response->successful()) {
                Log::info('MessagingService: WhatsApp sent via Meta API', ['to' => $to]);
                return true;
            }

            Log::warning('MessagingService: Meta WhatsApp API error', [
                'to'     => $to,
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);

            return false;
        } catch (\Exception $e) {
            Log::error('MessagingService: Meta WhatsApp exception', ['to' => $to, 'error' => $e->getMessage()]);
            return false;
        }
    }

    private function formatOwnerMessage(string $body): string
    {
        return $body . "\n\n— WhizIQ";
    }

    private function formatMoney(float $amount): string
    {
        return '£' . number_format($amount, 2);
    }

    private function truncateSms(string $message): string
    {
        return mb_strlen($message) > 1550
            ? mb_substr($message, 0, 1547) . '...'
            : $message;
    }

    private function getTwilio(): TwilioClient
    {
        if (! $this->twilio) {
            $this->twilio = new TwilioClient(
                config('services.twilio.sid'),
                config('services.twilio.token')
            );
        }

        return $this->twilio;
    }
}
