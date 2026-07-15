<?php

namespace App\Http\Controllers\Auth\Trait;

use App\Models\User;
use App\Filament\Dashboard\Resources\Subscriptions\SubscriptionResource;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;

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
            if ($this->hasUsableIntendedUrl()) {
                return Redirect::getIntendedUrl();
            }
            return route('filament.admin.pages.dashboard');
        }

        // Redirect to email verification if not verified (non-admins only)
        if (! $user->hasVerifiedEmail()) {
            return route('verification.notice');
        }

        if (! $user->hasCompletedBusinessSetup()) {
            return route('filament.dashboard.pages.onboarding');
        }

        if (! $user->isSubscribed() && ! $user->isTrialing()) {
            return SubscriptionResource::getUrl('index');
        }

        if ($this->hasUsableIntendedUrl()) {
            return Redirect::getIntendedUrl();
        }

        return route('filament.dashboard.pages.dashboard');
    }

    protected function hasUsableIntendedUrl(): bool
    {
        $intended = Redirect::getIntendedUrl();

        if ($intended === null) {
            return false;
        }

        if (! $this->isUsableRedirectUrl($intended)) {
            session()->forget('url.intended');

            return false;
        }

        return true;
    }

    protected function rememberIntendedUrl(?string $url): void
    {
        if ($this->isUsableRedirectUrl($url)) {
            Redirect::setIntendedUrl($url);
        }
    }

    protected function isUsableRedirectUrl(?string $url): bool
    {
        if (! $url) {
            return false;
        }

        $appHost = parse_url(config('app.url'), PHP_URL_HOST);
        $requestHost = request()->getHost();
        $urlHost = parse_url($url, PHP_URL_HOST);

        if ($urlHost && ! in_array($urlHost, array_filter([$appHost, $requestHost]), true)) {
            return false;
        }

        $path = '/' . ltrim(parse_url($url, PHP_URL_PATH) ?: '/', '/');
        $blockedPaths = [
            '/',
            parse_url(route('home'), PHP_URL_PATH) ?: '/',
            parse_url(route('login'), PHP_URL_PATH) ?: '/login',
            parse_url(route('register'), PHP_URL_PATH) ?: '/register',
            parse_url(route('password.request'), PHP_URL_PATH) ?: '/password/reset',
            parse_url(route('verification.notice'), PHP_URL_PATH) ?: '/email/verify',
        ];

        return ! in_array(rtrim($path, '/') ?: '/', array_unique(array_map(
            fn (string $blockedPath): string => rtrim($blockedPath, '/') ?: '/',
            $blockedPaths
        )), true)
            && ! Str::startsWith($path, ['/auth/', '/email/verify']);
    }
}
