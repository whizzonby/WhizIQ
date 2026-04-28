<?php

namespace App\Filament\Dashboard\Widgets;

use App\Models\ClientInvoice;
use App\Models\ClientPayment;
use App\Services\FinancialMetricsCalculator;
use Carbon\Carbon;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Cache;

class FinancialSnapshotWidget extends Widget
{
    protected static ?int $sort = 1;

    protected static bool $isLazy = true;

    protected int|string|array $columnSpan = 'full';

    protected string $view = 'filament.dashboard.widgets.financial-snapshot-widget';

    public function getSnapshotData(): array
    {
        $user     = auth()->user();
        $cacheKey = "financial_snapshot_{$user->id}_" . now()->format('Y-m-d-H');

        return Cache::remember($cacheKey, 1800, function () use ($user) {
            $calculator = app(FinancialMetricsCalculator::class);
            $metrics    = $calculator->getMetricsWithComparisons($user);
            $current    = $metrics['current'];
            $changes    = $metrics['changes'];

            $cashCollected = (float) ClientPayment::where('user_id', $user->id)
                ->whereBetween('payment_date', [now()->startOfMonth(), now()->endOfMonth()])
                ->sum('amount');

            $outstanding = (float) ClientInvoice::where('user_id', $user->id)
                ->whereIn('status', ['sent', 'overdue'])
                ->selectRaw('COALESCE(SUM(total_amount - amount_paid), 0) as total')
                ->value('total');

            $outstandingCount = ClientInvoice::where('user_id', $user->id)
                ->whereIn('status', ['sent', 'overdue'])
                ->count();

            $actualProfit  = (float) $current['profit'];
            $totalExpenses = (float) $current['expenses'];
            $gap           = $cashCollected - $actualProfit;

            $prevMonth     = $calculator->getLastMonthMetrics($user);

            $cashVsPrevPct = $prevMonth['revenue'] > 0
                ? round((($current['revenue'] - $prevMonth['revenue']) / $prevMonth['revenue']) * 100, 1)
                : null;

            $expVsPrevPct = $prevMonth['expenses'] > 0
                ? round((($current['expenses'] - $prevMonth['expenses']) / $prevMonth['expenses']) * 100, 1)
                : null;

            return [
                'month_name'        => now()->format('F Y'),
                'cash_collected'    => $cashCollected,
                'cash_change_pct'   => $cashVsPrevPct,
                'actual_profit'     => $actualProfit,
                'profit_margin'     => $current['profit_margin'],
                'total_expenses'    => $totalExpenses,
                'expenses_pct'      => $expVsPrevPct,
                'outstanding'       => $outstanding,
                'outstanding_count' => $outstandingCount,
                'gap'               => $gap,
                'gap_label'         => $gap >= 0 ? 'More cash than profit' : 'Less cash than profit',
            ];
        });
    }

    private function formatPct(?float $pct, bool $inverse = false): string
    {
        if ($pct === null) return '';
        $sign = $pct >= 0 ? '+' : '';
        return $sign . number_format($pct, 1) . '%';
    }
}
