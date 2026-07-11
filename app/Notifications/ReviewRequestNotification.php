<?php

namespace App\Notifications;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReviewRequestNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Appointment $appointment
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $businessName = $this->appointment->user?->bookingSetting?->display_name
            ?? $this->appointment->user?->name
            ?? 'us';

        return (new MailMessage)
            ->subject('How was your appointment?')
            ->greeting('Hello ' . ($this->appointment->attendee_name ?? 'there') . '!')
            ->line("Thank you for choosing {$businessName}.")
            ->line('Your feedback helps the business improve and helps future clients know what to expect.')
            ->action('Leave a Review', route('review.show', ['token' => $this->appointment->confirmation_token]))
            ->line('Thank you for taking a moment to share your experience.');
    }
}
