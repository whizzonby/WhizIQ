<?php

namespace App\Filament\Dashboard\Pages;

use App\Filament\Dashboard\Widgets\AIUsageWidget;
use App\Filament\Dashboard\Widgets\BusinessMetricsOverviewWidget;
use App\Filament\Dashboard\Widgets\MonthlyExpensesAndProfitWidget;
use App\Filament\Dashboard\Widgets\NaturalLanguageQueryWidget;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    /**
     * Only show essential overview widgets on the main dashboard
     * All other widgets are organized into their respective dashboards:
     * - CRM widgets → CRMDashboard
     * - Financial/Analytics widgets → AnalyticsDashboard
     * - Marketing widgets → MarketingDashboard
     * - Task widgets → TasksDashboard
     * - Goal widgets → GoalsDashboard
     * - Tax widgets → TaxDashboardPage
     * - Appointment widgets → AppointmentAnalyticsDashboard
     */

    /**
     * Get personalized dashboard title with user's name
     */
    public function getTitle(): string
    {
        $userName = auth()->user()->name ?? 'User';
        return "{$userName}'s Dashboard";
    }

    /**
     * Override to prevent auto-discovered widgets from appearing
     * Only return widgets we explicitly want on the main dashboard
     */
    public function getWidgets(): array
    {
        return [];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            NaturalLanguageQueryWidget::class,
            AIUsageWidget::class,
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            BusinessMetricsOverviewWidget::class,
            MonthlyExpensesAndProfitWidget::class,
        ];
    }

    public function getHeaderWidgetsColumns(): int | array
    {
        return 4;
    }

    public function getFooterWidgetsColumns(): int | array
    {
        return [
            'default' => 1,
            'sm' => 1,
            'md' => 2,
            'lg' => 4,
        ];
    }
}

