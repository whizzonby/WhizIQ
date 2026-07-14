<?php

namespace App\Livewire;

use App\Models\Appointment;
use App\Models\AppointmentType;
use App\Models\BookingSetting;
use App\Models\CampaignAudience;
use App\Models\Contact;
use App\Models\Venue;
use App\Services\AvailabilityService;
use App\Services\MeetingPlatform\MeetingPlatformService;
use Carbon\Carbon;
use Livewire\Component;
use Illuminate\Support\Str;

class PublicBooking extends Component
{
    public $bookingSlug;
    public $currentStep = 1;

    // Step 1: Select appointment type
    public $selectedTypeId;

    // Step 2: Select date and time
    public $selectedDate;
    public $selectedTime;
    public $availableDates = [];
    public $availableSlots = [];

    // Step 2.5: Select venue (for in-person appointments)
    public $selectedVenueId;
    public $availableVenues = [];

    // Step 3: Contact information
    public $attendeeName;
    public $attendeeEmail;
    public $attendeePhone;
    public $attendeeCompany;
    public $location; // For in-person appointments
    public $notes;

    // Confirmation
    public $confirmationToken;
    public $confirmed = false;
    public $createdAppointment = null;
    public $sourceCampaign;
    public $sourceContactId;
    public $sourceAudienceId;
    public $rescheduleToken;

    // PERFORMANCE FIX: Inject service once instead of creating multiple instances
    protected AvailabilityService $availabilityService;

    public function boot(AvailabilityService $availabilityService)
    {
        $this->availabilityService = $availabilityService;
    }

    protected function getBookingSetting(): BookingSetting
    {
        return BookingSetting::where('booking_slug', $this->bookingSlug)
            ->where('is_booking_enabled', true)
            ->firstOrFail();
    }

    protected function getSelectedType(): ?AppointmentType
    {
        return $this->selectedTypeId ? AppointmentType::find($this->selectedTypeId) : null;
    }

    public function mount($slug)
    {
        $this->bookingSlug = $slug;

        $bookingSetting = BookingSetting::where('booking_slug', $slug)
            ->where('is_booking_enabled', true)
            ->first();

        if (!$bookingSetting) {
            abort(404, 'Booking page not found or is currently disabled.');
        }

        // Check if a service was preselected (from service detail page via query param)
        if (request()->has('service')) {
            $preselectedId = request()->get('service');
            $this->selectType($preselectedId);
        }

        $this->sourceCampaign = request()->string('campaign')->trim()->value() ?: null;
        $this->sourceContactId = request()->integer('contact') ?: null;
        $this->sourceAudienceId = request()->integer('audience') ?: null;
        $this->rescheduleToken = request()->string('reschedule')->trim()->value() ?: null;
    }

    public function selectType($typeId)
    {
        $this->selectedTypeId = $typeId;
        $bookingSetting = $this->getBookingSetting();

        $this->availableDates = $this->availabilityService->getAvailableDates(
            $bookingSetting->user_id,
            $bookingSetting->max_booking_days_ahead ?? 30
        );

        $this->currentStep = 2;
    }

    public function selectDate($date)
    {
        $this->selectedDate = $date;
        $bookingSetting = $this->getBookingSetting();
        $selectedType = $this->getSelectedType();
        $minNoticeHours = $bookingSetting->min_booking_notice_hours ?? 0;

        $this->availableSlots = $this->availabilityService->getAvailableSlots(
            $bookingSetting->user_id,
            Carbon::parse($date),
            $selectedType->total_duration_minutes,
            $minNoticeHours
        );
    }

    public function selectTime($time)
    {
        $this->selectedTime = $time;

        // NEW: Skip venue selection - go directly to contact info
        // Venues are now optional, attendees can enter custom location
        $this->currentStep = 3;
    }

    public function selectVenue($venueId)
    {
        $this->selectedVenueId = $venueId;
        $this->currentStep = 3;
    }

    public function goBack()
    {
        if ($this->currentStep === 3) {
            $this->currentStep = 2;
            $this->selectedTime = null;
        } elseif ($this->currentStep === 2) {
            $this->currentStep = 1;
            $this->selectedDate = null;
            $this->selectedTime = null;
            $this->availableSlots = [];
        }
    }

    public function submitBooking()
    {
        $selectedType = $this->getSelectedType();
        $bookingSetting = $this->getBookingSetting();
        $format = $selectedType->appointment_format ?? 'online';
        $isInPersonOrHybrid = in_array($format, ['in_person', 'hybrid']);

        $this->validate([
            'attendeeName' => 'required|string|max:255',
            'attendeeEmail' => 'required|email|max:255',
            'attendeePhone' => $selectedType->require_phone ? 'required|string|max:20' : 'nullable|string|max:20',
            'attendeeCompany' => $selectedType->require_company ? 'required|string|max:255' : 'nullable|string|max:255',
            'location' => $isInPersonOrHybrid ? 'required|string|max:500' : 'nullable|string|max:500',
            'notes' => 'nullable|string|max:1000',
        ]);

        $startDateTime = Carbon::parse($this->selectedDate . ' ' . $this->selectedTime);
        $endDateTime = $startDateTime->copy()->addMinutes($selectedType->total_duration_minutes);

        if ($this->availabilityService->isSlotBooked($bookingSetting->user_id, $startDateTime, $endDateTime, null)) {
            session()->flash('error', 'This time slot is no longer available. Please select another time.');
            $this->currentStep = 2;
            $this->selectDate($this->selectedDate);
            return;
        }

        $this->createAppointment($selectedType, $bookingSetting);
    }

