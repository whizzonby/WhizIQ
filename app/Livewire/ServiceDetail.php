<?php

namespace App\Livewire;

use App\Models\AppointmentType;
use App\Models\BookingSetting;
use Livewire\Component;

class ServiceDetail extends Component
{
    public $slug;
    public $serviceId;
    public $appointmentType;
    public $bookingSetting;

    public function mount($slug, $serviceId)
    {
        $this->slug = $slug;
        $this->serviceId = $serviceId;

        // Get the booking settings
        $this->bookingSetting = BookingSetting::where('booking_slug', $slug)
            ->where('is_booking_enabled', true)
            ->firstOrFail();

        // Get the appointment type
        $this->appointmentType = AppointmentType::where('id', $serviceId)
            ->where('user_id', $this->bookingSetting->user_id)
            ->where('is_active', true)
            ->firstOrFail();
    }

    public function bookNow()
    {
        // Redirect to the booking page with service ID as query parameter
        return redirect()->route('booking.show', [
            'slug' => $this->slug,
            'service' => $this->serviceId
        ]);
    }

    public function render()
    {
        return view('livewire.service-detail')->layout('components.layouts.app');
    }
}
