<?php

namespace App\Http\Controllers\Auth\Trait;

use App\Models\User;
use Illuminate\Support\Facades\Redirect;
use Saasykit\FilamentOnboarding\Constants\OnboardingStatus;
use Saasykit\FilamentOnboarding\Models\Onboarding;

trait RedirectAwareTrait
{
    protected function getRedirectUrl(?User $user): string
    {
        // Change this if you want to redirect to a different page after login

        if (! $user) {
            return route('home');
        }

        // Skip email verification and onboarding for admins
        if ($user->is_admin) {
            if (Redirect::getIntendedUrl() !== null && rtrim(Redirect::getIntendedUrl(), '/') !== rtrim((route('home')), '/')) {
                return Redirect::getIntendedUrl();
            }
            return route('filament.admin.pages.dashboard');
        }

        // Redirect to email verification if not verified (non-admins only)
        if (! $user->hasVerifiedEmail()) {
            return route('verification.notice');
        }

        // Send users who haven't completed onboarding to the wizard
        $hasOnboarded = Onboarding::where('onboardable_type', get_class($user))
            ->where('onboardable_id', $user->id)
            ->whereIn('status', [OnboardingStatus::DONE, OnboardingStatus::SKIPPED])
            ->exists();

        if (! $hasOnboarded) {
            return route('filament.dashboard.pages.onboarding');
        }

        if (Redirect::getIntendedUrl() !== null && rtrim(Redirect::getIntendedUrl(), '/') !== rtrim((route('home')), '/')) {
            return Redirect::getIntendedUrl();
        }

        return route('filament.dashboard.pages.dashboard');
    }
}
