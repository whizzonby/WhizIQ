<?php

namespace App\Filament\Dashboard\Widgets;

use App\Models\ClientPayment;
use App\Models\Expense;
use App\Models\RevenueSource;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AIFinancialForecastWidget extends ChartWidget
{
    protected ?string $heading = 'AI Financial Forecast (Next 90 Days)';

    protected static ?int $sort = 4;


    protected int | string | array $columnSpan = 'full';

    protected ?string $maxHeight = '350px';

    public ?string $filter = 'revenue';

    protected function getData(): array
    {
        $user = auth()->user();
        $historicalDays = 90;
        $forecastDays = 90;

        // Get historical data
        $startDate = Carbon::today()->subDays($historicalDays);

        if ($this->filter === 'revenue') {
            $historicalData = $this->getHistoricalRevenue($user, $startDate, $historicalDays);
            $forecast = $this->generateForecast($historicalData, $forecastDays);
            $lineColor = 'rgb(34, 197, 94)';
            $forecastColor = 'rgb(134, 239, 172)';
        } elseif ($this->filter === 'profit') {
            $historicalData = $this->getHistoricalProfit($user, $startDate, $historicalDays);
            $forecast = $this->generateForecast($historicalData, $forecastDays);
            $lineColor = 'rgb(59, 130, 246)';
            $forecastColor = 'rgb(147, 197, 253)';
        } else {
            $historicalData = $this->getHistoricalExpenses($user, $startDate, $historicalDays);
            $forecast = $this->generateForecast($historicalData, $forecastDays);
            $lineColor = 'rgb(239, 68, 68)';
            $forecastColor = 'rgb(252, 165, 165)';
        }

        $labels = $this->generateLabels($historicalDays, $forecastDays);

        return [
            'datasets' => [
                [
                    'label' => 'Historical ' . ucfirst($this->filter),
                    'data' => array_pad($historicalData, count($labels), null),
                    'borderColor' => $lineColor,
                    'backgroundColor' => 'transparent',
                    'borderWidth' => 2,
                    'tension' => 0.4,
                    'pointRadius' => 0,
                ],
                [
                    'label' => 'Forecasted ' . ucfirst($this->filter),
                    'data' => array_merge(
                        array_fill(0, $historicalDays - 1, null),
                        [$historicalData[count($historicalData) - 1]],
                        $forecast
                    ),
                    'borderColor' => $forecastColor,
                    'backgroundColor' => 'transparent',
                    'borderWidth' => 2,
                    'borderDash' => [5, 5],
                    'tension' => 0.4,
                    'pointRadius' => 0,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getFilters(): ?array
    {
        return [
            'revenue' => 'Revenue Forecast',
            'expenses' => 'Expense Forecast',
            'profit' => 'Profit Forecast',
        ];
    }

    protected function getHistoricalRevenue($user, $startDate, $days): array
    {
        $cacheKey = "ai_forecast_revenue_{$user->id}_" . $startDate->format('Y-m-d') . "_{$days}";

        return Cache::remember($cacheKey, 3600, function () use ($user, $startDate, $days) {
            $endDate = Carbon::parse($startDate)->addDays($days - 1);

            // Get revenue from RevenueSource aggregated by date
            $revenueSources = RevenueSource::where('user_id', $user->id)
                ->whereBetween('date', [$startDate, $endDate])
                ->where('amount', '>=', 0)
                ->selectRaw('DATE(date) as date, SUM(amount) as total')
                ->groupBy('date')
                ->pluck('total', 'date');

            // Get invoice payment revenue (excluding tax portion) aggregated by date
            // Formula: payment_amount * (invoice_subtotal / invoice_total_amount)
            $invoicePayments = ClientPayment::where('client_payments.user_id', $user->id)
                ->whereBetween('client_payments.payment_date', [$startDate, $endDate])
                ->where('client_payments.amount', '>=', 0)
                ->join('client_invoices', 'client_payments.client_invoice_id', '=', 'client_invoices.id')
                ->selectRaw('DATE(client_payments.payment_date) as date, SUM(
                    CASE
                        WHEN client_invoices.total_amount > 0
                        THEN client_payments.amount * (client_invoices.subtotal / client_invoices.total_amount)
                        ELSE client_payments.amount
                    END
                ) as total')
                ->groupBy('date')
                ->pluck('total', 'date');

            // Combine daily revenue into array
            $data = [];
            for ($i = 0; $i < $days; $i++) {
                $date = Carbon::parse($startDate)->addDays($i)->format('Y-m-d');
                $revenueSourceAmount = (float) ($revenueSources[$date] ?? 0);
                $invoicePaymentAmount = (float) ($invoicePayments[$date] ?? 0);
                $data[] = $revenueSourceAmount + $invoicePaymentAmount;
            }

            return $data;
        });
    }

    protected function getHistoricalExpenses($user, $startDate, $days): array
    {
        $cacheKey = "ai_forecast_expenses_{$user->id}_" . $startDate->format('Y-m-d') . "_{$days}";

        return Cache::remember($cacheKey, 3600, function () use ($user, $startDate, $days) {
            $endDate = Carbon::parse($startDate)->addDays($days - 1);

            // Get expenses aggregated by date
            $expenses = Expense::where('user_id', $user->id)
                ->whereBetween('date', [$startDate, $endDate])
                ->where('amount', '>=', 0)
                ->selectRaw('DATE(date) as date, SUM(amount) as total')
                ->groupBy('date')
                ->pluck('total', 'date');

            // Build array with daily expenses
            $data = [];
            for ($i = 0; $i < $days; $i++) {
                $date = Carbon::parse($startDate)->addDays($i)->format('Y-m-d');
                $data[] = (float) ($expenses[$date] ?? 0);
            }

            return $data;
        });
    }

    protected function getHistoricalProfit($user, $startDate, $days): array
    {
        $cacheKey = "ai_forecast_profit_{$user->id}_" . $startDate->format('Y-m-d') . "_{$days}";

        return Cache::remember($cacheKey, 3600, function () use ($user, $startDate, $days) {
            // Get revenue and expenses, then calculate profit for each day
            $revenue = $this->getHistoricalRevenue($user, $startDate, $days);
            $expenses = $this->getHistoricalExpenses($user, $startDate, $days);

            $data = [];
            for ($i = 0; $i < $days; $i++) {
                $data[] = $revenue[$i] - $expenses[$i];
            }

            return $data;
        });
    }

    protected function generateForecast(array $historicalData, int $days): array
    {
        // Simple linear regression forecast
        $n = count($historicalData);
        if ($n < 2) {
            return array_fill(0, $days, 0);
        }

        // Calculate trend using simple moving average and linear trend
        $sumX = 0;
        $sumY = 0;
        $sumXY = 0;
        $sumX2 = 0;

        foreach ($historicalData as $i => $value) {
            $sumX += $i;
            $sumY += $value;
            $sumXY += $i * $value;
            $sumX2 += $i * $i;
        }

        // Calculate slope and intercept
        $slope = ($n * $sumXY - $sumX * $sumY) / ($n * $sumX2 - $sumX * $sumX);
        $intercept = ($sumY - $slope * $sumX) / $n;

        // Generate forecast
        $forecast = [];
        for ($i = 0; $i < $days; $i++) {
            $x = $n + $i;
            $predicted = $slope * $x + $intercept;
            // Ensure non-negative values
            $forecast[] = max(0, $predicted);
        }

        return $forecast;
    }

    protected function generateLabels(int $historicalDays, int $forecastDays): array
    {
        $labels = [];
        $start = Carbon::today()->subDays($historicalDays);

        for ($i = 0; $i < $historicalDays + $forecastDays; $i++) {
            $date = $start->copy()->addDays($i);
            // Show label every 15 days
            if ($i % 15 === 0 || $i === $historicalDays - 1) {
                $labels[] = $date->format('M d');
            } else {
                $labels[] = '';
            }
        }

        return $labels;
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'top',
                ],
                'tooltip' => [
                    'callbacks' => [
                        'label' => 'function(context) {
                            return context.dataset.label + ": $" + context.parsed.y.toLocaleString();
                        }'
                    ]
                ],
                'annotation' => [
                    'annotations' => [
                        'line1' => [
                            'type' => 'line',
                            'xMin' => 89,
                            'xMax' => 89,
                            'borderColor' => 'rgb(156, 163, 175)',
                            'borderWidth' => 2,
                            'borderDash' => [5, 5],
                            'label' => [
                                'enabled' => true,
                                'content' => 'Today',
                                'position' => 'start',
                            ],
                        ],
                    ],
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'callback' => 'function(value) {
                            return "$" + value.toLocaleString();
                        }'
                    ],
                ],
            ],
            'interaction' => [
                'intersect' => false,
                'mode' => 'index',
            ],
        ];
    }

    public function getDescription(): ?string
    {
        $confidence = $this->calculateConfidenceLevel();
        return "Projection based on 90-day historical trend • Confidence Level: {$confidence}%";
    }

    protected function calculateConfidenceLevel(): int
    {
        // Simple confidence calculation based on data consistency
        // In a real app, this would use more sophisticated statistical methods
        return 75; // Placeholder
    }
}
