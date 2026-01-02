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
    public $bookingSetting;
    public $currentStep = 1;

    // Step 1: Select appointment type
    public $selectedTypeId;
    public $selectedType;

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

    public function mount($slug)
    {
        $this->bookingSlug = $slug;

        $this->bookingSetting = BookingSetting::where('booking_slug', $slug)
            ->where('is_booking_enabled', true)
            ->first();

        if (!$this->bookingSetting) {
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
        $this->selectedType = AppointmentType::findOrFail($typeId);

        // Load available dates for the next 30 days
        $this->availableDates = $this->availabilityService->getAvailableDates(
            $this->bookingSetting->user_id,
            $this->bookingSetting->max_booking_days_ahead ?? 30
        );

        $this->currentStep = 2;
    }

    public function selectDate($date)
    {
        $this->selectedDate = $date;

        // Load available time slots for selected date
        $minNoticeHours = $this->bookingSetting->min_booking_notice_hours ?? 0;

        $this->availableSlots = $this->availabilityService->getAvailableSlots(
            $this->bookingSetting->user_id,
            Carbon::parse($date),
            $this->selectedType->total_duration_minutes,
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
        if ($this->currentStep > 1) {
            if ($this->currentStep == 3) {
                // Go back to time selection
                $this->currentStep = 2;
                $this->selectedTime = null;
            } elseif ($this->currentStep === 2) {
                $this->selectedTime = null;
                $this->availableSlots = [];
            } elseif ($this->currentStep === 1) {
                $this->selectedDate = null;
                $this->selectedTime = null;
                $this->availableSlots = [];
            }
        }
    }

    public function submitBooking()
    {
        $format = $this->selectedType->appointment_format ?? 'online';
        $isInPersonOrHybrid = in_array($format, ['in_person', 'hybrid']);

        $this->validate([
            'attendeeName' => 'required|string|max:255',
            'attendeeEmail' => 'required|email|max:255',
            'attendeePhone' => $this->selectedType->require_phone ? 'required|string|max:20' : 'nullable|string|max:20',
            'attendeeCompany' => $this->selectedType->require_company ? 'required|string|max:255' : 'nullable|string|max:255',
            'location' => $isInPersonOrHybrid ? 'required|string|max:500' : 'nullable|string|max:500',
            'notes' => 'nullable|string|max:1000',
        ]);

        $startDateTime = Carbon::parse($this->selectedDate . ' ' . $this->selectedTime);
        $endDateTime = $startDateTime->copy()->addMinutes($this->selectedType->total_duration_minutes);

        // Check if slot is still available
        if ($this->availabilityService->isSlotBooked($this->bookingSetting->user_id, $startDateTime, $endDateTime, null)) {
            session()->flash('error', 'This time slot is no longer available. Please select another time.');
            $this->currentStep = 2;
            $this->selectDate($this->selectedDate);
            return;
        }

        // Create the appointment (FAST - just database insert)
        $this->createAppointment();
    }

    protected function createAppointment()
    {
        $startDateTime = Carbon::parse($this->selectedDate . ' ' . $this->selectedTime);
        $endDateTime = $startDateTime->copy()->addMinutes($this->selectedType->total_duration_minutes);
        $format = $this->selectedType->appointment_format ?? 'online';

        $this->confirmationToken = Str::random(32);

        $appointment = Appointment::create([
            'user_id' => $this->bookingSetting->user_id,
            'appointment_type_id' => $this->selectedTypeId,
            'venue_id' => $this->selectedVenueId,
            'appointment_format' => $format,
            'location' => $this->location, // Custom location for in-person appointments
            'title' => $this->selectedType->name . ' with ' . $this->attendeeName,
            'description' => $this->selectedType->description,
            'start_datetime' => $startDateTime,
            'end_datetime' => $endDateTime,
            'timezone' => $this->bookingSetting->timezone,
            'status' => $this->bookingSetting->require_approval ? 'scheduled' : 'confirmed',
            'attendee_name' => $this->attendeeName,
            'attendee_email' => $this->attendeeEmail,
            'attendee_phone' => $this->attendeePhone,
            'attendee_company' => $this->attendeeCompany,
            'notes' => $this->notes,
            'confirmation_token' => $this->confirmationToken,
            'booked_via' => 'public_form',
        ]);

        // Load relationships for display (FAST - just queries)
        $this->createdAppointment = $appointment->load('venue', 'appointmentType');
        $this->confirmed = true;
        $this->currentStep = 4;

        // PERFORMANCE FIX: Dispatch slow operations to background job
        // This makes the booking complete instantly for the user
        \App\Jobs\ProcessNewAppointment::dispatch($appointment, $this->bookingSetting);

        // User sees confirmation page immediately while:
        // - Meeting link is being created in background
        // - Emails are being sent in background
        // These will complete within a few seconds without blocking the user
    }

    public function render()
    {
        $appointmentTypes = AppointmentType::where('user_id', $this->bookingSetting->user_id)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return view('livewire.public-booking', [
            'appointmentTypes' => $appointmentTypes,
        ])->layout('components.layouts.app');
    }
}
