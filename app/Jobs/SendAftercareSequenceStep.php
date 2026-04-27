<?php

namespace App\Jobs;

use App\Models\Appointment;
use App\Models\AftercareLog;
use App\Models\AftercareSequenceStep;
use App\Models\NotificationPreference;
use App\Services\MessagingService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendAftercareSequenceStep implements ShouldQueue
{
    use Queueable;

    public int $appointmentId;
    public int $stepId;
    public string $channel;
    public ?int $logId;

    public function __construct(
        Appointment           $appointment,
        AftercareSequenceStep $step,
        string                $channel,
        ?int                  $logId = null
    ) {
        $this->appointmentId = $appointment->id;
        $this->stepId        = $step->id;
        $this->channel       = $channel;
        $this->logId         = $logId;
    }

    public function handle(): void
    {
        try {
            $appointment = Appointment::with(['appointmentType', 'user'])->findOrFail($this->appointmentId);
            $step        = AftercareSequenceStep::findOrFail($this->stepId);

            if ($appointment->status !== 'completed') {
                Log::warning('Sequence step skipped — appointment not completed', [
                    'appointment_id' => $appointment->id,
                    'step_id'        => $step->id,
                ]);
                AftercareLog::find($this->logId)?->markSkipped('Appointment no longer completed');
                return;
            }

            if (!$step->is_active || !$step->sequence?->is_active) {
                Log::warning('Sequence step skipped — step or sequence inactive', [
                    'appointment_id' => $appointment->id,
                    'step_id'        => $step->id,
                ]);
                AftercareLog::find($this->logId)?->markSkipped('Step or sequence inactive');
                return;
            }

            $message = $step->replaceVariables($step->message_body, $appointment);

            match ($this->channel) {
                'whatsapp' => $this->sendWhatsApp($appointment, $message),
                'sms'      => $this->sendSms($appointment, $message),
                default    => throw new \Exception("Unknown channel: {$this->channel}"),
            };

            AftercareLog::find($this->logId)?->markSent();

            Log::info('Aftercare sequence step sent', [
                'appointment_id' => $appointment->id,
                'step_id'        => $step->id,
                'channel'        => $this->channel,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send aftercare sequence step', [
                'appointment_id' => $this->appointmentId,
                'step_id'        => $this->stepId,
                'channel'        => $this->channel,
                'error'          => $e->getMessage(),
            ]);

            AftercareLog::find($this->logId)?->markFailed($e->getMessage());
            throw $e;
        }
    }

    private function sendWhatsApp(Appointment $appointment, string $message): void
    {
        if (!$appointment->attendee_phone) {
            AftercareLog::find($this->logId)?->markSkipped('No attendee phone');
            return;
        }

        $adminConfig = \App\Models\WhatsAppAdminConfig::getConfig();
        if (!$adminConfig || !$adminConfig->isConfigured() || !$adminConfig->is_active) {
            AftercareLog::find($this->logId)?->markSkipped('WhatsApp admin not configured');
            return;
        }

        $userConfig = \App\Models\WhatsAppConfiguration::forUser($appointment->user_id);
        if (!$userConfig || empty($userConfig->phone_number_id) || !$userConfig->is_active) {
            AftercareLog::find($this->logId)?->markSkipped('User WhatsApp not connected');
            return;
        }

        $apiVersion = $adminConfig->api_version ?? 'v21.0';
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $adminConfig->access_token,
            'Content-Type'  => 'application/json',
        ])->post("https://graph.facebook.com/{$apiVersion}/{$userConfig->phone_number_id}/messages", [
            'messaging_product' => 'whatsapp',
            'to'                => preg_replace('/[^0-9]/', '', $appointment->attendee_phone),
            'type'              => 'text',
            'text'              => ['body' => $message],
        ]);

        if (!$response->successful()) {
            throw new \Exception('WhatsApp API error: ' . $response->body());
        }
    }

    private function sendSms(Appointment $appointment, string $message): void
    {
        if (!$appointment->attendee_phone) {
            AftercareLog::find($this->logId)?->markSkipped('No attendee phone');
            return;
        }

        // Respect global SMS aftercare toggle
        $prefs = NotificationPreference::forUser($appointment->user_id);
        if (!$prefs->client_aftercare_enabled) {
            AftercareLog::find($this->logId)?->markSkipped('SMS aftercare disabled by user');
            return;
        }

        app(MessagingService::class)->notifyClient($appointment->attendee_phone, $message);
    }
}