    protected function createAppointment(AppointmentType $selectedType, BookingSetting $bookingSetting)
    {
        $startDateTime = Carbon::parse($this->selectedDate . ' ' . $this->selectedTime);
        $endDateTime = $startDateTime->copy()->addMinutes($selectedType->total_duration_minutes);
        $format = $selectedType->appointment_format ?? 'online';

        $this->confirmationToken = Str::random(32);
        $linkedContactId = $this->resolveSourceContactId($bookingSetting);

        $appointment = Appointment::create([
            'user_id' => $bookingSetting->user_id,
            'contact_id' => $linkedContactId,
            'campaign_audience_id' => $this->resolveSourceAudienceId($bookingSetting),
            'appointment_type_id' => $this->selectedTypeId,
            'venue_id' => $this->selectedVenueId,
            'appointment_format' => $format,
            'location' => $this->location,
            'title' => $selectedType->name . ' with ' . $this->attendeeName,
            'description' => $selectedType->description,
            'start_datetime' => $startDateTime,
            'end_datetime' => $endDateTime,
            'timezone' => $bookingSetting->timezone,
            'status' => $bookingSetting->require_approval ? 'scheduled' : 'confirmed',
            'attendee_name' => $this->attendeeName,
            'attendee_email' => $this->attendeeEmail,
            'attendee_phone' => $this->attendeePhone,
            'attendee_company' => $this->attendeeCompany,
            'notes' => $this->notes,
            'confirmation_token' => $this->confirmationToken,
            'booked_via' => $this->sourceCampaign ? 'campaign_' . $this->sourceCampaign : 'public_form',
        ]);

        $this->createdAppointment = $appointment->load('venue', 'appointmentType');
        $this->confirmed = true;
        $this->currentStep = 4;

        $this->cancelRescheduledAppointment($bookingSetting);

        \App\Jobs\ProcessNewAppointment::dispatch($appointment, $bookingSetting);
    }

    protected function cancelRescheduledAppointment(BookingSetting $bookingSetting): void
    {
        if (! $this->rescheduleToken) {
            return;
        }

        $original = Appointment::where('user_id', $bookingSetting->user_id)
            ->where('confirmation_token', $this->rescheduleToken)
            ->whereIn('status', ['scheduled', 'confirmed'])
            ->first();

        $original?->cancel('Client rescheduled to a new time');
    }

    protected function resolveSourceContactId(BookingSetting $bookingSetting): ?int
    {
        if (! $this->sourceContactId) {
            return null;
        }

        return Contact::where('user_id', $bookingSetting->user_id)
            ->where('id', $this->sourceContactId)
            ->value('id');
    }

    protected function resolveSourceAudienceId(BookingSetting $bookingSetting): ?int
    {
        if (! $this->sourceAudienceId) {
            return null;
        }

        return CampaignAudience::where('user_id', $bookingSetting->user_id)
            ->where('id', $this->sourceAudienceId)
            ->value('id');
    }

    /**
     * Soonest genuinely bookable slot across the next couple of weeks, used
     * as the hero's "Next available" moment - real data, not a placeholder.
     */
    protected function getNextAvailableSlot(BookingSetting $bookingSetting, $appointmentTypes): ?array
    {
        $type = $appointmentTypes->first();

        if (! $type) {
            return null;
        }

        for ($dayOffset = 0; $dayOffset <= 13; $dayOffset++) {
            $date = now()->copy()->addDays($dayOffset)->startOfDay();

            $slots = $this->availabilityService->getAvailableSlots(
                $bookingSetting->user_id,
                $date,
                (int) $type->total_duration_minutes,
                (int) ($bookingSetting->min_booking_notice_hours ?? 0)
            );

            if (! empty($slots)) {
                return [
                    'date' => $date,
                    'time' => $slots[0]['formatted'],
                ];
            }
        }

        return null;
    }

    public function render()
    {
        $bookingSetting = $this->getBookingSetting();
        $selectedType = $this->getSelectedType();

        $appointmentTypes = AppointmentType::where('user_id', $bookingSetting->user_id)
            ->where('is_active', true)
            ->withCount(['approvedReviews as review_count'])
            ->withAvg(['approvedReviews as approved_reviews_avg_rating'], 'rating')
            ->orderBy('sort_order')
            ->get();

        $recentReviews = \App\Models\BusinessReview::where('user_id', $bookingSetting->user_id)
            ->approved()
            ->with('appointmentType')
            ->latest()
            ->limit(9)
            ->get();

        $businessHours = \App\Models\AvailabilitySchedule::where('user_id', $bookingSetting->user_id)
            ->orderBy('day_of_week')
            ->get()
            ->keyBy('day_of_week');

        return view('livewire.public-booking', [
            'bookingSetting' => $bookingSetting,
            'selectedType' => $selectedType,
            'appointmentTypes' => $appointmentTypes,
            'recentReviews' => $recentReviews,
            'businessHours' => $businessHours,
            'nextAvailableSlot' => $this->getNextAvailableSlot($bookingSetting, $appointmentTypes),
        ])->layout('components.layouts.booking-public', ['bookingSetting' => $bookingSetting]);
    }
}
