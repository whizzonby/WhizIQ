<?php

namespace App\Filament\Dashboard\Widgets;

use App\Models\ClientInvoice;
use App\Models\Contact;
use App\Models\Expense;
use App\Models\InvoiceClient;
use App\Models\RevenueSource;
use Carbon\Carbon;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Cache;

class FinancialAlertBarWidget extends Widget
{
    protected static ?string $heading = '🚨 Financial Alerts';
    
    protected static ?int $sort = -1;


    protected string $view = 'filament.dashboard.widgets.financial-alert-bar-widget';

    protected int | string | array $columnSpan = 'full';

    public function getAlerts(): array
    {
        $user = auth()->user();
        $cacheKey = "financial_alerts_{$user->id}_" . now()->format('Y-m-d-H');

        return Cache::remember($cacheKey, 1800, function () use ($user) {
            $alerts = [];

            // Financial health checks
            $checks = [
                $this->checkExpenseGrowth($user),
                $this->checkCashFlowTrend($user),
                $this->checkLiquidity($user),
                $this->checkProfitability($user),
                $this->checkExpenseSpike($user),
                // New: client-facing alerts
                $this->checkUninvoicedClients($user),
                $this->checkExpenseVsAverage($user),
                $this->checkOverdueInvoices($user),
            ];

            foreach ($checks as $alert) {
                if ($alert) {
                    $alerts[] = $alert;
                }
            }

            if (empty($alerts)) {
                return [[
                    'type'    => 'success',
                    'icon'    => 'heroicon-o-check-circle',
                    'title'   => 'Financial Health Good',
                    'message' => 'No critical alerts detected. Your financial metrics are within healthy ranges.',
                    'action'  => null,
                ]];
            }

            // Sort: danger first, then warning, then info
            usort($alerts, function ($a, $b) {
                $order = ['danger' => 0, 'warning' => 1, 'info' => 2, 'success' => 3];
                return ($order[$a['type']] ?? 9) <=> ($order[$b['type']] ?? 9);
            });

            return $alerts;
        });
    }

    protected function checkExpenseGrowth($user): ?array
    {
        $currentMonth = Carbon::today()->startOfMonth();
        $twoMonthsAgo = $currentMonth->copy()->subMonths(2);

        $currentRevenue = RevenueSource::where('user_id', $user->id)
            ->where('date', '>=', $currentMonth)
            ->sum('amount');

        $currentExpenses = Expense::where('user_id', $user->id)
            ->where('date', '>=', $currentMonth)
            ->sum('amount');

        $pastRevenue = RevenueSource::where('user_id', $user->id)
            ->whereBetween('date', [$twoMonthsAgo, $currentMonth])
            ->sum('amount');

        $pastExpenses = Expense::where('user_id', $user->id)
            ->whereBetween('date', [$twoMonthsAgo, $currentMonth])
            ->sum('amount');

        if ($pastRevenue > 0 && $pastExpenses > 0) {
            $revenueGrowth = (($currentRevenue - $pastRevenue) / $pastRevenue) * 100;
            $expenseGrowth = (($currentExpenses - $pastExpenses) / $pastExpenses) * 100;

            if ($expenseGrowth > $revenueGrowth && $expenseGrowth > 10) {
                return [
                    'type' => 'warning',
                    'icon' => 'heroicon-o-exclamation-triangle',
                    'title' => 'Expense Growth Outpacing Revenue',
                    'message' => sprintf(
                        'Expenses have grown %.1f%% while revenue only grew %.1f%% over the last 2 months.',
                        $expenseGrowth,
                        $revenueGrowth
                    ),
                    'action' => 'Review expense categories',
                ];
            }
        }

        return null;
    }

