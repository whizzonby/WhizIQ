<?php

namespace App\Filament\Dashboard\Widgets;

use App\Models\Contact;
use App\Models\Deal;
use App\Models\FollowUpReminder;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class ContactsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 19;

    protected static bool $isLazy = true;


    public function getHeading(): string
    {
        return '👥 Contacts Overview';
    }

    protected function getStats(): array
    {
        $userId = Auth::id();
        $cacheKey = "contacts_overview_{$userId}_" . now()->format('Y-m-d-H');

        $data = Cache::remember($cacheKey, 1800, function () use ($userId) {
            return [
                'totalContacts'  => Contact::where('user_id', $userId)->where('status', 'active')->count(),
                'clientsCount'   => Contact::where('user_id', $userId)->where('type', 'client')->count(),
                'leadsCount'     => Contact::where('user_id', $userId)->where('type', 'lead')->count(),
                'vipCount'       => Contact::where('user_id', $userId)->where('priority', 'vip')->count(),
                'followUpsNeeded'=> Contact::where('user_id', $userId)->needsFollowUp()->count(),
                'coldContacts'   => Contact::where('user_id', $userId)
                    ->where('status', 'active')
                    ->where(function ($query) {
                        $query->where('last_contact_date', '<', now()->subDays(30))
                            ->orWhereNull('last_contact_date');
                    })
                    ->count(),
            ];
        });

        $totalContacts   = $data['totalContacts'];
        $clientsCount    = $data['clientsCount'];
        $leadsCount      = $data['leadsCount'];
        $vipCount        = $data['vipCount'];
        $followUpsNeeded = $data['followUpsNeeded'];
        $coldContacts    = $data['coldContacts'];

        return [
            Stat::make('Total Contacts', $totalContacts)
                ->description("{$clientsCount} clients, {$leadsCount} leads")
                ->descriptionIcon('heroicon-m-user-group')
                ->color('primary')
                ->url(route('filament.dashboard.resources.contacts.index')),

            Stat::make('VIP Contacts', $vipCount)
                ->description('High priority relationships')
                ->descriptionIcon('heroicon-m-star')
                ->color('warning')
                ->url(route('filament.dashboard.resources.contacts.index', ['tab' => 'vip'])),

            Stat::make('Follow-Ups Needed', $followUpsNeeded)
                ->description('Contacts needing attention')
                ->descriptionIcon('heroicon-m-bell-alert')
                ->color($followUpsNeeded > 0 ? 'danger' : 'success')
                ->url(route('filament.dashboard.resources.contacts.index', ['tab' => 'needs_follow_up'])),

            Stat::make('Cold Contacts', $coldContacts)
                ->description('No contact in 30+ days')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($coldContacts > 0 ? 'danger' : 'success'),
        ];
    }
}

