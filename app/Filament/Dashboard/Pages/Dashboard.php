<?php

namespace App\Filament\Dashboard\Pages;

use App\Filament\Dashboard\Widgets\AttentionDigestWidget;
use App\Filament\Dashboard\Widgets\BusinessMetricsOverviewWidget;
use App\Filament\Dashboard\Widgets\BusinessPerformanceTrendWidget;
use App\Filament\Dashboard\Widgets\CampaignPerformanceWidget;
use App\Filament\Dashboard\Widgets\CollectionHealthWidget;
use App\Filament\Dashboard\Widgets\ContactsOverviewWidget;
use App\Filament\Dashboard\Widgets\DashboardGreetingWidget;
use App\Filament\Dashboard\Widgets\DealPipelineWidget;
use App\Filament\Dashboard\Widgets\FinancialAlertBarWidget;
use App\Filament\Dashboard\Widgets\FinancialSnapshotWidget;
use App\Filament\Dashboard\Widgets\GetStartedWidget;
use App\Filament\Dashboard\Widgets\ProfitabilityRatiosWidget;
use App\Filament\Dashboard\Widgets\RecentAppointmentsWidget;
use App\Services\DemoDataService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
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

    public function getTitle(): string
    {
        return '';
    }

    public function getWidgets(): array
    {
        return [];
    }

    protected function getHeaderActions(): array
    {
        $user = auth()->user();

        $actions = [
            Action::make('new_invoice')
                ->label('New Invoice')
                ->icon('heroicon-o-document-plus')
                ->color('primary')
                ->url(fn () => route('filament.dashboard.pages.invoice-builder-page')),

            Action::make('new_appointment')
                ->label('Book Appointment')
                ->icon('heroicon-o-calendar')
                ->color('success')
                ->url(fn () => route('filament.dashboard.resources.appointments.create')),

            Action::make('add_client')
                ->label('Add Client')
                ->icon('heroicon-o-user-plus')
                ->color('info')
                ->url(fn () => route('filament.dashboard.resources.contacts.create')),
        ];

        if ($user?->has_demo_data) {
            $actions[] = Action::make('clear_demo_data')
                ->label('Remove Sample Data')
                ->icon('heroicon-o-trash')
                ->color('gray')
                ->requiresConfirmation()
                ->modalHeading('Remove sample data?')
                ->modalDescription('This will permanently delete all the example clients, invoices, expenses, and tasks that were loaded when you signed up. Your own data will not be affected.')
                ->modalSubmitActionLabel('Yes, remove it')
                ->action(function () use ($user) {
                    app(DemoDataService::class)->clearForUser($user);

                    Notification::make()
                        ->title('Sample data removed')
                        ->body('Your dashboard now shows only your real data.')
                        ->success()
                        ->send();

                    $this->redirect(route('filament.dashboard.pages.dashboard'));
                });
        }

        return $actions;
    }

    protected function getHeaderWidgets(): array
    {
        return [
            DashboardGreetingWidget::class,
            GetStartedWidget::class,
            FinancialAlertBarWidget::class,
            FinancialSnapshotWidget::class,
            CollectionHealthWidget::class,
            DealPipelineWidget::class,
            AttentionDigestWidget::class,
            CampaignPerformanceWidget::class,
            ContactsOverviewWidget::class,
            RecentAppointmentsWidget::class,
            BusinessMetricsOverviewWidget::class,
            BusinessPerformanceTrendWidget::class,
            ProfitabilityRatiosWidget::class,
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [];
    }

    public function getHeaderWidgetsColumns(): int | array
    {
        return 1;
    }
}
