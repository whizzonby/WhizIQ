<?php

namespace App\Jobs;

use App\Models\Appointment;
use App\Models\AftercareLog;
use App\Models\AftercareTemplate;
use App\Models\NotificationPreference;
use App\Services\MessagingService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendAftercareMessage implements ShouldQueue
{
    use Queueable;

    public int $appointmentId;
    public int $templateId;
    public ?int $logId;
    public ?string $channelOverride;

    public function __construct(
        Appointment       $appointment,
        AftercareTemplate $template,
        ?int              $logId = null,
        ?string           $channelOverride = null
    ) {
        $this->appointmentId   = $appointment->id;
        $this->templateId      = $template->id;
        $this->logId           = $logId;
        $this->channelOverride = $channelOverride;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Reload models with relationships
            $appointment = Appointment::with(['appointmentType', 'user', 'contact'])
                ->findOrFail($this->appointmentId);
            
            $template = AftercareTemplate::findOrFail($this->templateId);

            // Verify appointment is still completed
            if ($appointment->status !== 'completed') {
                Log::warning('Aftercare message skipped - appointment not completed', [
                    'appointment_id' => $appointment->id,
                    'current_status' => $appointment->status,
                ]);
                return;
            }

            // Verify template is still active
            if (!$template->is_active) {
                Log::warning('Aftercare message skipped - template is inactive', [
                    'appointment_id' => $appointment->id,
                    'template_id' => $template->id,
                ]);
                return;
            }

            $channelsSent = [];

            // Send via Email
            if ($template->send_via_email && $appointment->attendee_email) {
                $this->sendEmail($appointment, $template);
                $channelsSent[] = 'Email';
            }

            // Send via SMS
            if ($template->send_via_sms && $appointment->attendee_phone) {
                $this->sendSMS($appointment, $template);
                $channelsSent[] = 'SMS';
            }

            // Send via WhatsApp
            if ($template->send_via_whatsapp && $appointment->attendee_phone) {
                $this->sendWhatsApp($appointment, $template);
                $channelsSent[] = 'WhatsApp';
            }

            if (empty($channelsSent)) {
                Log::warning('Aftercare message skipped - no valid channels or contact info', [
                    'appointment_id' => $appointment->id,
                    'template_id' => $template->id,
                    'has_email' => !empty($appointment->attendee_email),
                    'has_phone' => !empty($appointment->attendee_phone),
                    'template_channels' => $template->getActiveChannels(),
                ]);
                return;
            }

            Log::info('Aftercare message sent successfully', [
                'appointment_id' => $appointment->id,
                'template_id' => $template->id,
                'channels' => $channelsSent,
            ]);

            if ($this->logId) {
                AftercareLog::find($this->logId)?->markSent();
            }

        } catch (\Exception $e) {
            Log::error('Failed to send aftercare message', [
                'appointment_id' => $this->appointmentId,
                'template_id'    => $this->templateId,
                'error'          => $e->getMessage(),
                'trace'          => $e->getTraceAsString(),
            ]);

            if ($this->logId) {
                AftercareLog::find($this->logId)?->markFailed($e->getMessage());
            }

            throw $e;
        }
    }

    protected function sendEmail(Appointment $appointment, AftercareTemplate $template): void
    {
        try {
            $emailData = $template->prepareMessageForAppointment($appointment, 'email');

            if (empty($emailData['subject']) || empty($emailData['body'])) {
                Log::warning('Aftercare email skipped - missing subject or body', [
                    'appointment_id' => $appointment->id,
                    'template_id' => $template->id,
                ]);
                return;
            }

            Mail::send([], [], function ($message) use ($emailData, $appointment) {
                $message->to($appointment->attendee_email)
                    ->subject($emailData['subject'])
                    ->html($this->formatEmailBody($emailData['body'], $appointment, $template));
            });

            Log::info('Aftercare email sent', [
                'appointment_id' => $appointment->id,
                'to' => $appointment->attendee_email,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send aftercare email', [
                'appointment_id' => $appointment->id,
                'to' => $appointment->attendee_email,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    protected function sendSMS(Appointment $appointment, AftercareTemplate $template): void
    {
        try {
            if (! $appointment->attendee_phone) {
                return;
            }

            // Respect the owner's client_aftercare_enabled preference
            if ($appointment->user_id) {
                $prefs = NotificationPreference::forUser($appointment->user_id);
                if (! $prefs->client_aftercare_enabled) {
                    return;
                }
            }

            $smsData = $template->prepareMessageForAppointment($appointment, 'sms');

            if (empty($smsData['message'])) {
                Log::warning('Aftercare SMS skipped - missing message', [
                    'appointment_id' => $appointment->id,
                    'template_id' => $template->id,
                ]);
                return;
            }

            app(MessagingService::class)->notifyClient(
                $appointment->attendee_phone,
                $smsData['message']
            );

            Log::info('Aftercare SMS sent', [
                'appointment_id' => $appointment->id,
                'to' => $appointment->attendee_phone,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send aftercare SMS', [
                'appointment_id' => $appointment->id,
                'to' => $appointment->attendee_phone,
                'error' => $e->getMessage(),
            ]);
        }
    }

    protected function sendWhatsApp(Appointment $appointment, AftercareTemplate $template): void
    {
        try {
            $whatsappData = $template->prepareMessageForAppointment($appointment, 'whatsapp');

            if (empty($whatsappData['message'])) {
                Log::warning('Aftercare WhatsApp skipped - missing message', [
                    'appointment_id' => $appointment->id,
                    'template_id' => $template->id,
                ]);
                return;
            }

            // Get admin WhatsApp configuration (global technical setup)
            $adminConfig = \App\Models\WhatsAppAdminConfig::getConfig();
            
            if (!$adminConfig || !$adminConfig->isConfigured() || !$adminConfig->is_active) {
                Log::warning('Aftercare WhatsApp skipped - admin not configured', [
                    'appointment_id' => $appointment->id,
                    'user_id' => $appointment->user_id,
                ]);
                return;
            }

            // Get user's phone number configuration
            $userConfig = \App\Models\WhatsAppConfiguration::forUser($appointment->user_id);

            if (!$userConfig || empty($userConfig->phone_number_id) || !$userConfig->is_active) {
                Log::warning('Aftercare WhatsApp skipped - user phone not connected', [
                    'appointment_id' => $appointment->id,
                    'user_id' => $appointment->user_id,
                    'has_phone_number_id' => !empty($userConfig?->phone_number_id),
                    'is_active' => $userConfig?->is_active ?? false,
                ]);
                return;
            }

            // Send via WhatsApp Business API using admin config + user's phone number
            $this->sendWhatsAppMessage($adminConfig, $userConfig, $appointment->attendee_phone, $whatsappData['message']);

            Log::info('Aftercare WhatsApp sent', [
                'appointment_id' => $appointment->id,
                'to' => $appointment->attendee_phone,
                'user_phone_number_id' => $userConfig->phone_number_id,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send aftercare WhatsApp', [
                'appointment_id' => $appointment->id,
                'to' => $appointment->attendee_phone,
                'error' => $e->getMessage(),
            ]);
            // Don't throw for SMS/WhatsApp as they're not fully implemented
        }
    }

    protected function sendWhatsAppMessage(
        \App\Models\WhatsAppAdminConfig $adminConfig,
        \App\Models\WhatsAppConfiguration $userConfig,
        string $phoneNumber,
        string $message
    ): void {
        $apiVersion = $adminConfig->api_version ?? 'v21.0';
        $apiUrl = "https://graph.facebook.com/{$apiVersion}/{$userConfig->phone_number_id}/messages";
        
        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => $this->formatPhoneNumber($phoneNumber),
            'type' => 'text',
            'text' => [
                'body' => $message,
            ],
        ];

        $response = \Illuminate\Support\Facades\Http::withHeaders([
            'Authorization' => 'Bearer ' . $adminConfig->access_token,
            'Content-Type' => 'application/json',
        ])->post($apiUrl, $payload);

        if (!$response->successful()) {
            throw new \Exception('WhatsApp API error: ' . $response->body());
        }
    }

    protected function formatPhoneNumber(string $phoneNumber): string
    {
        // Remove all non-numeric characters
        $phoneNumber = preg_replace('/[^0-9]/', '', $phoneNumber);
        
        // If it doesn't start with country code, assume it needs one
        // This is a basic implementation - you may want to enhance this
        if (strlen($phoneNumber) < 10) {
            return $phoneNumber;
        }
        
        return $phoneNumber;
    }

    protected function formatEmailBody(string $body, Appointment $appointment, AftercareTemplate $template): string
    {
        // Add rebooking link if enabled
        if ($template->include_rebooking_link) {
            $rebookingUrl = $appointment->user?->bookingSetting?->booking_url ?? url('/book');
            $ctaText = $template->rebooking_cta_text ?? 'Book Your Next Appointment';

            $rebookingButton = <<<HTML
                <div style="margin: 30px 0; text-align: center;">
                    <a href="{$rebookingUrl}"
                       style="display: inline-block;
                              padding: 12px 30px;
                              background-color: #3b82f6;
                              color: white;
                              text-decoration: none;
                              border-radius: 6px;
                              font-weight: 600;">
                        {$ctaText}
                    </a>
                </div>
            HTML;

            $body .= $rebookingButton;
        }

        $businessName = $appointment->user?->name ?? 'Our Business';

        // Wrap in basic email template
        return <<<HTML
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
            </head>
            <body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
                <div style="background-color: #f9fafb; padding: 30px; border-radius: 10px;">
                    {$body}
                </div>
                <div style="margin-top: 20px; text-align: center; color: #666; font-size: 12px;">
                    <p>This is an automated aftercare message from {$businessName}</p>
                </div>
            </body>
            </html>
        HTML;
    }
}