    protected function checkCashFlowTrend($user): ?array
    {
        $months = [];
        for ($i = 0; $i < 3; $i++) {
            $monthStart = Carbon::today()->subMonths($i)->startOfMonth();
            $monthEnd = $monthStart->copy()->endOfMonth();

            $revenue = RevenueSource::where('user_id', $user->id)
                ->whereBetween('date', [$monthStart, $monthEnd])
                ->sum('amount');

            $expenses = Expense::where('user_id', $user->id)
                ->whereBetween('date', [$monthStart, $monthEnd])
                ->sum('amount');

            $months[] = $revenue - $expenses;
        }

        // Check if declining for 2 consecutive months
        if (count($months) >= 3 && $months[0] < $months[1] && $months[1] < $months[2]) {
            return [
                'type' => 'danger',
                'icon' => 'heroicon-o-arrow-trending-down',
                'title' => 'Cash Flow Declining',
                'message' => 'Net cash flow has been declining for the past 2 months. Take action to improve revenue or reduce costs.',
                'action' => 'View detailed analysis',
            ];
        }

        return null;
    }

    protected function checkLiquidity($user): ?array
    {
        $currentMonth = Carbon::today()->startOfMonth();

        $revenue = RevenueSource::where('user_id', $user->id)
            ->where('date', '>=', $currentMonth)
            ->sum('amount');

        $expenses = Expense::where('user_id', $user->id)
            ->where('date', '>=', $currentMonth)
            ->sum('amount');

        if ($expenses > 0) {
            $liquidityRatio = $revenue / $expenses;

            if ($liquidityRatio < 1) {
                return [
                    'type' => 'danger',
                    'icon' => 'heroicon-o-exclamation-circle',
                    'title' => 'Critical: Low Liquidity',
                    'message' => sprintf(
                        'Your liquidity ratio is %.2f:1. Expenses exceed revenue this month. Immediate attention required.',
                        $liquidityRatio
                    ),
                    'action' => 'Review budget',
                ];
            }
        }

        return null;
    }

    protected function checkProfitability($user): ?array
    {
        $currentMonth = Carbon::today()->startOfMonth();

        $revenue = RevenueSource::where('user_id', $user->id)
            ->where('date', '>=', $currentMonth)
            ->sum('amount');

        $expenses = Expense::where('user_id', $user->id)
            ->where('date', '>=', $currentMonth)
            ->sum('amount');

        if ($revenue > 0) {
            $netMargin = (($revenue - $expenses) / $revenue) * 100;

            if ($netMargin < 5) {
                return [
                    'type' => 'warning',
                    'icon' => 'heroicon-o-chart-bar',
                    'title' => 'Low Profitability Margin',
                    'message' => sprintf(
                        'Your net margin is only %.1f%%, which is below the recommended 15%% minimum.',
                        $netMargin
                    ),
                    'action' => 'View profitability ratios',
                ];
            }
        }

        return null;
    }

    protected function checkExpenseSpike($user): ?array
    {
        $today = Carbon::today();
        $lastWeek = $today->copy()->subWeek();

        $recentExpenses = Expense::where('user_id', $user->id)
            ->where('date', '>=', $lastWeek)
            ->sum('amount');

        $previousWeek = Expense::where('user_id', $user->id)
            ->whereBetween('date', [$lastWeek->copy()->subWeek(), $lastWeek])
            ->sum('amount');

        if ($previousWeek > 0) {
            $spike = (($recentExpenses - $previousWeek) / $previousWeek) * 100;

            if ($spike > 50) {
                return [
                    'type'    => 'info',
                    'icon'    => 'heroicon-o-arrow-trending-up',
                    'title'   => 'Unusual Expense Activity',
                    'message' => sprintf(
                        'Your expenses increased by %.1f%% this week compared to last week.',
                        $spike
                    ),
                    'action'  => 'Review recent expenses',
                ];
            }
        }

        return null;
    }

