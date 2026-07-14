<?php

namespace App\Listeners\Subscription;

use App\Events\Subscription\LocalSubscriptionEnded;
use App\Notifications\LocalTrialEndedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendLocalTrialEndedNotification implements ShouldQueue
{
    public function handle(LocalSubscriptionEnded $event): void
    {
        $user = $event->subscription->user;

        if (! $user || $user->is_admin) {
            return;
        }

        $user->notify(new LocalTrialEndedNotification($event->subscription));
    }
}
