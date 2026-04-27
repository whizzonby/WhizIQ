<?php

namespace App\Filament\Dashboard\Widgets;

use App\Models\ClientPayment;
use App\Services\FinancialMetricsCalculator;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Cache;

class CashVsProfitWidget extends Widget
{
    protected static ?int $sort = -1;

    protected static bool $isLazy = true;

    protected int|string|array $columnSpan = 'full';

    protected string $view = 'filament.dashboard.widgets.cash-vs-profit-widget';

    public function getFinancialSnapshot(): array
    {
        $user = auth()->user();
        $cacheKey = "cash_vs_profit_{$user->id}_" . now()->format('Y-m-d-H');

        return Cache::remember($cacheKey, 1800, function () use ($user) {
            $calculator = app(FinancialMetricsCalculator::class);
            $metrics    = $calculator->getMetricsWithComparisons($user);
            $current    = $metrics['current'];

            // Cash collected = payments actually received this month
            $cashCollected = ClientPayment::where('user_id', $user->id)
                ->whereBetween('payment_date', [now()->startOfMonth(), now()->endOfMonth()])
                ->sum('amount');

            $actualProfit  = $current['profit'];
            $totalRevenue  = $current['revenue'];
            $totalExpenses = $current['expenses'];
            $gap           = $cashCollected - $actualProfit;

            return [
                'cash_collected'  => (float) $cashCollected,
                'actual_profit'   => (float) $actualProfit,
                'total_revenue'   => (float) $totalRevenue,
                'total_expenses'  => (float) $totalExpenses,
                'gap'             => (float) $gap,
                'profit_margin'   => $current['profit_margin'],
                'month_name'      => now()->format('F Y'),
            ];
        });
    }
}
