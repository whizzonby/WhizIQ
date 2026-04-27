<?php

namespace App\Console\Commands;

use App\Models\Appointment;
use App\Models\NotificationPreference;
use App\Notifications\AppointmentReminderNotification;
use App\Services\MessagingService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

class SendAppointmentReminders extends Command
{
    protected $signature = 'appointments:send-reminders {--hours=24 : Hours before appointment to send reminder}';

    protected $description = 'Send reminders for upcoming appointments via email and SMS/WhatsApp';

    public function handle(MessagingService $messaging)
    {
        $hoursBeforeAppointment = (int) $this->option('hours');
        $this->info("Checking for appointment reminders ({$hoursBeforeAppointment} hours before)...");

        $targetTime = now()->addHours($hoursBeforeAppointment);

        $appointments = Appointment::whereIn('status', ['confirmed', 'scheduled'])
            ->whereBetween('start_datetime', [
                $targetTime->copy()->subMinutes(30),
                $targetTime->copy()->addMinutes(30)
            ])
            ->where(function ($query) use ($hoursBeforeAppointment) {
                $query->whereNull('reminder_sent_at')
                    ->orWhere('reminder_sent_at', '<', now()->subHours($hoursBeforeAppointment + 1));
            })
            ->with(['appointmentType', 'venue', 'user.businessProfile'])
            ->get();

        if ($appointments->isEmpty()) {
            $this->info('No reminders to send.');
            return 0;
        }

        $count = 0;

        foreach ($appointments as $appointment) {
            try {
                // Email reminder to attendee
                if ($appointment->attendee_email) {
                    Notification::route('mail', $appointment->attendee_email)
                        ->notify(new AppointmentReminderNotification($appointment));
                }

                // SMS reminder to client (if owner has client reminders enabled)
                if ($appointment->attendee_phone && $appointment->user) {
                    $prefs = NotificationPreference::forUser($appointment->user_id);

                    if ($prefs->client_appointment_reminders) {
                        $businessName = $appointment->user->businessProfile?->biz_trading_name
                            ?? $appointment->user->businessProfile?->biz_registered_name
                            ?? $appointment->user->name;

                        $serviceName = $appointment->appointmentType?->name ?? $appointment->title;
                        $dateTime = $appointment->start_datetime->format('D j M \a\t g:ia');

                        $messaging->sendAppointmentReminderToClient(
                            $appointment->attendee_phone,
                            $appointment->attendee_name ?? 'there',
                            $businessName,
                            $serviceName,
                            $dateTime,
                            $hoursBeforeAppointment
                        );
                    }
                }

                $appointment->update(['reminder_sent_at' => now()]);
                $this->line("✓ Reminder sent: {$appointment->title} → {$appointment->attendee_email}");
                $count++;
            } catch (\Exception $e) {
                $this->error("✗ Failed: {$appointment->title} — {$e->getMessage()}");
            }
        }

        $this->info("Successfully sent {$count} appointment reminder(s).");
        return 0;
    }
}
