<?php

namespace App\Services;

use App\Jobs\SendAftercareMessage;
use App\Jobs\SendAftercareSequenceStep;
use App\Models\AftercareLog;
use App\Models\AftercareSequence;
use App\Models\AftercareTemplate;
use App\Models\Appointment;
use App\Models\Contact;
use App\Models\ContactFollowUpRule;
use Illuminate\Support\Facades\Log;

class AftercareDispatchService
{
    /**
     * Entry point called from Appointment::scheduleAftercareMessage().
     * Priority order:
     *   1. Contact-level follow-up rule (if exists and active)
     *   2. AppointmentType sequence (if assigned and active)
     *   3. AppointmentType single template (legacy path)
     */
    public static function dispatch(Appointment $appointment): void
    {
        $appointment->loadMissing(['appointmentType', 'user']);

        if (!$appointment->appointmentType?->enable_aftercare) {
            Log::debug('Aftercare skipped — not enabled', ['appointment_id' => $appointment->id]);
            return;
        }

        // Resolve the contact linked to this appointment (matched by email)
        $contact = null;
        if ($appointment->attendee_email) {
            $contact = Contact::where('user_id', $appointment->user_id)
                ->where('email', $appointment->attendee_email)
                ->first();
        }

        // 1. Contact-level rule
        if ($contact) {
            $rule = ContactFollowUpRule::where('user_id', $appointment->user_id)
                ->where('contact_id', $contact->id)
                ->where('is_active', true)
                ->first();

            if ($rule) {
                if ($rule->usesSequence() && $rule->sequence && $rule->sequence->is_active) {
                    self::dispatchSequence($appointment, $rule->sequence, $contact, $rule->preferred_channel);
                    return;
                }

                if ($rule->aftercare_template_id && $rule->template && $rule->template->is_active) {
                    self::dispatchTemplate($appointment, $rule->template, $contact, $rule->preferred_channel);
                    return;
                }
            }
        }

        // 2. AppointmentType sequence
        $appointmentType = $appointment->appointmentType;

        if ($appointmentType->aftercare_sequence_id) {
            $sequence = AftercareSequence::find($appointmentType->aftercare_sequence_id);
            if ($sequence && $sequence->is_active) {
                self::dispatchSequence($appointment, $sequence, $contact);
                return;
            }
        }

        // 3. Legacy single template
        if ($appointmentType->aftercare_template_id) {
            $template = AftercareTemplate::find($appointmentType->aftercare_template_id);
            if ($template && $template->is_active) {
                self::dispatchTemplate($appointment, $template, $contact);
                return;
            }
        }

        Log::debug('Aftercare skipped — no template or sequence configured', [
            'appointment_id'    => $appointment->id,
            'appointment_type'  => $appointmentType->id,
        ]);
    }

    private static function dispatchTemplate(
        Appointment       $appointment,
        AftercareTemplate $template,
        ?Contact          $contact = null,
        ?string           $channelOverride = null
    ): void {
        $delayMinutes = $template->getTotalDelayInMinutes();
        $sendAt       = now()->addMinutes($delayMinutes);

        $log = AftercareLog::create([
            'appointment_id'       => $appointment->id,
            'user_id'              => $appointment->user_id,
            'contact_id'           => $contact?->id,
            'aftercare_template_id'=> $template->id,
            'channel'              => $channelOverride ?? $template->channel ?? 'whatsapp',
            'status'               => AftercareLog::STATUS_PENDING,
            'scheduled_at'         => $sendAt,
        ]);

        SendAftercareMessage::dispatch($appointment, $template, $log->id, $channelOverride)
            ->delay($sendAt);

        Log::info('Aftercare template scheduled', [
            'appointment_id' => $appointment->id,
            'template_id'    => $template->id,
            'delay_minutes'  => $delayMinutes,
            'send_at'        => $sendAt->toDateTimeString(),
        ]);
    }

    private static function dispatchSequence(
        Appointment       $appointment,
        AftercareSequence $sequence,
        ?Contact          $contact = null,
        ?string           $channelOverride = null
    ): void {
        $steps = $sequence->activeSteps;

        if ($steps->isEmpty()) {
            Log::warning('Aftercare sequence has no active steps', [
                'appointment_id' => $appointment->id,
                'sequence_id'    => $sequence->id,
            ]);
            return;
        }

        foreach ($steps as $step) {
            $delayMinutes = $step->getTotalDelayInMinutes();
            $sendAt       = now()->addMinutes($delayMinutes);

            // Determine channels for this step
            $channels = [];
            if ($channelOverride) {
                $channels = [$channelOverride];
            } else {
                if ($step->send_whatsapp) $channels[] = 'whatsapp';
                if ($step->send_sms)      $channels[] = 'sms';
            }

            if (empty($channels)) {
                Log::debug('Aftercare sequence step skipped — no channel enabled', [
                    'step_id' => $step->id,
                ]);
                continue;
            }

            foreach ($channels as $channel) {
                $log = AftercareLog::create([
                    'appointment_id'            => $appointment->id,
                    'user_id'                   => $appointment->user_id,
                    'contact_id'                => $contact?->id,
                    'aftercare_sequence_step_id'=> $step->id,
                    'channel'                   => $channel,
                    'status'                    => AftercareLog::STATUS_PENDING,
                    'scheduled_at'              => $sendAt,
                ]);

                SendAftercareSequenceStep::dispatch($appointment, $step, $channel, $log->id)
                    ->delay($sendAt);
            }
        }

        Log::info('Aftercare sequence scheduled', [
            'appointment_id' => $appointment->id,
            'sequence_id'    => $sequence->id,
            'steps_count'    => $steps->count(),
        ]);
    }
}
