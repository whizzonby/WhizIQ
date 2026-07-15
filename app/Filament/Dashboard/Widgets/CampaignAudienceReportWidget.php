<?php

namespace App\Filament\Dashboard\Widgets;

use App\Services\CampaignAudienceService;
use Filament\Widgets\Widget;

class CampaignAudienceReportWidget extends Widget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected string $view = 'filament.dashboard.widgets.campaign-audience-report-widget';

    public function getReportData(): array
    {
        $service = app(CampaignAudienceService::class);
        $recommendations = $service->recommendationsForUser(auth()->user());

        return [
            'recommended' => $recommendations['recommended'] ?? null,
            'audiences' => $recommendations['audiences'] ?? [],
            'open_slot_signal' => $recommendations['open_slot_signal'] ?? null,
        ];
    }
}
