<?php

namespace App\Filament\Dashboard\Widgets;

use App\Models\ClientPayment;
use App\Models\RevenueSource;
use App\Services\FinancialMetricsCalculator;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class RevenueInsightsWidget extends BaseWidget
{
    protected static ?int $sort = 4;


    public function getHeading(): string
    {
        return '📈 Revenue Insights';
    }

    protected function getStats(): array
    {
        $user = auth()->user();
        $calculator = app(FinancialMetricsCalculator::class);
        $startOfMonth = Carbon::now()->startOfMonth();
        $startOfLastMonth = Carbon::now()->subMonth()->startOfMonth();
        $endOfLastMonth = Carbon::now()->subMonth()->endOfMonth();

        // Get current and last month metrics using FinancialMetricsCalculator (correct formula)
        $currentMetrics = $calculator->getCurrentMonthMetrics($user);
        $lastMetrics = $calculator->getLastMonthMetrics($user);

        $currentMonthRevenue = $currentMetrics['revenue'];
        $lastMonthRevenue = $lastMetrics['revenue'];

        // Calculate change
        $revenueChange = $calculator->calculatePercentageChange($currentMonthRevenue, $lastMonthRevenue);

        // Get top revenue source (including both RevenueSource and ClientPayment)
        $topSource = $this->getTopRevenueSource($user, $startOfMonth);

        // Revenue diversification (count of unique sources)
        $uniqueSources = RevenueSource::where('user_id', $user->id)
            ->where('date', '>=', $startOfMonth)
            ->distinct('source')
            ->count('source');

        // MRR (Monthly Recurring Revenue) from subscriptions
        $mrr = RevenueSource::where('user_id', $user->id)
            ->where('date', '>=', $startOfMonth)
            ->where('source', 'subscriptions')
            ->sum('amount');

        // Get revenue trend for last 7 days
        $revenueTrend = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $dailyRevenue = RevenueSource::where('user_id', $user->id)
                ->whereDate('date', $date)
                ->sum('amount');
            $revenueTrend[] = (float) $dailyRevenue;
        }

        return [
            Stat::make('Monthly Revenue', '$' . number_format($currentMonthRevenue, 0))
                ->description($this->getChangeDescription($revenueChange))
                ->descriptionIcon($this->getChangeIcon($revenueChange))
                ->chart($revenueTrend)
                ->color($this->getRevenueColor($revenueChange)),

            Stat::make('Top Source', $topSource ? ucwords(str_replace('_', ' ', $topSource->source)) : 'N/A')
                ->description($topSource ? '$' . number_format($topSource->total, 0) . ' (' . number_format(($topSource->total / max($currentMonthRevenue, 1)) * 100, 0) . '%)' : 'No revenue yet')
                ->descriptionIcon('heroicon-o-chart-bar')
                ->color('warning'),

            Stat::make($mrr > 0 ? 'MRR' : 'Revenue Streams', $mrr > 0 ? '$' . number_format($mrr, 0) : $uniqueSources)
                ->description($mrr > 0 ? 'Monthly Recurring Revenue' : ($uniqueSources > 1 ? 'Diversified sources' : 'Add more sources'))
                ->descriptionIcon($mrr > 0 ? 'heroicon-o-arrow-path' : 'heroicon-o-squares-2x2')
                ->color($mrr > 0 ? 'success' : ($uniqueSources > 2 ? 'success' : 'warning')),
        ];
    }

    protected function getChangeDescription(float $percentage): string
    {
        if ($percentage == 0) {
            return 'No change from last month';
        }

        return abs($percentage) . '% ' . ($percentage > 0 ? 'growth' : 'decline') . ' vs last month';
    }

    protected function getChangeIcon(float $percentage): string
    {
        if ($percentage == 0) {
            return 'heroicon-m-minus';
        }

        return $percentage > 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down';
    }

    protected function getRevenueColor(float $percentage): string
    {
        if ($percentage == 0) {
            return 'gray';
        }

        // For revenue, increase is good (green), decrease is bad (red)
        return $percentage > 0 ? 'success' : 'danger';
    }

    /**
     * Get top revenue source combining both RevenueSource and ClientPayment
     */
    protected function getTopRevenueSource($user, Carbon $startOfMonth): ?object
    {
        // Get revenue sources grouped by source
        $revenueSources = RevenueSource::where('user_id', $user->id)
            ->where('date', '>=', $startOfMonth)
            ->selectRaw('source as name, SUM(amount) as total')
            ->groupBy('source')
            ->get();

        // Get invoice payment revenue (excluding tax portion)
        $invoiceRevenue = ClientPayment::where('client_payments.user_id', $user->id)
            ->whereBetween('client_payments.payment_date', [$startOfMonth, Carbon::now()])
            ->join('client_invoices', 'client_payments.client_invoice_id', '=', 'client_invoices.id')
            ->selectRaw('SUM(
                CASE
                    WHEN client_invoices.total_amount > 0
                    THEN client_payments.amount * (client_invoices.subtotal / client_invoices.total_amount)
                    ELSE client_payments.amount
                END
            ) as total')
            ->value('total') ?? 0;

        // Combine all sources into a collection
        $allSources = collect();

        // Add revenue sources
        foreach ($revenueSources as $source) {
            $allSources->push((object)[
                'source' => $source->name,
                'total' => (float) $source->total,
            ]);
        }

        // Add invoice payments as a source
        if ($invoiceRevenue > 0) {
            $allSources->push((object)[
                'source' => 'client_invoices',
                'total' => (float) $invoiceRevenue,
            ]);
        }

        // Return the top source by total amount
        return $allSources->sortByDesc('total')->first();
    }
}
