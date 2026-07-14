<?php

namespace App\Notifications;

use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LocalTrialEndedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Subscription $subscription,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $planName = $this->subscription->plan?->name ?? 'your plan';

        return (new MailMessage)
            ->subject('Your free trial has ended')
            ->greeting("Hello {$notifiable->name},")
            ->line("Your free trial of {$planName} has ended.")
            ->line('You can still view everything you\'ve already added, but creating new records is paused until you choose a plan.')
            ->action('Choose a plan', route('checkout.convert-local-subscription', ['subscriptionUuid' => $this->subscription->uuid]))
            ->line('Pick a plan whenever you\'re ready to keep going.')
            ->salutation("Best regards,\n" . config('app.name'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'subscription_id' => $this->subscription->id,
            'plan_name' => $this->subscription->plan?->name,
            'message' => 'Your free trial has ended. Choose a plan to keep creating new records.',
        ];
    }
}
