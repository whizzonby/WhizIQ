<?php

namespace App\Filament\Dashboard\Pages;

use App\Filament\Dashboard\Widgets\CampaignPerformanceWidget;
use Filament\Pages\Page;
use BackedEnum;
use UnitEnum;

class MarketingDashboard extends Page
{
    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-megaphone';

    protected static ?string $navigationLabel = 'Marketing Dashboard';

    protected static ?string $title = 'Marketing Analytics Dashboard';

    protected static UnitEnum|string|null $navigationGroup = 'Marketing';

    protected static bool $shouldRegisterNavigation = false;

    protected static ?int $navigationSort = 0;

    protected string $view = 'filament.dashboard.pages.marketing-dashboard';

    protected function getHeaderWidgets(): array
    {
        return [
            CampaignPerformanceWidget::class,
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [];
    }
}
