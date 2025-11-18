<?php

namespace App\Services;

use App\Models\ClientPayment;
use App\Models\Expense;
use App\Models\RevenueSource;
use App\Models\SwotAnalysis;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SwotGeneratorService
{
    protected OpenAIService $openAI;
    protected FinancialMetricsCalculator $calculator;

    public function __construct(OpenAIService $openAI, FinancialMetricsCalculator $calculator)
    {
        $this->openAI = $openAI;
        $this->calculator = $calculator;
    }

    /**
     * Generate SWOT analysis using AI (wrapper for widget)
     */
    public function generateSwotAnalysis(int $userId, int $days = 90): array
    {
        return $this->generateSwot($userId, $days);
    }

    /**
     * Generate SWOT analysis using AI
     */
    public function generateSwot(int $userId, int $days = 90): array
    {
        // Gather business data
        $businessData = $this->gatherBusinessData($userId, $days);

        // Check if we have enough data
        if (empty($businessData['has_data'])) {
            return $this->getDefaultSwot();
        }

        // Try AI generation first
        if (!empty(config('services.openai.key'))) {
            try {
                $aiSwot = $this->generateWithAI($businessData);
                if ($aiSwot) {
                    return $this->createSwotRecords($userId, $aiSwot);
                }
            } catch (\Exception $e) {
                Log::warning('AI SWOT generation failed, using rule-based', ['error' => $e->getMessage()]);
            }
        }

        // Fallback to rule-based generation
        $ruleBasedSwot = $this->generateWithRules($businessData);
        return $this->createSwotRecords($userId, $ruleBasedSwot);
    }

    /**
     * Gather business data for analysis
     */
    protected function gatherBusinessData(int $userId, int $days): array
    {
        $user = User::find($userId);
        if (!$user) {
            return ['has_data' => false];
        }

        $startDate = Carbon::today()->subDays($days);
        $endDate = Carbon::today();

        // Get expenses
        $expenses = Expense::where('user_id', $userId)
            ->where('date', '>=', $startDate)
            ->get();

        // Get revenue from RevenueSource
        $revenueSources = RevenueSource::where('user_id', $userId)
            ->where('date', '>=', $startDate)
            ->where('amount', '>=', 0)
            ->get();

        // Get revenue from ClientPayments (excluding tax portion)
        $clientPayments = ClientPayment::where('client_payments.user_id', $userId)
            ->whereBetween('client_payments.payment_date', [$startDate, Carbon::now()])
            ->where('client_payments.amount', '>=', 0)
            ->join('client_invoices', 'client_payments.client_invoice_id', '=', 'client_invoices.id')
            ->selectRaw('
                client_payments.payment_date as date,
                CASE
                    WHEN client_invoices.total_amount > 0
                    THEN client_payments.amount * (client_invoices.subtotal / client_invoices.total_amount)
                    ELSE client_payments.amount
                END as amount
            ')
            ->get();

        // Get current and previous month metrics using calculator
        $currentMetrics = $this->calculator->getCurrentMonthMetrics($user);
        $previousMetrics = $this->calculator->getLastMonthMetrics($user);

        // Check if we have historical data for valid comparison
        $hasHistoricalData = $previousMetrics['revenue'] > 0 || $previousMetrics['expenses'] > 0;

        // Calculate revenue growth only if we have valid historical data
        $revenueGrowth = null;
        if ($hasHistoricalData && $previousMetrics['revenue'] > 0) {
            $revenueGrowth = $this->calculator->calculatePercentageChange(
                $currentMetrics['revenue'],
                $previousMetrics['revenue']
            );
        }

        // Get revenue trend
        $revenueTrend = $this->calculator->getMonthlyTrend($user, 'revenue');

        // Top expense categories
        $topExpenses = $expenses->groupBy('category')
            ->map(fn ($items) => $items->sum('amount'))
            ->sortDesc()
            ->take(3);

        // Revenue sources - combine all sources
        $allRevenueSources = collect();

        // Add RevenueSource data
        foreach ($revenueSources->groupBy('source') as $source => $items) {
            $allRevenueSources->put($source, $items->sum('amount'));
        }

        // Add ClientPayment data
        if ($clientPayments->sum('amount') > 0) {
            $allRevenueSources->put('client_invoices', $clientPayments->sum('amount'));
        }

        $hasData = $expenses->isNotEmpty() || $revenueSources->isNotEmpty() || $clientPayments->isNotEmpty() || array_sum($revenueTrend) > 0;

        return [
            'has_data' => $hasData,
            'has_historical_data' => $hasHistoricalData,
            'period_days' => $days,
            'total_revenue' => $currentMetrics['revenue'],
            'total_expenses' => $currentMetrics['expenses'],
            'avg_revenue' => count($revenueTrend) > 0 ? array_sum($revenueTrend) / count($revenueTrend) : 0,
            'avg_profit' => $currentMetrics['profit'],
            'latest_cash_flow' => $currentMetrics['cash_flow'],
            'revenue_growth' => $revenueGrowth, // null if no historical data
            'profit_margin' => $currentMetrics['profit_margin'],
            'top_expenses' => $topExpenses->toArray(),
            'revenue_sources' => $allRevenueSources->toArray(),
            'revenue_sources_count' => $allRevenueSources->count(),
            'metrics_count' => count(array_filter($revenueTrend)),
        ];
    }

    /**
     * Generate SWOT with AI
     */
    protected function generateWithAI(array $data): ?array
    {
        $prompt = $this->buildPrompt($data);

        $response = $this->openAI->chat([
            [
                'role' => 'system',
                'content' => 'You are a business strategy consultant. Analyze the provided business data and generate a comprehensive SWOT analysis (Strengths, Weaknesses, Opportunities, Threats). Each category should have 3-5 specific, actionable items with priority levels (1-10). Respond ONLY with valid JSON in this exact format: {"strengths": [{"description": "...", "priority": 8}], "weaknesses": [...], "opportunities": [...], "threats": [...]}'
            ],
            [
                'role' => 'user',
                'content' => $prompt,
            ],
        ], [
            'feature' => 'swot_analysis',
            'action' => 'generate',
            'temperature' => 0.7,
            'max_tokens' => 1500,
        ]);

        if ($response) {
            try {
                // Try to extract JSON from response
                if (preg_match('/\{[\s\S]*\}/', $response, $matches)) {
                    $json = $matches[0];
                    $decoded = json_decode($json, true);

                    if ($decoded && isset($decoded['strengths'], $decoded['weaknesses'], $decoded['opportunities'], $decoded['threats'])) {
                        return $decoded;
                    }
                }
            } catch (\Exception $e) {
                Log::error('Failed to parse AI SWOT response', ['response' => $response, 'error' => $e->getMessage()]);
            }
        }

        return null;
    }

    /**
     * Build prompt for AI
     */
    protected function buildPrompt(array $data): string
    {
        $prompt = "Analyze this business data from the last {$data['period_days']} days:\n\n";
        $prompt .= "**Financial Overview:**\n";
        $prompt .= "- Total Revenue: $" . number_format($data['total_revenue'], 2) . "\n";
        $prompt .= "- Total Expenses: $" . number_format($data['total_expenses'], 2) . "\n";
        $prompt .= "- Profit Margin: " . number_format($data['profit_margin'], 1) . "%\n";

        // Only include growth if we have historical data
        if ($data['revenue_growth'] !== null && $data['has_historical_data']) {
            $prompt .= "- Revenue Growth: " . number_format($data['revenue_growth'], 1) . "%\n";
        } else {
            $prompt .= "- Revenue Growth: Insufficient historical data to calculate trend\n";
        }

        $prompt .= "- Current Cash Flow: $" . number_format($data['latest_cash_flow'], 2) . "\n\n";

        if (!empty($data['top_expenses'])) {
            $prompt .= "**Top Expense Categories:**\n";
            foreach ($data['top_expenses'] as $category => $amount) {
                $prompt .= "- " . ucwords(str_replace('_', ' ', $category)) . ": $" . number_format($amount, 2) . "\n";
            }
            $prompt .= "\n";
        }

        if (!empty($data['revenue_sources'])) {
            $prompt .= "**Revenue Sources:**\n";
            foreach ($data['revenue_sources'] as $source => $amount) {
                $prompt .= "- " . ucwords(str_replace('_', ' ', $source)) . ": $" . number_format($amount, 2) . "\n";
            }
            $prompt .= "\n";
        }

        $prompt .= "**IMPORTANT CONSTRAINTS:**\n";
        $prompt .= "- Only use the data provided above. Do NOT make assumptions or add fictional details.\n";
        $prompt .= "- If historical data is insufficient, note 'Trend unavailable - insufficient history' instead of calculating growth.\n";
        $prompt .= "- Cash flow shown is NET cash flow (positive = surplus, negative = deficit).\n";
        if ($data['latest_cash_flow'] > 0) {
            $prompt .= "- Do NOT list positive cash flow as a weakness. It is a strength.\n";
        }

        // Add context about business stage and temporary situations
        if (!$data['has_historical_data']) {
            $prompt .= "- This is an EARLY-STAGE business with limited history. Negative profit margins are NORMAL during setup/ramp-up phase.\n";
            $prompt .= "- Frame weaknesses constructively for a startup context (e.g., 'temporary negative margin reflects setup costs' not 'critical profitability issue').\n";
        }

        if ($data['profit_margin'] < -50) {
            $prompt .= "- The deeply negative profit margin likely reflects ONE-TIME or SETUP expenses, not ongoing operational issues.\n";
            $prompt .= "- Contextualize this as a temporary phase requiring monitoring, not a critical structural weakness.\n";
        }

        $prompt .= "\nGenerate a SWOT analysis with 3-5 items per category based ONLY on the real data provided. Focus on actionable insights that are appropriate for the business stage.";

        return $prompt;
    }

    /**
     * Generate SWOT with business rules (fallback)
     */
    protected function generateWithRules(array $data): array
    {
        $swot = [
            'strengths' => [],
            'weaknesses' => [],
            'opportunities' => [],
            'threats' => [],
        ];

        // Strengths
        if ($data['revenue_growth'] !== null && $data['revenue_growth'] > 10 && $data['has_historical_data']) {
            $swot['strengths'][] = [
                'description' => "Strong revenue growth of " . number_format($data['revenue_growth'], 1) . "% indicates market demand and effective sales strategy.",
                'priority' => 9,
            ];
        }

        if ($data['profit_margin'] > 20) {
            $swot['strengths'][] = [
                'description' => "Healthy profit margin of " . number_format($data['profit_margin'], 1) . "% shows efficient cost management and strong pricing power.",
                'priority' => 8,
            ];
        }

        if ($data['latest_cash_flow'] > 0) {
            $swot['strengths'][] = [
                'description' => "Positive cash flow of $" . number_format($data['latest_cash_flow'], 2) . " provides financial stability and growth opportunities.",
                'priority' => 8,
            ];
        }

        if ($data['revenue_sources_count'] > 2) {
            $swot['strengths'][] = [
                'description' => "Diversified revenue streams across " . $data['revenue_sources_count'] . " sources reduce dependency risk.",
                'priority' => 7,
            ];
        }

        // Weaknesses
        if ($data['revenue_growth'] !== null && $data['revenue_growth'] < 0 && $data['has_historical_data']) {
            $swot['weaknesses'][] = [
                'description' => "Revenue declining by " . number_format(abs($data['revenue_growth']), 1) . "% requires immediate attention to sales and marketing strategies.",
                'priority' => 9,
            ];
        } elseif ($data['revenue_growth'] === null || !$data['has_historical_data']) {
            // Note insufficient data instead of false weakness
            $swot['weaknesses'][] = [
                'description' => "Insufficient historical data to calculate revenue trends - need at least 2 months of data for meaningful analysis.",
                'priority' => 5,
            ];
        }

        // Context-aware profit margin weakness detection
        if ($data['profit_margin'] < 10 && $data['total_revenue'] > 0) {
            // Check if this is an early-stage business or temporary situation
            $isEarlyStage = !$data['has_historical_data'] || $data['period_days'] < 60;
            $hasExpenseSpike = $this->detectExpenseSpike($data);

            if ($isEarlyStage || $hasExpenseSpike) {
                // Contextual weakness for temporary/setup phase
                if ($data['profit_margin'] < -50) {
                    // Significant negative margin - acknowledge but contextualize
                    $swot['weaknesses'][] = [
                        'description' => "High short-term expenses created a temporary negative profit margin of " . number_format($data['profit_margin'], 1) . "%. This reflects setup costs rather than operational performance. Monitor spending consistency as business stabilizes.",
                        'priority' => 6,
                    ];
                } else {
                    // Minor negative or low positive margin
                    $swot['weaknesses'][] = [
                        'description' => "Current profit margin of " . number_format($data['profit_margin'], 1) . "% reflects early-stage operations. Focus on stabilizing revenue and controlling ongoing expenses as the business matures.",
                        'priority' => 5,
                    ];
                }
            } else {
                // Established business with consistently low margin - genuine concern
                $swot['weaknesses'][] = [
                    'description' => "Sustained low profit margin of " . number_format($data['profit_margin'], 1) . "% suggests structural issues with costs or pricing. Consider reviewing pricing strategy and operational efficiency.",
                    'priority' => 8,
                ];
            }
        }

        // Opportunities
        if ($data['revenue_growth'] !== null && $data['revenue_growth'] > 0 && $data['revenue_growth'] < 20 && $data['has_historical_data']) {
            $swot['opportunities'][] = [
                'description' => "Moderate growth of " . number_format($data['revenue_growth'], 1) . "% can be accelerated with increased marketing investment.",
                'priority' => 7,
            ];
        }

        if (!empty($data['top_expenses'])) {
            $topExpense = array_keys($data['top_expenses'])[0];
            $swot['opportunities'][] = [
                'description' => "Optimizing " . ucwords(str_replace('_', ' ', $topExpense)) . " expenses could significantly improve profitability.",
                'priority' => 7,
            ];
        }

        $swot['opportunities'][] = [
            'description' => "Leveraging data analytics and automation can improve operational efficiency and reduce costs.",
            'priority' => 6,
        ];

        // Threats
        if ($data['revenue_sources_count'] <= 1) {
            $swot['threats'][] = [
                'description' => "Heavy reliance on single revenue source creates vulnerability to market changes.",
                'priority' => 8,
            ];
        }

        if ($data['profit_margin'] < 15 && $data['revenue_growth'] !== null && $data['revenue_growth'] < 5 && $data['has_historical_data']) {
            $swot['threats'][] = [
                'description' => "Combination of low margins and slow growth makes business vulnerable to economic downturns.",
                'priority' => 8,
            ];
        }

        $swot['threats'][] = [
            'description' => "Market competition and changing customer preferences require continuous innovation and adaptation.",
            'priority' => 6,
        ];

        // Add defaults if categories are empty
        if (empty($swot['strengths'])) {
            $swot['strengths'][] = [
                'description' => "Business is operational and generating revenue, providing foundation for growth.",
                'priority' => 5,
            ];
        }

        if (empty($swot['weaknesses'])) {
            $swot['weaknesses'][] = [
                'description' => "Limited historical data makes it difficult to identify long-term trends and patterns.",
                'priority' => 5,
            ];
        }

        if (empty($swot['opportunities'])) {
            $swot['opportunities'][] = [
                'description' => "Digital transformation and online presence can expand market reach.",
                'priority' => 6,
            ];
        }

        if (empty($swot['threats'])) {
            $swot['threats'][] = [
                'description' => "Economic uncertainty and market volatility require financial prudence and risk management.",
                'priority' => 6,
            ];
        }

        return $swot;
    }

    /**
     * Create SWOT records in database
     * IMPORTANT: Replaces all existing records (does not append)
     */
    protected function createSwotRecords(int $userId, array $swot): array
    {
        // Delete all existing SWOT records for this user to avoid duplicates
        SwotAnalysis::where('user_id', $userId)->delete();

        $created = [];

        foreach (['strengths' => 'strength', 'weaknesses' => 'weakness', 'opportunities' => 'opportunity', 'threats' => 'threat'] as $category => $type) {
            foreach ($swot[$category] as $item) {
                $record = SwotAnalysis::create([
                    'user_id' => $userId,
                    'type' => $type,
                    'description' => $item['description'],
                    'priority' => $item['priority'] ?? 5,
                ]);

                $created[] = $record;
            }
        }

        return [
            'success' => true,
            'created' => count($created),
            'items' => $created,
        ];
    }

    /**
     * Detect if there's an expense spike (indicating temporary/one-time costs)
     */
    protected function detectExpenseSpike(array $data): bool
    {
        // If we don't have detailed expense data, can't detect spike
        if (empty($data['top_expenses'])) {
            return false;
        }

        // Calculate if current month's expenses are significantly higher than expected
        // This is a simplified check - actual anomaly detection is more sophisticated
        $totalTopExpenses = array_sum($data['top_expenses']);
        $avgMonthlyExpenses = $data['total_expenses'];

        // If top expenses represent > 80% of total expenses, suggests concentrated/one-time costs
        if ($totalTopExpenses > 0 && ($totalTopExpenses / $avgMonthlyExpenses) > 0.8) {
            return true;
        }

        // If profit margin is deeply negative (< -50%), suggests unusual expense period
        if ($data['profit_margin'] < -50) {
            return true;
        }

        return false;
    }

    /**
     * Get default SWOT when no data available
     */
    protected function getDefaultSwot(): array
    {
        return [
            'success' => false,
            'message' => 'Not enough business data to generate SWOT analysis. Please add expenses and revenue data first.',
            'created' => 0,
        ];
    }
}
