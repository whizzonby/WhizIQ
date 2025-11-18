<?php

namespace App\Filament\Dashboard\Widgets;

use App\Models\Expense;
use App\Models\RevenueSource;
use App\Models\Contact;
use App\Models\Goal;
use App\Models\MarketingMetric;
use App\Services\OpenAIService;
use App\Services\AnomalyDetectionService;
use App\Services\FinancialMetricsCalculator;
use Carbon\Carbon;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Cache;

class AIBusinessInsightsWidget extends Widget
{
    protected static ?string $heading = '🤖 AI Business Insights';

    protected static ?int $sort = 14;

    protected string $view = 'filament.dashboard.widgets.ai-business-insights-widget';

    protected int | string | array $columnSpan = 'full';

    public ?array $insights = null;

    public ?array $anomalies = null;

    public ?array $forecasts = null;

    public bool $isLoading = false;

    public function mount()
    {
        $this->loadInsights();
    }

    public function loadInsights()
    {
        $this->isLoading = true;

        try {
            $user = auth()->user();
            $cacheKey = "ai_insights_{$user->id}_" . now()->format('Y-m-d-H');

            // Cache for 2 hours to reduce API costs
            $cachedData = Cache::remember($cacheKey, 7200, function () use ($user) {
                return $this->generateAllInsights($user);
            });

            $this->insights = $cachedData['insights'];
            $this->anomalies = $cachedData['anomalies'];
            $this->forecasts = $cachedData['forecasts'];
        } catch (\Exception $e) {
            $this->insights = [[
                'type' => 'warning',
                'title' => 'AI Insights Unavailable',
                'description' => 'Unable to generate insights. Please check your OpenAI API configuration.',
                'icon' => 'heroicon-o-exclamation-triangle',
            ]];
            $this->anomalies = [];
            $this->forecasts = [];
        } finally {
            $this->isLoading = false;
        }
    }

    protected function generateAllInsights($user): array
    {
        // Gather comprehensive business data
        $businessData = $this->gatherBusinessData($user);

        // Detect anomalies
        $anomalies = $this->detectAnomalies($user);

        // Generate AI insights
        $insights = $this->generateAIInsights($user, $businessData, $anomalies);

        // Generate forecasts
        $forecasts = $this->generateForecasts($user, $businessData);

        return [
            'insights' => $insights,
            'anomalies' => $anomalies,
            'forecasts' => $forecasts,
        ];
    }

