<?php

namespace App\Observers;

use App\Models\Appointment;
use App\Models\NotificationPreference;
use App\Services\CalendarSyncService;
use App\Services\ContactSyncService;
use App\Services\MessagingService;
use Illuminate\Support\Facades\Log;

class AppointmentObserver
{
    public function __construct(
        protected ContactSyncService $contactSyncService,
        protected CalendarSyncService $calendarSyncService,
        protected MessagingService $messaging
    ) {}

    /**
     * Handle the Appointment "created" event.
     * Auto-sync appointment to contacts and Google Calendar
     */
    public function created(Appointment $appointment): void
    {
        // Auto-sync to contacts
        $this->contactSyncService->syncAppointmentToContact($appointment);

        // Auto-sync to Google Calendar
        if (in_array($appointment->status, ['confirmed', 'scheduled'])) {
            $this->syncToCalendar($appointment);
        }

        // Messaging notifications
        $this->sendBookingNotifications($appointment);
    }

    /**
     * Handle the Appointment "updated" event.
     * Sync if attendee info changed or appointment details changed
     */
    public function updated(Appointment $appointment): void
    {
        // If attendee email changed or contact not linked, try to sync again
        if ($appointment->wasChanged(['attendee_email', 'attendee_name', 'attendee_phone', 'attendee_company'])) {
            $this->contactSyncService->syncAppointmentToContact($appointment);
        }

        // Check if status changed to completed - trigger aftercare + update contact
        if ($appointment->wasChanged('status') && $appointment->status === 'completed') {
            $originalStatus = $appointment->getOriginal('status');

            if ($originalStatus !== 'completed') {
                Log::info('Appointment completed — triggering aftercare and contact sync', [
                    'appointment_id' => $appointment->id,
                    'previous_status' => $originalStatus,
                ]);

                $appointment->scheduleAftercareMessage();

                // Update contact's last appointment timestamp
                if ($appointment->contact_id) {
                    \App\Models\Contact::where('id', $appointment->contact_id)
                        ->update(['last_appointment_at' => now()]);
                } elseif ($appointment->attendee_email) {
                    \App\Models\Contact::where('user_id', $appointment->user_id)
                        ->where('email', $appointment->attendee_email)
                        ->update(['last_appointment_at' => now()]);
                }
            }
        }

        // Check if status changed to no_show — chase client + flag CRM
        if ($appointment->wasChanged('status') && $appointment->status === 'no_show') {
            $originalStatus = $appointment->getOriginal('status');

            if ($originalStatus !== 'no_show') {
                Log::info('Appointment no-show — triggering chase and CRM flag', [
                    'appointment_id' => $appointment->id,
                    'previous_status' => $originalStatus,
                ]);

                $this->handleNoShow($appointment);
            }
        }

        // Sync to Google Calendar if relevant fields changed
        if ($appointment->wasChanged(['title', 'description', 'start_datetime', 'end_datetime', 'location', 'venue_id', 'room_name', 'meeting_url', 'status'])) {
            // If status changed to cancelled, delete from calendar
            if ($appointment->status === 'cancelled' && $appointment->calendar_event_id) {
                $this->calendarSyncService->deleteAppointmentFromCalendar($appointment);
            }
            // If status is confirmed/scheduled, sync to calendar
            elseif (in_array($appointment->status, ['confirmed', 'scheduled'])) {
                $this->syncToCalendar($appointment);
            }
        }
    }

    /**
     * Handle the Appointment "deleted" event.
     */
    public function deleted(Appointment $appointment): void
    {
        // Delete from Google Calendar
        if ($appointment->calendar_event_id) {
            $this->calendarSyncService->deleteAppointmentFromCalendar($appointment);
        }

        // Optional: You could log this as a cancellation interaction
        // For now, we keep the contact but unlink the appointment
    }

    /**
     * Handle the Appointment "restored" event.
     */
    public function restored(Appointment $appointment): void
    {
        // Re-sync if appointment is restored
        $this->contactSyncService->syncAppointmentToContact($appointment);

        // Re-sync to calendar if status is confirmed/scheduled
        if (in_array($appointment->status, ['confirmed', 'scheduled'])) {
            $this->syncToCalendar($appointment);
        }
    }

    /**
     * Handle the Appointment "force deleted" event.
     */
    public function forceDeleted(Appointment $appointment): void
    {
        // Delete from Google Calendar
        if ($appointment->calendar_event_id) {
            $this->calendarSyncService->deleteAppointmentFromCalendar($appointment);
        }

        // Permanent deletion - no action needed
    }

