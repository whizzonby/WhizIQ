<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\BusinessReview;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicReviewController extends Controller
{
    public function show(string $token): View
    {
        $appointment = $this->findAppointment($token);

        return view('appointments.review', [
            'appointment' => $appointment,
            'existingReview' => $appointment->businessReview,
        ]);
    }

    public function store(Request $request, string $token): RedirectResponse
    {
        $appointment = $this->findAppointment($token);

        if ($appointment->businessReview) {
            return redirect()
                ->route('review.show', ['token' => $token])
                ->with('success', 'Thank you. Your review has already been received.');
        }

        $data = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:2000'],
            'display_name' => ['nullable', 'string', 'max:120'],
        ]);

        $displayName = $data['display_name'] ?? null;

        // Auto-publish strong reviews so the "done for you" feel holds for the
        // common case; hold 3-star-and-under back so an owner can screen
        // negative or inappropriate content before it appears on their
        // public booking page.
        $isApproved = (int) $data['rating'] >= 4;

        BusinessReview::create([
            'user_id' => $appointment->user_id,
            'contact_id' => $appointment->contact_id,
            'appointment_id' => $appointment->id,
            'appointment_type_id' => $appointment->appointment_type_id,
            'display_name' => $displayName ?: ($appointment->attendee_name ?? 'Client'),
            'rating' => $data['rating'],
            'comment' => $data['comment'] ?? null,
            'is_approved' => $isApproved,
            'approved_at' => $isApproved ? now() : null,
        ]);

        if ($appointment->contact) {
            $appointment->contact->logInteraction(
                type: 'note',
                description: 'Client left a ' . $data['rating'] . '-star review.',
                interactionDate: now(),
                subject: 'Review received',
                outcome: $data['rating'] >= 4 ? 'positive' : 'neutral'
            );
        }

        return redirect()
            ->route('review.show', ['token' => $token])
            ->with('success', 'Thank you for sharing your feedback.');
    }

    protected function findAppointment(string $token): Appointment
    {
        return Appointment::where('confirmation_token', $token)
            ->where('status', 'completed')
            ->with(['appointmentType', 'businessReview', 'contact', 'user.bookingSetting'])
            ->firstOrFail();
    }
}
