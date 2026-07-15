<?php

namespace Tests\Feature\Http\Middleware;

use App\Constants\SubscriptionStatus;
use App\Filament\Dashboard\Resources\Subscriptions\SubscriptionResource;
use App\Models\BookingSetting;
use App\Models\BusinessProfile;
use App\Models\Subscription;
use App\Models\User;
use Tests\Feature\FeatureTest;

class EnsureDashboardSubscriptionIsActiveTest extends FeatureTest
{
    public function test_onboarded_user_without_active_subscription_is_redirected_to_subscriptions(): void
    {
        $user = $this->createOnboardedUser();

        $this->actingAs($user)
            ->get(route('filament.dashboard.pages.dashboard'))
            ->assertRedirect(SubscriptionResource::getUrl('index'));
    }

    public function test_onboarded_user_with_expired_subscription_is_redirected_to_subscriptions(): void
    {
        $user = $this->createOnboardedUser();

        Subscription::factory()->for($user)->create([
            'status' => SubscriptionStatus::ACTIVE->value,
            'ends_at' => now()->subDay(),
            'trial_ends_at' => null,
        ]);

        $this->actingAs($user)
            ->get(route('filament.dashboard.pages.dashboard'))
            ->assertRedirect(SubscriptionResource::getUrl('index'));
    }

    public function test_onboarded_user_with_active_subscription_can_access_dashboard(): void
    {
        $user = $this->createOnboardedUser();

        Subscription::factory()->for($user)->create([
            'status' => SubscriptionStatus::ACTIVE->value,
            'ends_at' => now()->addMonth(),
            'trial_ends_at' => null,
        ]);

        $this->actingAs($user)
            ->get(route('filament.dashboard.pages.dashboard'))
            ->assertSuccessful();
    }

    private function createOnboardedUser(): User
    {
        $user = $this->createUser();

        BusinessProfile::create([
            'user_id' => $user->id,
            'biz_registered_name' => 'Test Business',
            'biz_country' => 'US',
            'biz_incorporation_date' => now()->subYear()->toDateString(),
            'biz_legal_type' => 'sole_trader',
            'comp_tax_cycle' => 'annually',
        ]);

        BookingSetting::create([
            'user_id' => $user->id,
            'display_name' => 'Test Business',
            'currency' => 'USD',
        ]);

        return $user;
    }
}
