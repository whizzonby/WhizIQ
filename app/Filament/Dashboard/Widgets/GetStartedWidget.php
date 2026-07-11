<?php

namespace App\Filament\Dashboard\Widgets;

use App\Models\Appointment;
use App\Models\AppointmentType;
use App\Models\AvailabilitySchedule;
use App\Models\BookingSetting;
use App\Models\Contact;
use Filament\Widgets\Widget;

class GetStartedWidget extends Widget
{
    protected static ?int $sort = -9;

    protected int|string|array $columnSpan = 'full';

    protected string $view = 'filament.dashboard.widgets.get-started-widget';

    public static function canView(): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        $hasBusinessProfile = $user->businessProfile()->exists();
        $hasService = AppointmentType::where('user_id', $user->id)
            ->where('is_active', true)
            ->exists();
        $hasWorkingHours = AvailabilitySchedule::where('user_id', $user->id)
            ->where('is_available', true)
            ->exists();
        $hasLiveBookingPage = BookingSetting::where('user_id', $user->id)
            ->where('is_booking_enabled', true)
            ->exists();
        $hasPublicBooking = Appointment::where('user_id', $user->id)
            ->where('booked_via', 'public_form')
            ->exists();
        $hasClient = Contact::where('user_id', $user->id)->exists();

        return ! (
            $hasBusinessProfile
            && $hasService
            && $hasWorkingHours
            && $hasLiveBookingPage
            && $hasPublicBooking
            && $hasClient
        );
    }

    public function getSteps(): array
    {
        $user = auth()->user();
        $userId = $user?->id;
        $bookingSetting = $userId
            ? BookingSetting::where('user_id', $userId)->first()
            : null;
        $bookingUrl = $bookingSetting?->is_booking_enabled
            ? $bookingSetting->booking_url
            : route('filament.dashboard.pages.booking-settings-page');

        return [
            [
                'icon' => 'heroicon-o-building-storefront',
                'label' => 'Business profile',
                'url' => route('filament.dashboard.pages.onboarding'),
                'complete' => (bool) $user?->businessProfile,
            ],
            [
                'icon' => 'heroicon-o-briefcase',
                'label' => 'First service',
                'url' => route('filament.dashboard.resources.appointment-types.index'),
                'complete' => $userId
                    ? AppointmentType::where('user_id', $userId)->where('is_active', true)->exists()
                    : false,
            ],
            [
                'icon' => 'heroicon-o-clock',
                'label' => 'Working hours',
                'url' => route('filament.dashboard.pages.booking-settings-page'),
                'complete' => $userId
                    ? AvailabilitySchedule::where('user_id', $userId)->where('is_available', true)->exists()
                    : false,
            ],
            [
                'icon' => 'heroicon-o-link',
                'label' => 'Booking page live',
                'url' => route('filament.dashboard.pages.booking-settings-page'),
                'complete' => (bool) $bookingSetting?->is_booking_enabled,
            ],
            [
                'icon' => 'heroicon-o-arrow-top-right-on-square',
                'label' => 'Test booking',
                'url' => $bookingUrl,
                'complete' => $userId
                    ? Appointment::where('user_id', $userId)->where('booked_via', 'public_form')->exists()
                    : false,
            ],
            [
                'icon' => 'heroicon-o-user-plus',
                'label' => 'First client',
                'url' => route('filament.dashboard.resources.contacts.create'),
                'complete' => $userId
                    ? Contact::where('user_id', $userId)->exists()
                    : false,
            ],
        ];
    }
}
