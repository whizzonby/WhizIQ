<?php

namespace App\Filament\Dashboard\Widgets;

use App\Services\FinancialMetricsCalculator;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class MonthlyExpensesAndProfitWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected function getStats(): array
    {
        $user = auth()->user();
        $calculator = app(FinancialMetricsCalculator::class);

        // Get current and previous month metrics
        $metricsData = $calculator->getMetricsWithComparisons($user);
        $current = $metricsData['current'];
        $changes = $metricsData['changes'];
        $trends = $metricsData['trends'];

        // Expenses stat
        $expensesChangePercentage = $changes['expenses_change'];
        $expensesChangeDescription = $this->getChangeDescription($expensesChangePercentage);
        $expensesChangeIcon = $this->getChangeIcon($expensesChangePercentage);
        // Inverse color: increase in expenses is bad (danger), decrease is good (success)
        $expensesColor = $this->getChangeColor($expensesChangePercentage, true);

        // Profit stat
        $profitChangePercentage = $changes['profit_change'];
        $profitChangeDescription = $this->getChangeDescription($profitChangePercentage);
        $profitChangeIcon = $this->getChangeIcon($profitChangePercentage);
        $profitColor = $this->getProfitColor($current['profit'], $profitChangePercentage);

        return [
            Stat::make('Monthly Profit', '$' . number_format($current['profit'], 0))
                ->description($profitChangeDescription . ' • ' . number_format($current['profit_margin'], 1) . '% margin')
                ->descriptionIcon($profitChangeIcon)
                ->chart($trends['profit'])
                ->color($profitColor),
        ];
    }

    protected function getChangeDescription(?float $percentage): string
    {
        if ($percentage === null || abs($percentage) < 0.1) {
            return 'No change from last month';
        }

        return number_format(abs($percentage), 1) . '% ' . ($percentage > 0 ? 'increase' : 'decrease') . ' from last month';
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

    protected function getProfitColor(float $profit, ?float $changePercentage): string
    {
        // Negative profit is always danger
        if ($profit < 0) {
            return 'danger';
        }

        // Positive profit with improvement or no change
        if ($changePercentage === null || $changePercentage >= 0) {
            return 'success';
        }

        // Positive profit but declining
        return 'warning';
    }
}
