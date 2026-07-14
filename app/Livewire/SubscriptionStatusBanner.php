<?php

namespace App\Livewire;

use App\Constants\SubscriptionStatus;
use App\Constants\SubscriptionType;
use App\Filament\Dashboard\Resources\Subscriptions\SubscriptionResource;
use Livewire\Attributes\Lazy;
use Livewire\Component;

#[Lazy]
class SubscriptionStatusBanner extends Component
{
    public function render()
    {
        $user = auth()->user();

        if (! $user || $user->is_admin) {
            return view('livewire.subscription-status-banner', ['state' => null]);
        }

        $hasPaidSubscription = $user->subscriptions()
            ->where('type', SubscriptionType::PAYMENT_PROVIDER_MANAGED)
            ->where('status', SubscriptionStatus::ACTIVE->value)
            ->where('ends_at', '>', now())
            ->exists();

        if ($hasPaidSubscription) {
            return view('livewire.subscription-status-banner', ['state' => null]);
        }

        $trial = $user->subscriptions()
            ->where('type', SubscriptionType::LOCALLY_MANAGED)
            ->where('status', SubscriptionStatus::ACTIVE->value)
            ->where('ends_at', '>', now())
            ->latest('ends_at')
            ->first();

        if ($trial) {
            return view('livewire.subscription-status-banner', [
                'state' => 'trialing',
                'daysLeft' => max(0, (int) now()->diffInDays($trial->ends_at, false) + 1),
                'planName' => $trial->plan?->name,
                'ctaUrl' => SubscriptionResource::getUrl('index'),
            ]);
        }

        $everHadSubscription = $user->subscriptions()->exists();

        return view('livewire.subscription-status-banner', [
            'state' => $everHadSubscription ? 'locked' : 'no-plan',
            'ctaUrl' => SubscriptionResource::getUrl('index'),
        ]);
    }
}
