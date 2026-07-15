<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\AppointmentType;
use App\Models\BookingSetting;
use App\Models\CampaignAudience;
use App\Models\Contact;
use App\Services\AvailabilityService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PublicBookingSubmissionController extends Controller
{
    public function __invoke(Request $request, AvailabilityService $availabilityService, string $slug): RedirectResponse
    {
        $bookingSetting = BookingSetting::where('booking_slug', $slug)
            ->where('is_booking_enabled', true)
            ->firstOrFail();

        $selectedType = AppointmentType::where('user_id', $bookingSetting->user_id)
            ->where('is_active', true)
            ->findOrFail($request->integer('service'));

        $format = $selectedType->appointment_format ?? 'online';
        $isInPersonOrHybrid = in_array($format, ['in_person', 'hybrid'], true);

        $validated = $request->validate([
            'service' => 'required|integer',
            'date' => 'required|date_format:Y-m-d',
            'time' => 'required|date_format:H:i',
            'attendee_name' => 'required|string|max:255',
            'attendee_email' => 'required|email|max:255',
            'attendee_phone' => $selectedType->require_phone ? 'required|string|max:20' : 'nullable|string|max:20',
            'attendee_company' => $selectedType->require_company ? 'required|string|max:255' : 'nullable|string|max:255',
            'location' => $isInPersonOrHybrid ? 'required|string|max:500' : 'nullable|string|max:500',
            'notes' => 'nullable|string|max:1000',
            'campaign' => 'nullable|string|max:255',
            'contact' => 'nullable|integer',
            'audience' => 'nullable|integer',
            'reschedule' => 'nullable|string|max:255',
        ]);

        $startDateTime = Carbon::parse($validated['date'] . ' ' . $validated['time']);
        $endDateTime = $startDateTime->copy()->addMinutes($selectedType->total_duration_minutes);

        $availableSlots = $availabilityService->getAvailableSlots(
            $bookingSetting->user_id,
            Carbon::parse($validated['date']),
            $selectedType->total_duration_minutes,
            $bookingSetting->min_booking_notice_hours ?? 0
        );

        $isAvailableSlot = collect($availableSlots)->contains(
            fn ($slot) => ($slot['time'] ?? null) === $validated['time']
        );

        if (! $isAvailableSlot || $availabilityService->isSlotBooked($bookingSetting->user_id, $startDateTime, $endDateTime, null)) {
            return redirect()
                ->route('booking.public', [
                    'slug' => $bookingSetting->booking_slug,
                    'service' => $selectedType->id,
                    'date' => $validated['date'],
                ])
                ->with('error', 'This time slot is no longer available. Please select another time.');
        }

        $confirmationToken = Str::random(32);

        $appointment = Appointment::create([
            'user_id' => $bookingSetting->user_id,
            'contact_id' => $this->resolveSourceContactId($bookingSetting, $request->integer('contact')),
            'campaign_audience_id' => $this->resolveSourceAudienceId($bookingSetting, $request->integer('audience')),
            'appointment_type_id' => $selectedType->id,
            'appointment_format' => $format,
            'location' => $validated['location'] ?? null,
            'title' => $selectedType->name . ' with ' . $validated['attendee_name'],
            'description' => $selectedType->description,
            'start_datetime' => $startDateTime,
            'end_datetime' => $endDateTime,
            'timezone' => $bookingSetting->timezone,
            'status' => $bookingSetting->require_approval ? 'scheduled' : 'confirmed',
            'attendee_name' => $validated['attendee_name'],
            'attendee_email' => $validated['attendee_email'],
            'attendee_phone' => $validated['attendee_phone'] ?? null,
            'attendee_company' => $validated['attendee_company'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'confirmation_token' => $confirmationToken,
            'booked_via' => filled($validated['campaign'] ?? null) ? 'campaign_' . $validated['campaign'] : 'public_form',
        ]);

        $this->cancelRescheduledAppointment($bookingSetting, $validated['reschedule'] ?? null);

        \App\Jobs\ProcessNewAppointment::dispatch($appointment, $bookingSetting);

        return redirect()
            ->route('appointment.manage', ['token' => $confirmationToken])
            ->with('success', 'Your appointment has been booked.');
    }

    protected function resolveSourceContactId(BookingSetting $bookingSetting, ?int $contactId): ?int
    {
        if (! $contactId) {
            return null;
        }

        return Contact::where('user_id', $bookingSetting->user_id)
            ->where('id', $contactId)
            ->value('id');
    }

    protected function resolveSourceAudienceId(BookingSetting $bookingSetting, ?int $audienceId): ?int
    {
        if (! $audienceId) {
            return null;
        }

        return CampaignAudience::where('user_id', $bookingSetting->user_id)
            ->where('id', $audienceId)
            ->value('id');
    }

    protected function cancelRescheduledAppointment(BookingSetting $bookingSetting, ?string $rescheduleToken): void
    {
        if (! $rescheduleToken) {
            return;
        }

        $original = Appointment::where('user_id', $bookingSetting->user_id)
            ->where('confirmation_token', $rescheduleToken)
            ->whereIn('status', ['scheduled', 'confirmed'])
            ->first();

        $original?->cancel('Client rescheduled to a new time');
    }
}
