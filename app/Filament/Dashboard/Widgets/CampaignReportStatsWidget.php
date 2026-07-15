<?php

namespace App\Filament\Dashboard\Widgets;

use App\Services\CampaignAudienceService;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CampaignReportStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $audiences = collect(app(CampaignAudienceService::class)->summaryForUser(auth()->user()));
        $recommended = app(CampaignAudienceService::class)->recommendationsForUser(auth()->user())['recommended'] ?? null;

        $contacts = (int) $audiences->sum('contact_count');
        $sent = (int) $audiences->sum('sent');
        $booked = (int) $audiences->sum('booked');
        $collected = (float) $audiences->sum('collected');
        $conversion = $sent > 0 ? round(($booked / $sent) * 100, 1) : 0;

        return [
            Stat::make('Recommended next', $recommended['name'] ?? 'Build an audience')
                ->description($recommended['reason'] ?? 'Collect client activity to unlock smarter recommendations.')
                ->descriptionIcon('heroicon-m-sparkles')
                ->color($recommended ? 'primary' : 'gray'),

            Stat::make('Ready contacts', number_format($contacts))
                ->description('Contacts eligible for campaign audiences')
                ->descriptionIcon('heroicon-m-user-group')
                ->color($contacts > 0 ? 'success' : 'gray'),

            Stat::make('Bookings from campaigns', number_format($booked))
                ->description($sent > 0 ? "{$conversion}% conversion from {$sent} sent" : 'No campaign emails sent yet')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color($booked > 0 ? 'success' : 'warning'),

            Stat::make('Collected revenue', '$' . number_format($collected, 2))
                ->description('Attributed to campaign audiences')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color($collected > 0 ? 'success' : 'gray'),
        ];
    }
}
