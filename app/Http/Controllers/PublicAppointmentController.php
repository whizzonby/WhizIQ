<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicAppointmentController extends Controller
{
    public function manage(string $token): View
    {
        $appointment = $this->findAppointment($token);

        return view('appointments.manage', [
            'appointment' => $appointment,
            'canCancel' => $this->canCancel($appointment),
            'bookingUrl' => $appointment->user?->bookingSetting?->booking_url,
        ]);
    }

    public function cancel(Request $request, string $token): RedirectResponse
    {
        $appointment = $this->findAppointment($token);

        if (! $this->canCancel($appointment)) {
            return redirect()
                ->route('appointment.manage', ['token' => $token])
                ->with('error', 'This appointment can no longer be cancelled online.');
        }

        $appointment->cancel($request->string('reason')->trim()->value() ?: 'Cancelled by client');

        return redirect()
            ->route('appointment.manage', ['token' => $token])
            ->with('success', 'Your appointment has been cancelled.');
    }

    public function reschedule(string $token): RedirectResponse
    {
        $appointment = $this->findAppointment($token);

        if (! $this->canCancel($appointment)) {
            return redirect()
                ->route('appointment.manage', ['token' => $token])
                ->with('error', 'This appointment can no longer be rescheduled online.');
        }

        $bookingSetting = $appointment->user?->bookingSetting;

        if (! $bookingSetting || ! $bookingSetting->is_booking_enabled) {
            return redirect()
                ->route('appointment.manage', ['token' => $token])
                ->with('error', 'Online booking is not currently available for rescheduling.');
        }

        // Don't cancel yet — the original appointment stays live until the
        // client actually finishes booking a new time, so an abandoned
        // reschedule doesn't leave them with nothing on the calendar.
        return redirect()
            ->route('booking.public', [
                'slug' => $bookingSetting->booking_slug,
                'service' => $appointment->appointment_type_id,
                'reschedule' => $token,
            ])
            ->with('success', 'Choose a new time for your appointment.');
    }

    protected function findAppointment(string $token): Appointment
    {
        return Appointment::where('confirmation_token', $token)
            ->with(['appointmentType', 'businessReview', 'user.bookingSetting'])
            ->firstOrFail();
    }

    protected function canCancel(Appointment $appointment): bool
    {
        if (! in_array($appointment->status, ['scheduled', 'confirmed'], true)) {
            return false;
        }

        return $appointment->start_datetime->isFuture();
    }
}