    /**
     * Send SMS booking confirmation to client + new booking alert to owner.
     */
    protected function sendBookingNotifications(Appointment $appointment): void
    {
        if (! $appointment->user_id) {
            return;
        }

        try {
            $prefs = NotificationPreference::forUser($appointment->user_id);

            $appointment->loadMissing(['user.businessProfile', 'appointmentType']);

            $businessName = $appointment->user?->businessProfile?->biz_trading_name
                ?? $appointment->user?->businessProfile?->biz_registered_name
                ?? $appointment->user?->name
                ?? 'your provider';

            $serviceName  = $appointment->appointmentType?->name ?? $appointment->title;
            $dateTime     = $appointment->start_datetime->format('D j M \a\t g:ia');

            // Booking confirmation to client — SMS always, WhatsApp if owner has it configured
            if ($prefs->client_booking_confirmation && $appointment->attendee_phone) {
                $this->messaging->sendBookingConfirmationToClient(
                    $appointment->attendee_phone,
                    $appointment->attendee_name ?? 'there',
                    $businessName,
                    $serviceName,
                    $dateTime
                );

                // Also send via WhatsApp if the owner's preferred channel includes it
                if ($prefs->usesWhatsApp() && $appointment->user) {
                    $this->messaging->sendWhatsApp(
                        $appointment->attendee_phone,
                        "Hi {$appointment->attendee_name}, your {$serviceName} appointment on {$dateTime} with {$businessName} is confirmed. See you then!",
                        $appointment->user
                    );
                }
            }

            // New booking alert SMS to owner
            if ($appointment->user) {
                $this->messaging->sendNewBookingAlert(
                    $appointment->user,
                    $appointment->attendee_name ?? 'A client',
                    $serviceName,
                    $dateTime
                );
            }
        } catch (\Exception $e) {
            Log::error('Failed to send booking SMS notifications', [
                'appointment_id' => $appointment->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Chase a no-show client: send a re-book message, log a CRM interaction,
     * and create a follow-up reminder for the business owner.
     */
    protected function handleNoShow(Appointment $appointment): void
    {
        try {
            $appointment->loadMissing(['user.businessProfile']);

            $businessName = $appointment->user?->businessProfile?->biz_trading_name
                ?? $appointment->user?->businessProfile?->biz_registered_name
                ?? $appointment->user?->name
                ?? 'us';

            // Build a rebooking URL from the owner's booking slug
            $bookingSlug = \App\Models\BookingSetting::where('user_id', $appointment->user_id)
                ->value('booking_slug');

            $rebookUrl = $bookingSlug
                ? route('booking.public', ['slug' => $bookingSlug])
                : null;

            $clientName = $appointment->attendee_name ?? 'there';
            $message    = "Hi {$clientName}, we missed you for your appointment today with {$businessName}."
                . ($rebookUrl ? " Rebook at your convenience: {$rebookUrl}" : ' Please get in touch to reschedule.')
                . "\n\n— {$businessName}";

            // Send chase SMS/WhatsApp to client
            if ($appointment->attendee_phone) {
                $this->messaging->notifyClient($appointment->attendee_phone, $message);
            }

            // Alert the owner
            if ($appointment->user) {
                $this->messaging->notifyOwner(
                    $appointment->user,
                    "No-show: {$clientName} missed their appointment. A chase message has been sent."
                );
            }

            // Log a CRM interaction on the linked contact
            $contact = null;
            if ($appointment->contact_id) {
                $contact = \App\Models\Contact::find($appointment->contact_id);
            } elseif ($appointment->attendee_email) {
                $contact = \App\Models\Contact::where('user_id', $appointment->user_id)
                    ->where('email', $appointment->attendee_email)
                    ->first();
            }

            if ($contact) {
                \App\Models\ContactInteraction::create([
                    'user_id'          => $appointment->user_id,
                    'contact_id'       => $contact->id,
                    'type'             => 'note',
                    'subject'          => 'Appointment no-show',
                    'description'      => "Client did not attend their appointment. Chase message sent.",
                    'interaction_date' => now(),
                    'outcome'          => 'follow_up_needed',
                ]);

                // Create a follow-up reminder for the owner to personally follow up in 2 days
                \App\Models\FollowUpReminder::create([
                    'user_id'     => $appointment->user_id,
                    'contact_id'  => $contact->id,
                    'title'       => "Follow up: {$clientName} missed appointment",
                    'description' => 'Client was a no-show. Chase message was sent — follow up if no response.',
                    'remind_at'   => now()->addDays(2),
                    'status'      => 'pending',
                    'priority'    => 'medium',
                ]);

                // Pipeline Flag — flag any open deals for this contact so the board
                // surfaces them as at-risk before the owner loses the sale.
                $openDeals = $contact->deals()->open()->get();
                foreach ($openDeals as $deal) {
                    \App\Models\FollowUpReminder::create([
                        'user_id'     => $appointment->user_id,
                        'contact_id'  => $contact->id,
                        'deal_id'     => $deal->id,
                        'title'       => "At-risk deal: {$clientName} was a no-show",
                        'description' => "Deal \"{$deal->title}\" may be at risk — client missed their appointment. Review the pipeline.",
                        'remind_at'   => now()->addDay(),
                        'status'      => 'pending',
                        'priority'    => 'high',
                    ]);
                }
            }

        } catch (\Exception $e) {
            Log::error('Failed to handle no-show follow-up', [
                'appointment_id' => $appointment->id,
                'error'          => $e->getMessage(),
            ]);
        }
    }

    /**
     * Sync appointment to Google Calendar
     */
    protected function syncToCalendar(Appointment $appointment): void
    {
        try {
            $result = $this->calendarSyncService->pushAppointmentToCalendar($appointment);

            if ($result['success']) {
                Log::info('Appointment synced to calendar via observer', [
                    'appointment_id' => $appointment->id,
                    'event_id' => $result['event_id'] ?? null,
                ]);
            } else {
                Log::warning('Failed to sync appointment to calendar', [
                    'appointment_id' => $appointment->id,
                    'message' => $result['message'],
                    'conflicts' => $result['conflicts'] ?? [],
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error syncing appointment to calendar in observer', [
                'appointment_id' => $appointment->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
