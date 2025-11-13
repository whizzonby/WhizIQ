<?php

namespace App\Filament\Dashboard\Widgets;

use App\Models\Expense;
use App\Services\FinancialMetricsCalculator;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ExpenseInsightsWidget extends BaseWidget
{
    protected static ?int $sort = 5;


    public function getHeading(): string
    {
        return '📉 Expense Insights';
    }

    protected function getStats(): array
    {
        $user = auth()->user();
        $calculator = app(FinancialMetricsCalculator::class);
        $startOfMonth = Carbon::now()->startOfMonth();

        // Get current and last month metrics using FinancialMetricsCalculator (correct formula)
        $currentMetrics = $calculator->getCurrentMonthMetrics($user);
        $lastMetrics = $calculator->getLastMonthMetrics($user);

        $currentMonthExpenses = $currentMetrics['expenses'];
        $lastMonthExpenses = $lastMetrics['expenses'];

        // Calculate change
        $expenseChange = $calculator->calculatePercentageChange($currentMonthExpenses, $lastMonthExpenses);

        // Top category this month
        $topCategory = Expense::where('user_id', $user->id)
            ->where('date', '>=', $startOfMonth)
            ->selectRaw('category, SUM(amount) as total')
            ->groupBy('category')
            ->orderByDesc('total')
            ->first();

        // Tax deductible expenses
        $taxDeductibleTotal = Expense::where('user_id', $user->id)
            ->where('date', '>=', $startOfMonth)
            ->where('is_tax_deductible', true)
            ->sum('amount');

        // Get expense trend for last 7 days
        $expenseTrend = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $dailyExpense = Expense::where('user_id', $user->id)
                ->whereDate('date', $date)
                ->sum('amount');
            $expenseTrend[] = (float) $dailyExpense;
        }

        return [
            Stat::make('Monthly Expenses', '$' . number_format($currentMonthExpenses, 0))
                ->description($this->getChangeDescription($expenseChange))
                ->descriptionIcon($this->getChangeIcon($expenseChange))
                ->chart($expenseTrend)
                ->color($this->getExpenseColor($expenseChange)),

            Stat::make('Top Expense Category', $topCategory ? ucwords(str_replace('_', ' ', $topCategory->category)) : 'N/A')
                ->description($topCategory ? '$' . number_format($topCategory->total, 0) : 'No expenses yet')
                ->descriptionIcon('heroicon-o-chart-pie')
                ->color('warning'),

            Stat::make('Tax Deductible', '$' . number_format($taxDeductibleTotal, 0))
                ->description('This month\'s deductions')
                ->descriptionIcon('heroicon-o-document-text')
                ->color('success'),
        ];
    }

    protected function getChangeDescription(float $percentage): string
    {
        if ($percentage == 0) {
            return 'No change from last month';
        }

        return abs($percentage) . '% ' . ($percentage > 0 ? 'higher' : 'lower') . ' than last month';
    }

    protected function getChangeIcon(float $percentage): string
    {
        if ($percentage == 0) {
            return 'heroicon-m-minus';
        }

        return $percentage > 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down';
    }

    protected function getExpenseColor(float $percentage): string
    {
        if ($percentage == 0) {
            return 'gray';
        }

        // For expenses, increase is bad (red), decrease is good (green)
        return $percentage > 0 ? 'danger' : 'success';
    }
}
