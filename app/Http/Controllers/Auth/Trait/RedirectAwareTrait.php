<?php

namespace App\Http\Controllers\Auth\Trait;

use App\Models\User;
use Illuminate\Support\Facades\Redirect;

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

        // Check if onboarding is required (only for non-admin users)
        if (! $user->onboardingData) {
            return route('filament.dashboard.pages.onboarding');
        }

        // Check if user has active subscription (only for non-admin users)
        // If no subscription, redirect to plans page instead of dashboard
        if (! $user->isSubscribed()) {
            return route('filament.dashboard.resources.subscriptions.index');
        }

        if (Redirect::getIntendedUrl() !== null && rtrim(Redirect::getIntendedUrl(), '/') !== rtrim((route('home')), '/')) {
            return Redirect::getIntendedUrl();
        }

        return route('filament.dashboard.pages.dashboard');
    }
}
