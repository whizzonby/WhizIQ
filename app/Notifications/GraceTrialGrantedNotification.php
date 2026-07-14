<?php

namespace App\Notifications;

use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class GraceTrialGrantedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Subscription $subscription,
        public int $days,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("You now have a {$this->days}-day free trial")
            ->greeting("Hello {$notifiable->name},")
            ->line("We've started a {$this->days}-day free trial on your account, giving you full access to keep everything running exactly as it is today.")
            ->line("When it ends, you'll still be able to see everything you've already added — you'll just need to choose a plan to keep creating new records.")
            ->action('View your plan', route('filament.dashboard.resources.subscriptions.index'))
            ->line('No action is needed right now — pick a plan whenever you\'re ready.')
            ->salutation("Best regards,\n" . config('app.name'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'subscription_id' => $this->subscription->id,
            'days' => $this->days,
            'message' => "We've started a {$this->days}-day free trial on your account.",
        ];
    }
}
