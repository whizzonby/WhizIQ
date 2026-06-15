<?php

namespace App\Livewire;

use App\Models\Appointment;
use App\Models\AppointmentType;
use App\Models\BookingSetting;
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

        $appointment = Appointment::create([
            'user_id' => $bookingSetting->user_id,
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
            'booked_via' => 'public_form',
        ]);

        $this->createdAppointment = $appointment->load('venue', 'appointmentType');
        $this->confirmed = true;
        $this->currentStep = 4;

        \App\Jobs\ProcessNewAppointment::dispatch($appointment, $bookingSetting);
    }

    public function render()
    {
        $bookingSetting = $this->getBookingSetting();
        $selectedType = $this->getSelectedType();

        $appointmentTypes = AppointmentType::where('user_id', $bookingSetting->user_id)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return view('livewire.public-booking', [
            'bookingSetting' => $bookingSetting,
            'selectedType' => $selectedType,
            'appointmentTypes' => $appointmentTypes,
        ])->layout('components.layouts.app');
    }
}
