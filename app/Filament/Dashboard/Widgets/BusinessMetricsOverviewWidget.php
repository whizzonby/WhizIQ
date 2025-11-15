<?php

namespace App\Filament\Dashboard\Widgets;

use App\Models\Contact;
use App\Services\FinancialMetricsCalculator;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class BusinessMetricsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;


    public function getHeading(): string
    {
        return '📊 Monthly Business Metrics Overview';
    }

    protected function getStats(): array
    {
        $user = auth()->user();
        $calculator = app(FinancialMetricsCalculator::class);

        // Check if tax settings are configured (show notification only once per session if incomplete)
        $taxSetting = $user->taxSetting;
        $sessionKey = 'tax_settings_notification_shown_' . $user->id;

        // Only show notification if:
        // 1. Tax settings are incomplete AND
        // 2. Notification hasn't been shown this session
        if ((!$taxSetting || !$taxSetting->tax_rate) && !session()->has($sessionKey)) {
            \Filament\Notifications\Notification::make()
                ->warning()
                ->title('Tax Settings Incomplete')
                ->body('Please configure your tax rate in Tax Settings for accurate financial calculations.')
                ->persistent()
                ->send();

            // Mark as shown for this session
            session()->put($sessionKey, true);
        }

        // If tax settings are completed, clear the session flag
        if ($taxSetting && $taxSetting->tax_rate) {
            session()->forget($sessionKey);
        }

        // Get comprehensive metrics with comparisons
        $metricsData = $calculator->getMetricsWithComparisons($user);
        $current = $metricsData['current'];
        $changes = $metricsData['changes'];
        $trends = $metricsData['trends'];

        // Calculate customer growth (compare complete months only)
        // Current complete month = last month (e.g., October if today is in November)
        $currentCompleteMonthStart = Carbon::now()->subMonth()->startOfMonth();
        $currentCompleteMonthEnd = Carbon::now()->subMonth()->endOfMonth();

        // Previous complete month = month before last (e.g., September if today is in November)
        $previousCompleteMonthStart = Carbon::now()->subMonths(2)->startOfMonth();
        $previousCompleteMonthEnd = Carbon::now()->subMonths(2)->endOfMonth();

        $currentMonthCustomers = Contact::where('user_id', $user->id)
            ->whereBetween('created_at', [$currentCompleteMonthStart, $currentCompleteMonthEnd])
            ->count();

        $lastMonthCustomers = Contact::where('user_id', $user->id)
            ->whereBetween('created_at', [$previousCompleteMonthStart, $previousCompleteMonthEnd])
            ->count();

        $customerGrowthPercentage = $lastMonthCustomers > 0
            ? (($currentMonthCustomers - $lastMonthCustomers) / $lastMonthCustomers) * 100
            : ($currentMonthCustomers > 0 ? 100 : 0);

        // Format the month name for display
        $currentMonthName = $currentCompleteMonthStart->format('F');

        // Get outstanding invoice amount (unpaid revenue + unpaid tax)
        $outstandingInvoiceAmount = $calculator->getOutstandingInvoiceAmount($user);

        return [
            Stat::make('Monthly Revenue', '$' . number_format($current['revenue'], 0))
                ->description($this->getChangeDescription($changes['revenue_change']))
                ->descriptionIcon($this->getChangeIcon($changes['revenue_change']))
                ->chart($trends['revenue'])
                ->color($this->getChangeColor($changes['revenue_change']))
                ->extraAttributes([
                    'class' => 'cursor-pointer hover:shadow-lg transition-shadow',
                ]),

            Stat::make('Profit Margin', number_format($current['profit_margin'], 1) . '%')
                ->description('$' . number_format($current['profit'], 0) . ' profit this month')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->chart($trends['profit'])
                ->color($this->getProfitMarginColor($current['profit_margin']))
                ->extraAttributes([
                    'class' => 'cursor-pointer hover:shadow-lg transition-shadow',
                ]),

            Stat::make('Monthly Expenses', '$' . number_format($current['expenses'], 0))
                ->description($this->getChangeDescription($changes['expenses_change']))
                ->descriptionIcon($this->getChangeIcon($changes['expenses_change']))
                ->chart($trends['expenses'])
                ->color($this->getChangeColor($changes['expenses_change'], true))
                ->extraAttributes([
                    'class' => 'cursor-pointer hover:shadow-lg transition-shadow',
                ]),

            Stat::make('Customer Growth', $currentMonthCustomers . ' in ' . $currentMonthName)
                ->description($this->getCustomerGrowthDescription($customerGrowthPercentage) . ' from previous month')
                ->descriptionIcon($this->getChangeIcon($customerGrowthPercentage))
                ->chart($this->getCustomerGrowthTrend($user))
                ->color($this->getChangeColor($customerGrowthPercentage))
                ->extraAttributes([
                    'class' => 'cursor-pointer hover:shadow-lg transition-shadow',
                ]),

            Stat::make('Outstanding Invoice Amount', '$' . number_format($outstandingInvoiceAmount, 0))
                ->description('Unpaid invoices (revenue + tax)')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning')
                ->extraAttributes([
                    'class' => 'cursor-pointer hover:shadow-lg transition-shadow',
                ]),

            Stat::make('Cash Flow', '$' . number_format($current['cash_flow'], 0))
                ->description($this->getChangeDescription($changes['cash_flow_change']))
                ->descriptionIcon($this->getChangeIcon($changes['cash_flow_change']))
                ->chart($trends['cash_flow'])
                ->color($this->getCashFlowColor($current['cash_flow']))
                ->extraAttributes([
                    'class' => 'cursor-pointer hover:shadow-lg transition-shadow',
                ]),
        ];
    }

    protected function getProfitMarginColor($margin): string
    {
        if ($margin >= 20) {
            return 'success';
        } elseif ($margin >= 10) {
            return 'warning';
        } else {
            return 'danger';
        }
    }

    protected function getCashFlowColor($cashFlow): string
    {
        if ($cashFlow < 0) {
            return 'danger';
        } elseif ($cashFlow < 10000) {
            return 'warning';
        } else {
            return 'success';
        }
    }

    protected function getCustomerGrowthDescription($percentage): string
    {
        if (abs($percentage) < 0.1) {
            return 'Same as last month';
        }

        return number_format(abs($percentage), 1) . '% ' . ($percentage > 0 ? 'growth' : 'decline');
    }

    protected function getChangeDescription(?float $percentage): string
    {
        if ($percentage === null || abs($percentage) < 0.1) {
            return 'No change';
        }

        return number_format(abs($percentage), 1) . '% ' . ($percentage > 0 ? 'increase' : 'decrease');
    }

    protected function getChangeIcon(?float $percentage): string
    {
        if ($percentage === null || abs($percentage) < 0.1) {
            return 'heroicon-m-minus';
        }

        return $percentage > 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down';
    }

    protected function getChangeColor(?float $percentage, bool $inverse = false): string
    {
        if ($percentage === null || abs($percentage) < 0.1) {
            return 'gray';
        }

        $isPositive = $percentage > 0;

        if ($inverse) {
            return $isPositive ? 'danger' : 'success';
        }

        return $isPositive ? 'success' : 'danger';
    }

    protected function getCustomerGrowthTrend($user): array
    {
        $trend = [];
        for ($i = 11; $i >= 0; $i--) {
            $monthStart = Carbon::now()->subMonths($i)->startOfMonth();
            $monthEnd = Carbon::now()->subMonths($i)->endOfMonth();

            $count = Contact::where('user_id', $user->id)
                ->whereBetween('created_at', [$monthStart, $monthEnd])
                ->count();

            $trend[] = $count;
        }

        return $trend;
    }
}