    protected function gatherBusinessData($user): array
    {
        $calculator = app(FinancialMetricsCalculator::class);
        $startOfMonth = Carbon::now()->startOfMonth();
        $lastMonth = Carbon::now()->subMonth()->startOfMonth();
        $lastMonthEnd = Carbon::now()->subMonth()->endOfMonth();

        // Get current and previous month metrics
        $currentMetrics = $calculator->getCurrentMonthMetrics($user);
        $previousMetrics = $calculator->getLastMonthMetrics($user);

        // Get revenue trend (all months)
        $revenueTrend = $calculator->getMonthlyTrend($user, 'revenue');

        // Calculate historical average (EXCLUDING current month for accurate comparison)
        $historicalRevenue = array_slice($revenueTrend, 0, -1); // Remove current month
        $avgHistoricalRevenue = count($historicalRevenue) > 0 ? array_sum($historicalRevenue) / count($historicalRevenue) : 0;

        // Calculate expense trend
        $expenseTrend = $calculator->getMonthlyTrend($user, 'expenses');
        $historicalExpenses = array_slice($expenseTrend, 0, -1); // Remove current month
        $avgHistoricalExpenses = count($historicalExpenses) > 0 ? array_sum($historicalExpenses) / count($historicalExpenses) : 0;

        // Calculate changes vs HISTORICAL AVERAGE (not just previous month)
        $revenueChange = $avgHistoricalRevenue > 0
            ? (($currentMetrics['revenue'] - $avgHistoricalRevenue) / $avgHistoricalRevenue) * 100
            : ($currentMetrics['revenue'] > 0 ? 100 : 0);

        $expenseChange = $avgHistoricalExpenses > 0
            ? (($currentMetrics['expenses'] - $avgHistoricalExpenses) / $avgHistoricalExpenses) * 100
            : ($currentMetrics['expenses'] > 0 ? 100 : 0);

        // Get marketing metrics
        $currentMarketingMetrics = MarketingMetric::where('user_id', $user->id)
            ->where('date', '>=', $startOfMonth)
            ->selectRaw('
                SUM(ad_spend) as total_ad_spend,
                AVG(roi) as avg_roi,
                SUM(conversions) as total_conversions,
                SUM(leads) as total_leads,
                AVG(customer_acquisition_cost) as avg_cac,
                AVG(clv_cac_ratio) as avg_clv_cac_ratio,
                AVG(cost_per_conversion) as avg_cost_per_conversion
            ')
            ->first();

        $previousMarketingMetrics = MarketingMetric::where('user_id', $user->id)
            ->whereBetween('date', [$lastMonth, $lastMonthEnd])
            ->selectRaw('
                SUM(ad_spend) as total_ad_spend,
                AVG(roi) as avg_roi,
                SUM(conversions) as total_conversions,
                SUM(leads) as total_leads
            ')
            ->first();

        // Calculate marketing changes
        $adSpendChange = $this->calculatePercentageChange(
            $currentMarketingMetrics->total_ad_spend ?? 0,
            $previousMarketingMetrics->total_ad_spend ?? 0
        );

        $marketingConversionChange = $this->calculatePercentageChange(
            $currentMarketingMetrics->total_conversions ?? 0,
            $previousMarketingMetrics->total_conversions ?? 0
        );

        return [
            'current_revenue' => $currentMetrics['revenue'],
            'previous_revenue' => $previousMetrics['revenue'],
            'revenue_change' => $revenueChange,
            'avg_historical_revenue' => $avgHistoricalRevenue,
            'current_profit' => $currentMetrics['profit'],
            'current_expenses' => $currentMetrics['expenses'],
            'expense_change' => $expenseChange,
            'avg_historical_expenses' => $avgHistoricalExpenses,
            'cash_flow' => $currentMetrics['cash_flow'],
            'profit_margin' => $currentMetrics['profit_margin'],
            'metrics_count' => count(array_filter($revenueTrend)),
            'avg_revenue' => $avgHistoricalRevenue, // Use historical average
            'avg_expenses' => $avgHistoricalExpenses, // Use historical average
            'revenue_trend' => $revenueTrend,
            'new_customers_this_month' => Contact::where('user_id', $user->id)
                ->where('created_at', '>=', $startOfMonth)
                ->count(),
            'top_expense_category' => $this->getTopExpenseCategory($user),
            'active_goals' => Goal::where('user_id', $user->id)
                ->where('status', 'in_progress')
                ->count(),
            // Marketing metrics
            'total_ad_spend' => $currentMarketingMetrics->total_ad_spend ?? 0,
            'ad_spend_change' => $adSpendChange,
            'avg_marketing_roi' => $currentMarketingMetrics->avg_roi ?? 0,
            'total_marketing_conversions' => $currentMarketingMetrics->total_conversions ?? 0,
            'marketing_conversion_change' => $marketingConversionChange,
            'total_marketing_leads' => $currentMarketingMetrics->total_leads ?? 0,
            'avg_cac' => $currentMarketingMetrics->avg_cac ?? 0,
            'avg_clv_cac_ratio' => $currentMarketingMetrics->avg_clv_cac_ratio ?? 0,
            'avg_cost_per_conversion' => $currentMarketingMetrics->avg_cost_per_conversion ?? 0,
        ];
    }

    protected function calculatePercentageChange($current, $previous): float
    {
        if ($previous == 0) {
            return $current > 0 ? 100 : 0;
        }

        return (($current - $previous) / $previous) * 100;
    }

    protected function getTopExpenseCategory($user): ?string
    {
        $topExpense = Expense::where('user_id', $user->id)
            ->where('date', '>=', Carbon::now()->startOfMonth())
            ->selectRaw('category, SUM(amount) as total')
            ->groupBy('category')
            ->orderByDesc('total')
            ->first();

        return $topExpense->category ?? null;
    }

    protected function detectAnomalies($user): array
    {
        try {
            $anomalyService = app(AnomalyDetectionService::class);
            $detectedAnomalies = $anomalyService->detectMetricAnomalies($user->id);

            return collect($detectedAnomalies)->map(function ($anomaly) {
                return [
                    'metric' => $anomaly['metric'] ?? 'Unknown',
                    'severity' => $anomaly['severity'] ?? 'low',
                    'message' => $anomaly['message'] ?? $anomaly['description'] ?? 'Anomaly detected',
                    'value' => $anomaly['value'] ?? null,
                    'expected' => $anomaly['expected'] ?? null,
                    'deviation' => $anomaly['deviation'] ?? null,
                ];
            })->toArray();
        } catch (\Exception $e) {
            return [];
        }
    }

    protected function generateAIInsights($user, array $data, array $anomalies): array
    {
        try {
            $openAI = app(OpenAIService::class);

            $prompt = $this->buildInsightsPrompt($data, $anomalies);
            $response = $openAI->chat([
                [
                    'role' => 'system',
                    'content' => 'You are a business intelligence advisor. Analyze business metrics and provide actionable insights and recommendations.',
                ],
                [
                    'role' => 'user',
                    'content' => $prompt,
                ],
            ], [
                'feature' => 'business_insights',
                'action' => 'generate',
            ]);

            if ($response) {
                return $this->parseAIResponse($response);
            }

            return $this->getFallbackInsights($data, $anomalies);
        } catch (\Exception $e) {
            return $this->getFallbackInsights($data, $anomalies);
        }
    }

    protected function buildInsightsPrompt(array $data, array $anomalies): string
    {
        $anomalyText = '';
        if (!empty($anomalies)) {
            $anomalyText = "\n\nAnomalies detected:\n";
            foreach ($anomalies as $anomaly) {
                $anomalyText .= "- {$anomaly['message']} (Severity: {$anomaly['severity']})\n";
            }
        }

        $marketingText = '';
        if ($data['total_ad_spend'] > 0) {
            $marketingText = "\n\nMarketing Performance:
- Total Ad Spend: \${$data['total_ad_spend']} ({$data['ad_spend_change']}% change)
- Average Marketing ROI: {$data['avg_marketing_roi']}%
- Marketing Conversions: {$data['total_marketing_conversions']} ({$data['marketing_conversion_change']}% change)
- Marketing Leads: {$data['total_marketing_leads']}
- Avg Customer Acquisition Cost (CAC): \${$data['avg_cac']}
- Avg CLV:CAC Ratio: {$data['avg_clv_cac_ratio']}:1
- Avg Cost Per Conversion: \${$data['avg_cost_per_conversion']}";
        }

        return <<<PROMPT
You are a business intelligence advisor. Analyze the following business metrics and provide 3-5 actionable insights and recommendations.

Current Business Performance:
- Current Month Revenue: \${$data['current_revenue']}
- Historical Average Revenue: \${$data['avg_historical_revenue']}
- Change vs Historical Average: {$data['revenue_change']}%
- Profit: \${$data['current_profit']} (Margin: {$data['profit_margin']}%)
- Current Month Expenses: \${$data['current_expenses']}
- Historical Average Expenses: \${$data['avg_historical_expenses']}
- Expense Change vs Historical Average: {$data['expense_change']}%
- Cash Flow: \${$data['cash_flow']}
- New Customers This Month: {$data['new_customers_this_month']}
- Top Expense Category: {$data['top_expense_category']}
{$marketingText}
{$anomalyText}

IMPORTANT: The percentage changes shown are vs HISTORICAL AVERAGE (not previous month), providing a true baseline comparison.

Provide insights in this exact format (no markdown, just plain text with numbers):

1. [Insight Type]: [Insight Title]
   [2-3 sentence description with specific recommendations]

2. [Insight Type]: [Insight Title]
   [2-3 sentence description with specific recommendations]

Focus on:
- Revenue optimization opportunities
- Cost reduction strategies
- Cash flow management (ONLY if negative or presents a risk)
- Marketing ROI and customer acquisition efficiency
- Correlation between marketing spend and revenue growth
- CLV:CAC health and unit economics
- Growth opportunities
- Risk mitigation

CRITICAL CONSTRAINTS:
- Do NOT call small changes (<10%) "volatile" or "significant" - these are normal fluctuations
- Do NOT highlight positive cash flow as an insight - this is expected normal operation
- Only flag volatility if changes exceed 40% vs historical average
- Only mention cash flow if negative or presents immediate concern
- Focus on actionable issues and opportunities, not routine business operations

Keep each insight actionable and specific to the data provided. When marketing data is available, highlight relationships between marketing performance and business outcomes.
PROMPT;
    }

    protected function parseAIResponse(string $response): array
    {
        $insights = [];
        $lines = explode("\n", trim($response));
        $currentInsight = null;

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            // Check if it's a numbered insight
            if (preg_match('/^(\d+)\.\s*\[?([^\]]+)\]?:\s*(.+)$/', $line, $matches)) {
                if ($currentInsight) {
                    $insights[] = $currentInsight;
                }

                $type = strtolower($matches[2]);
                $title = $matches[3];

                $currentInsight = [
                    'type' => $this->mapInsightType($type),
                    'title' => $title,
                    'description' => '',
                    'icon' => $this->getIconForInsightType($this->mapInsightType($type)),
                ];
            } else {
                // Continuation of description
                if ($currentInsight) {
                    $currentInsight['description'] .= ' ' . $line;
                }
            }
        }

        if ($currentInsight) {
            $insights[] = $currentInsight;
        }

        return !empty($insights) ? $insights : $this->parseFallbackFormat($response);
    }

    protected function parseFallbackFormat(string $response): array
    {
        // If structured parsing fails, return as single insight
        return [[
            'type' => 'info',
            'title' => 'AI Business Analysis',
            'description' => $response,
            'icon' => 'heroicon-o-light-bulb',
        ]];
    }

    protected function mapInsightType(string $type): string
    {
        $type = strtolower($type);

        if (str_contains($type, 'warning') || str_contains($type, 'risk') || str_contains($type, 'concern')) {
            return 'warning';
        }

        if (str_contains($type, 'opportunity') || str_contains($type, 'growth') || str_contains($type, 'recommendation')) {
            return 'success';
        }

        if (str_contains($type, 'alert') || str_contains($type, 'critical')) {
            return 'danger';
        }

        return 'info';
    }

    protected function getIconForInsightType(string $type): string
    {
        return match($type) {
            'warning' => 'heroicon-o-exclamation-triangle',
            'danger' => 'heroicon-o-exclamation-circle',
            'success' => 'heroicon-o-light-bulb',
            'info' => 'heroicon-o-information-circle',
            default => 'heroicon-o-chart-bar',
        };
    }

    protected function getFallbackInsights(array $data, array $anomalies): array
    {
        $insights = [];

        // Revenue insight
        if ($data['revenue_change'] < -10) {
            $insights[] = [
                'type' => 'danger',
                'title' => 'Revenue Decline Alert',
                'description' => "Revenue dropped {$data['revenue_change']}% this period. Review your sales pipeline and customer retention strategies. Consider launching targeted marketing campaigns to boost acquisition.",
                'icon' => 'heroicon-o-exclamation-circle',
            ];
        } elseif ($data['revenue_change'] > 15) {
            $insights[] = [
                'type' => 'success',
                'title' => 'Strong Revenue Growth',
                'description' => "Excellent! Revenue increased {$data['revenue_change']}% this period. Capitalize on this momentum by investing in customer success and expansion opportunities.",
                'icon' => 'heroicon-o-arrow-trending-up',
            ];
        }

        // Expense-specific insights
        $expenseInsights = $this->getExpenseInsights($data);
        $insights = array_merge($insights, $expenseInsights);

        // Revenue-specific insights
        $revenueInsights = $this->getRevenueInsights($data);
        $insights = array_merge($insights, $revenueInsights);

        // Marketing-specific insights
        $marketingInsights = $this->getMarketingInsights($data);
        $insights = array_merge($insights, $marketingInsights);

        // Profit margin insight - contextual based on whether it's negative or just low
        if ($data['profit_margin'] < 0) {
            // Negative margin - expenses exceeded revenue
            $insights[] = [
                'type' => 'warning',
                'title' => 'Negative Profit Margin',
                'description' => "Profit margin is currently " . number_format($data['profit_margin'], 1) . "% because expenses exceeded revenue this month. Review whether this is due to one-time costs or ongoing cost structure. Top expense category: {$data['top_expense_category']}.",
                'icon' => 'heroicon-o-exclamation-triangle',
            ];
        } elseif ($data['profit_margin'] < 10 && $data['profit_margin'] >= 0) {
            // Positive but low margin
            $insights[] = [
                'type' => 'info',
                'title' => 'Low Profit Margin',
                'description' => "Your profit margin is {$data['profit_margin']}%. While positive, there's room for improvement. Consider reviewing pricing strategy and cost optimization opportunities, especially in {$data['top_expense_category']}.",
                'icon' => 'heroicon-o-information-circle',
            ];
        }

        // Cash flow insight removed - redundant with profit margin insights

        // Customer growth insight
        if ($data['new_customers_this_month'] == 0) {
            $insights[] = [
                'type' => 'warning',
                'title' => 'No New Customer Acquisition',
                'description' => "No new customers acquired this month. Increase marketing efforts and review your customer acquisition channels. Consider referral programs or partnerships.",
                'icon' => 'heroicon-o-user-group',
            ];
        }

        // Default insight if none triggered
        if (empty($insights)) {
            $insights[] = [
                'type' => 'info',
                'title' => 'Business Performance Summary',
                'description' => "Your business metrics are stable. Current profit margin is {$data['profit_margin']}% with \$" . number_format($data['current_revenue']) . " in revenue. Continue monitoring trends for optimization opportunities.",
                'icon' => 'heroicon-o-chart-bar',
            ];
        }

        return $insights;
    }

    protected function generateForecasts($user, array $data): array
    {
        if ($data['metrics_count'] < 3) {
            return [];
        }

        try {
            // Simple linear regression for next period forecast
            $revenueTrend = $data['revenue_trend'];
            $trendCount = count($revenueTrend);

            if ($trendCount < 3) {
                return [];
            }

            // Calculate simple moving average
            $recentAvg = array_sum(array_slice($revenueTrend, -3)) / 3;
            $olderAvg = array_sum(array_slice($revenueTrend, 0, 3)) / 3;
            $trend = $recentAvg - $olderAvg;

            $nextMonthRevenue = $data['current_revenue'] + $trend;
            
            // Avoid division by zero when current_revenue is 0
            if ($data['current_revenue'] <= 0) {
                // If no current revenue, use trend-based confidence
                $confidence = abs($trend) > 0 ? 70 : 50;
            } else {
                $confidence = min(95, max(60, 100 - (abs($trend) / $data['current_revenue']) * 100));
            }

            return [
                [
                    'metric' => 'Revenue',
                    'current_value' => $data['current_revenue'],
                    'forecast_value' => max(0, $nextMonthRevenue),
                    'confidence' => round($confidence),
                    'trend' => $trend > 0 ? 'increasing' : 'decreasing',
                    'period' => 'Next Month',
                ],
            ];
        } catch (\Exception $e) {
            return [];
        }
    }

    public function refreshInsights()
    {
        // Clear cache and reload
        $user = auth()->user();
        $cacheKey = "ai_insights_{$user->id}_" . now()->format('Y-m-d-H');
        Cache::forget($cacheKey);

        $this->loadInsights();
    }

    public function getSeverityColor(string $severity): string
    {
        return match($severity) {
            'high' => 'danger',
            'medium' => 'warning',
            'low' => 'info',
            default => 'gray',
        };
    }

    protected function getExpenseInsights(array $data): array
    {
        $insights = [];
        $user = auth()->user();
        $startOfMonth = \Carbon\Carbon::now()->startOfMonth();
        $startOfLastMonth = \Carbon\Carbon::now()->subMonth()->startOfMonth();
        $endOfLastMonth = \Carbon\Carbon::now()->subMonth()->endOfMonth();

        // Current and last month expenses
        $currentExpenses = \App\Models\Expense::where('user_id', $user->id)
            ->where('date', '>=', $startOfMonth)
            ->sum('amount');

        $lastExpenses = \App\Models\Expense::where('user_id', $user->id)
            ->whereBetween('date', [$startOfLastMonth, $endOfLastMonth])
            ->sum('amount');

        $expenseChange = $lastExpenses > 0 ? (($currentExpenses - $lastExpenses) / $lastExpenses) * 100 : 0;

        // Check for unusual expense increase
        if ($expenseChange > 30) {
            $insights[] = [
                'type' => 'warning',
                'title' => 'Significant Expense Increase',
                'description' => "Expenses increased by " . number_format($expenseChange, 1) . "% this month. Review recent purchases and identify if this is a one-time spike or a trend. Top category: {$data['top_expense_category']}.",
                'icon' => 'heroicon-o-arrow-trending-up',
            ];
        }

        // Check for expense reduction opportunity
        if ($expenseChange < -15) {
            $insights[] = [
                'type' => 'success',
                'title' => 'Great Cost Control',
                'description' => "Expenses decreased by " . number_format(abs($expenseChange), 1) . "% this month. Excellent cost management! Maintain this discipline to improve profit margins.",
                'icon' => 'heroicon-o-arrow-trending-down',
            ];
        }

        // Tax deductible insights - use calculateDeductibleAmount() for accuracy
        $taxDeductibleExpenses = \App\Models\Expense::where('user_id', $user->id)
            ->where('date', '>=', $startOfMonth)
            ->where('is_tax_deductible', true)
            ->get();

        $taxDeductible = $taxDeductibleExpenses->sum(function ($expense) {
            return $expense->calculateDeductibleAmount();
        });

        $deductiblePercentage = $currentExpenses > 0 ? ($taxDeductible / $currentExpenses) * 100 : 0;

        if ($deductiblePercentage < 30 && $currentExpenses > 1000) {
            $insights[] = [
                'type' => 'info',
                'title' => 'Tax Deduction Opportunity',
                'description' => "Only " . number_format($deductiblePercentage, 0) . "% of expenses are marked as tax deductible. Review your expenses to ensure all eligible deductions are captured for tax savings.",
                'icon' => 'heroicon-o-document-text',
            ];
        }

        return $insights;
    }

    protected function getRevenueInsights(array $data): array
    {
        $insights = [];
        $user = auth()->user();
        $startOfMonth = \Carbon\Carbon::now()->startOfMonth();
        $startOfLastMonth = \Carbon\Carbon::now()->subMonth()->startOfMonth();
        $endOfLastMonth = \Carbon\Carbon::now()->subMonth()->endOfMonth();

        // Current and last month revenue
        $currentRevenue = \App\Models\RevenueSource::where('user_id', $user->id)
            ->where('date', '>=', $startOfMonth)
            ->sum('amount');

        $lastRevenue = \App\Models\RevenueSource::where('user_id', $user->id)
            ->whereBetween('date', [$startOfLastMonth, $endOfLastMonth])
            ->sum('amount');

        // Revenue diversification check
        $uniqueSources = \App\Models\RevenueSource::where('user_id', $user->id)
            ->where('date', '>=', $startOfMonth)
            ->distinct('source')
            ->count('source');

        $topSource = \App\Models\RevenueSource::where('user_id', $user->id)
            ->where('date', '>=', $startOfMonth)
            ->selectRaw('source, SUM(amount) as total')
            ->groupBy('source')
            ->orderByDesc('total')
            ->first();

        // Check for revenue concentration risk
        if ($topSource && $currentRevenue > 0) {
            $concentration = ($topSource->total / $currentRevenue) * 100;

            if ($concentration > 70 && $uniqueSources < 3) {
                $sourceName = ucwords(str_replace('_', ' ', $topSource->source));
                $insights[] = [
                    'type' => 'warning',
                    'title' => 'Revenue Concentration Risk',
                    'description' => "Over " . number_format($concentration, 0) . "% of revenue comes from {$sourceName}. Diversify revenue streams to reduce business risk and improve stability.",
                    'icon' => 'heroicon-o-exclamation-triangle',
                ];
            } elseif ($uniqueSources >= 4) {
                $insights[] = [
                    'type' => 'success',
                    'title' => 'Well-Diversified Revenue',
                    'description' => "Excellent! You have {$uniqueSources} active revenue streams. This diversification reduces risk and creates more stability for your business.",
                    'icon' => 'heroicon-o-squares-2x2',
                ];
            }
        }

        // MRR (Monthly Recurring Revenue) insights
        $mrr = \App\Models\RevenueSource::where('user_id', $user->id)
            ->where('date', '>=', $startOfMonth)
            ->where('source', 'subscriptions')
            ->sum('amount');

        $lastMrr = \App\Models\RevenueSource::where('user_id', $user->id)
            ->whereBetween('date', [$startOfLastMonth, $endOfLastMonth])
            ->where('source', 'subscriptions')
            ->sum('amount');

        if ($mrr > 0) {
            $mrrPercentage = $currentRevenue > 0 ? ($mrr / $currentRevenue) * 100 : 0;
            $mrrChange = $lastMrr > 0 ? (($mrr - $lastMrr) / $lastMrr) * 100 : 0;

            if ($mrrPercentage > 50) {
                $insights[] = [
                    'type' => 'success',
                    'title' => 'Strong Recurring Revenue Base',
                    'description' => "Excellent! " . number_format($mrrPercentage, 0) . "% of revenue is recurring (MRR: $" . number_format($mrr, 0) . "). This provides revenue predictability and business stability.",
                    'icon' => 'heroicon-o-arrow-path',
                ];
            } elseif ($mrrChange > 10) {
                $insights[] = [
                    'type' => 'success',
                    'title' => 'Growing Recurring Revenue',
                    'description' => "MRR increased " . number_format($mrrChange, 1) . "% this month. Continue focusing on subscription growth to build a more predictable revenue stream.",
                    'icon' => 'heroicon-o-arrow-trending-up',
                ];
            }
        } elseif ($currentRevenue > 5000) {
            $insights[] = [
                'type' => 'info',
                'title' => 'Consider Recurring Revenue',
                'description' => "You have no recurring revenue streams yet. Consider subscription-based offerings or retainer contracts to create predictable monthly income and business stability.",
                'icon' => 'heroicon-o-arrow-path',
            ];
        }

        // Revenue volatility check - compare to HISTORICAL AVERAGE not last month
        // Use the change from business data which is already calculated vs historical average
        if ($data['avg_historical_revenue'] > 0) {
            $revenueChangeVsAverage = $data['revenue_change'];

            // Only flag as volatile if deviation exceeds 40% from historical average
            if (abs($revenueChangeVsAverage) > 40) {
                $direction = $revenueChangeVsAverage > 0 ? 'increased' : 'decreased';
                $insights[] = [
                    'type' => 'warning',
                    'title' => 'High Revenue Volatility',
                    'description' => "Revenue {$direction} " . number_format(abs($revenueChangeVsAverage), 0) . "% compared to your historical average. High volatility can make planning difficult. Focus on building recurring revenue to stabilize income.",
                    'icon' => 'heroicon-o-chart-bar',
                ];
            } elseif (abs($revenueChangeVsAverage) <= 10) {
                // If within 10% of historical average, it's stable (don't show as insight unless there's nothing else)
                // This prevents false "volatility" warnings
            }
        }

        return $insights;
    }

    protected function getMarketingInsights(array $data): array
    {
        // Skip marketing insights - too technical for most business owners
        // Basic revenue and expense insights are more actionable
        return [];
    }
}
