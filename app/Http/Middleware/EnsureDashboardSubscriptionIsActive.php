<?php

namespace App\Http\Middleware;

use App\Filament\Dashboard\Resources\Subscriptions\SubscriptionResource;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureDashboardSubscriptionIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || $user->isAdmin()) {
            return $next($request);
        }

        if (! $user->hasVerifiedEmail() || ! $user->hasCompletedBusinessSetup()) {
            return $next($request);
        }

        if ($this->isAllowedDashboardRoute($request)) {
            return $next($request);
        }

        if ($user->isSubscribed() || $user->isTrialing()) {
            return $next($request);
        }

        return redirect()
            ->to(SubscriptionResource::getUrl('index'))
            ->with('warning', __('Choose a plan to continue using your dashboard.'));
    }

    private function isAllowedDashboardRoute(Request $request): bool
    {
        return $request->routeIs(
            'filament.dashboard.resources.subscriptions.*',
            'filament.dashboard.pages.onboarding'
        );
    }
}
