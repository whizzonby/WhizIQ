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
        if (! $this->selectedTypeId) {
            return null;
        }

        $bookingSetting = $this->getBookingSetting();

        return AppointmentType::where('user_id', $bookingSetting->user_id)
            ->where('is_active', true)
            ->find($this->selectedTypeId);
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

        $this->sourceCampaign = request()->string('campaign')->trim()->value() ?: null;
        $this->sourceContactId = request()->integer('contact') ?: null;
        $this->sourceAudienceId = request()->integer('audience') ?: null;
        $this->rescheduleToken = request()->string('reschedule')->trim()->value() ?: null;

        $preselectedId = request()->integer('service') ?: null;
        if ($preselectedId) {
            $this->selectType($preselectedId);
        }

        $preselectedDate = request()->string('date')->trim()->value() ?: null;
        if ($preselectedId && $preselectedDate) {
            $this->selectDate($preselectedDate);
        }

        $preselectedTime = request()->string('time')->trim()->value() ?: null;
        if ($preselectedId && $preselectedDate && $preselectedTime) {
            $this->selectTime($preselectedTime);
        }
    }

    public function selectType($typeId)
    {
        $bookingSetting = $this->getBookingSetting();
        $type = AppointmentType::where('user_id', $bookingSetting->user_id)
            ->where('is_active', true)
            ->findOrFail($typeId);

        $this->selectedTypeId = $type->id;
        $this->selectedDate = null;
        $this->selectedTime = null;
        $this->availableSlots = [];

        $this->availableDates = $this->availabilityService->getAvailableDates(
            $bookingSetting->user_id,
            $bookingSetting->max_booking_days_ahead ?? 30
        );

        $this->currentStep = 2;
    }

    public function selectDate($date)
    {
        $selectedType = $this->getSelectedType();
        if (! $selectedType) {
            abort(404, 'Selected service is no longer available.');
        }

        if (empty($this->availableDates)) {
            $this->selectType($selectedType->id);
        }

        $availableDateValues = collect($this->availableDates)->pluck('date');
        if (! $availableDateValues->contains($date)) {
            $this->availableSlots = [];
            $this->selectedDate = null;
            $this->selectedTime = null;
            session()->flash('error', 'That date is not available. Please choose another date.');
            return;
        }

        $this->selectedDate = $date;
        $this->selectedTime = null;
        $bookingSetting = $this->getBookingSetting();
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
        if (empty($this->availableSlots) && $this->selectedDate) {
            $this->selectDate($this->selectedDate);
        }

        if (! collect($this->availableSlots)->contains(fn ($slot) => ($slot['time'] ?? null) === $time)) {
            session()->flash('error', 'That time is no longer available. Please choose another time.');
            $this->currentStep = 2;
            return;
        }

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

    public function bookingFlowUrl(array $overrides = []): string
    {
        $parameters = array_merge([
            'slug' => $this->bookingSlug,
            'service' => $this->selectedTypeId,
            'date' => $this->selectedDate,
            'time' => $this->selectedTime,
            'campaign' => $this->sourceCampaign,
            'contact' => $this->sourceContactId,
            'audience' => $this->sourceAudienceId,
            'reschedule' => $this->rescheduleToken,
        ], $overrides);

        return route('booking.public', array_filter($parameters, fn ($value) => filled($value)));
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
            ->limit(3)
            ->get();

        return view('livewire.public-booking', [
            'bookingSetting' => $bookingSetting,
            'selectedType' => $selectedType,
            'appointmentTypes' => $appointmentTypes,
            'recentReviews' => $recentReviews,
        ])->layout('components.layouts.app');
    }
}
