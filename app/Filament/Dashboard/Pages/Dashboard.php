<?php

namespace App\Filament\Dashboard\Pages;

use App\Filament\Dashboard\Widgets\AttentionDigestWidget;
use App\Filament\Dashboard\Widgets\BusinessMetricsOverviewWidget;
use App\Filament\Dashboard\Widgets\CashVsProfitWidget;
use App\Filament\Dashboard\Widgets\FinancialAlertBarWidget;
use App\Filament\Dashboard\Widgets\GetStartedWidget;
use App\Filament\Dashboard\Widgets\MonthlyExpensesAndProfitWidget;
use App\Filament\Dashboard\Widgets\OutstandingInvoicesWidget;
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
            GetStartedWidget::class,
            AttentionDigestWidget::class,
            CashVsProfitWidget::class,
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            FinancialAlertBarWidget::class,
            OutstandingInvoicesWidget::class,
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