    /**
     * Alert: Active clients who haven't been invoiced in 30+ days.
     * Helps solo operators avoid forgetting to bill existing clients.
     */
    protected function checkUninvoicedClients($user): ?array
    {
        $cutoff = now()->subDays(30);

        // Find invoice clients linked to a contact (real clients) whose last invoice is older than 30 days
        $uninvoicedCount = InvoiceClient::where('invoice_clients.user_id', $user->id)
            ->whereNotNull('invoice_clients.contact_id')
            ->whereHas('invoices', function ($q) {
                // Only consider clients who have been invoiced before (not brand new)
                $q->whereNotIn('status', ['draft', 'cancelled']);
            })
            ->whereDoesntHave('invoices', function ($q) use ($cutoff) {
                $q->whereNotIn('status', ['draft', 'cancelled'])
                  ->where('created_at', '>=', $cutoff);
            })
            ->count();

        if ($uninvoicedCount >= 1) {
            return [
                'type'    => 'warning',
                'icon'    => 'heroicon-o-document-currency-dollar',
                'title'   => $uninvoicedCount === 1
                    ? '1 Client Not Invoiced in 30+ Days'
                    : "{$uninvoicedCount} Clients Not Invoiced in 30+ Days",
                'message' => $uninvoicedCount === 1
                    ? 'One of your clients has not received an invoice in over 30 days. Check if there is outstanding work to bill.'
                    : "{$uninvoicedCount} of your clients have not been invoiced in over 30 days. You may have unbilled work.",
                'action'  => 'View client invoices',
            ];
        }

        return null;
    }

    /**
     * Alert: This month's expenses are significantly above the 3-month average.
     */
    protected function checkExpenseVsAverage($user): ?array
    {
        $currentMonthStart = Carbon::today()->startOfMonth();

        $currentExpenses = Expense::where('user_id', $user->id)
            ->where('date', '>=', $currentMonthStart)
            ->sum('amount');

        // 3-month average (months prior to this one)
        $threeMonthTotal = 0;
        for ($i = 1; $i <= 3; $i++) {
            $start = Carbon::today()->subMonths($i)->startOfMonth();
            $end   = $start->copy()->endOfMonth();
            $threeMonthTotal += Expense::where('user_id', $user->id)
                ->whereBetween('date', [$start, $end])
                ->sum('amount');
        }

        $threeMonthAvg = $threeMonthTotal / 3;

        if ($threeMonthAvg > 0 && $currentExpenses > 0) {
            $percentAbove = (($currentExpenses - $threeMonthAvg) / $threeMonthAvg) * 100;

            if ($percentAbove >= 20) {
                return [
                    'type'    => 'warning',
                    'icon'    => 'heroicon-o-banknotes',
                    'title'   => 'Expenses Above 3-Month Average',
                    'message' => sprintf(
                        'This month\'s expenses ($%s) are %.0f%% above your 3-month average ($%s). Review your spending.',
                        number_format($currentExpenses, 0),
                        $percentAbove,
                        number_format($threeMonthAvg, 0)
                    ),
                    'action'  => 'Review expenses',
                ];
            }
        }

        return null;
    }

    /**
     * Alert: Outstanding overdue invoices above a meaningful threshold.
     */
    protected function checkOverdueInvoices($user): ?array
    {
        $overdue = ClientInvoice::where('user_id', $user->id)
            ->where('status', 'overdue')
            ->selectRaw('COUNT(*) as count, COALESCE(SUM(total_amount - amount_paid), 0) as total_owed')
            ->first();

        if (!$overdue || $overdue->count === 0) {
            return null;
        }

        return [
            'type'    => $overdue->count >= 3 ? 'danger' : 'warning',
            'icon'    => 'heroicon-o-clock',
            'title'   => $overdue->count === 1
                ? '1 Overdue Invoice'
                : "{$overdue->count} Overdue Invoices",
            'message' => sprintf(
                '$%s is overdue across %d %s. Follow up to protect your cash flow.',
                number_format($overdue->total_owed, 0),
                $overdue->count,
                $overdue->count === 1 ? 'invoice' : 'invoices'
            ),
            'action'  => 'View overdue invoices',
        ];
    }
}
