<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\User;

class OpenAIService
{
    protected string $apiKey;
    protected string $baseUrl = 'https://api.openai.com/v1';
    protected string $model = 'gpt-4';
    protected AIUsageService $usageService;

    public function __construct(AIUsageService $usageService)
    {
        $this->apiKey = config('services.openai.key', '');
        $this->usageService = $usageService;
    }

    /**
     * Generate a chat completion using GPT-4 with usage tracking
     */
    public function chat(array $messages, array $options = []): ?string
    {
        $user = $options['user'] ?? auth()->user();
        $feature = $options['feature'] ?? 'general';
        $action = $options['action'] ?? null;

        // Check if user can make request
        if ($user) {
            $canUse = $this->usageService->canMakeRequest($user, $feature);

            if (!$canUse['allowed']) {
                Log::warning('AI request blocked - limit reached', [
                    'user_id' => $user->id,
                    'feature' => $feature,
                    'reason' => $canUse['reason'] ?? 'unknown',
                ]);

                throw new \Exception($canUse['message'] ?? 'AI request limit reached');
            }
        }

        if (empty($this->apiKey)) {
            Log::warning('OpenAI API key is not configured');
            return null;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])
            ->timeout(60)
            ->post($this->baseUrl . '/chat/completions', [
                'model' => $options['model'] ?? $this->model,
                'messages' => $messages,
                'temperature' => $options['temperature'] ?? 0.7,
                'max_tokens' => $options['max_tokens'] ?? 1000,
            ]);

            if ($response->successful()) {
                $result = $response->json('choices.0.message.content');

                // Log usage
                if ($user) {
                    $tokensUsed = $response->json('usage.total_tokens', 0);
                    $promptSummary = $messages[array_key_last($messages)]['content'] ?? '';

                    $this->usageService->logUsage(
                        $user,
                        $feature,
                        $action,
                        $tokensUsed,
                        $promptSummary,
                        [
                            'model' => $options['model'] ?? $this->model,
                            'max_tokens' => $options['max_tokens'] ?? 1000,
                        ]
                    );
                }

                return $result;
            }

            $errorBody = $response->json();

            Log::error('OpenAI API error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            // Throw user-friendly error for quota/rate limit issues
            if ($response->status() === 429) {
                throw new \Exception('Our AI Server is busy, please try again in a few minutes.');
            }

            return null;
        } catch (\Exception $e) {
            // Re-throw if it's a limit exception
            if (str_contains($e->getMessage(), 'limit reached') || str_contains($e->getMessage(), 'not available')) {
                throw $e;
            }

            Log::error('OpenAI API exception', [
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Analyze business data and generate insights
     */
    public function generateBusinessInsights(array $data): ?string
    {
        $prompt = $this->buildBusinessInsightsPrompt($data);

        return $this->chat([
            [
                'role' => 'system',
                'content' => 'You are a business analytics AI assistant. Analyze the provided business data and generate actionable insights. Focus on trends, anomalies, opportunities, and risks. Be concise and specific.',
            ],
            [
                'role' => 'user',
                'content' => $prompt,
            ],
        ], [
            'feature' => 'business_insights',
            'action' => 'generate',
        ]);
    }

    /**
     * Process natural language query about business data
     */
    public function processNaturalLanguageQuery(string $query, array $availableData): ?string
    {
        $dataContext = json_encode($availableData, JSON_PRETTY_PRINT);

        return $this->chat([
            [
                'role' => 'system',
                'content' => 'You are a business analytics AI assistant. Answer questions about business data based on the provided context. Be specific and data-driven in your responses.',
            ],
            [
                'role' => 'user',
                'content' => "Available data:\n{$dataContext}\n\nQuestion: {$query}",
            ],
        ], [
            'feature' => 'business_insights',
            'action' => 'query',
            'max_tokens' => 500,
        ]);
    }

    /**
     * Detect anomalies in business metrics
     */
    public function detectAnomalies(array $historicalData, array $currentData): ?array
    {
        $prompt = "Analyze the following business metrics data and identify any anomalies or unusual patterns.\n\n";
        $prompt .= "Historical data:\n" . json_encode($historicalData, JSON_PRETTY_PRINT) . "\n\n";
        $prompt .= "Current data:\n" . json_encode($currentData, JSON_PRETTY_PRINT) . "\n\n";
        $prompt .= "Respond with a JSON array of anomalies. Each anomaly should have 'metric', 'severity' (low/medium/high), 'description', and 'recommendation' fields.";

        $response = $this->chat([
            [
                'role' => 'system',
                'content' => 'You are an anomaly detection AI. Analyze business metrics and identify unusual patterns. Respond only with valid JSON.',
            ],
            [
                'role' => 'user',
                'content' => $prompt,
            ],
        ], [
            'feature' => 'anomaly_detection',
            'action' => 'detect',
            'temperature' => 0.3,
        ]);

        if ($response) {
            try {
                return json_decode($response, true);
            } catch (\Exception $e) {
                Log::error('Failed to parse anomaly detection response', [
                    'response' => $response,
                ]);
            }
        }

        return null;
    }

    /**
     * Generate predictive forecast
     */
    public function generateForecast(array $historicalData, int $days = 30): ?string
    {
        $dataJson = json_encode($historicalData, JSON_PRETTY_PRINT);

        return $this->chat([
            [
                'role' => 'system',
                'content' => 'You are a predictive analytics AI. Generate forecasts based on historical business data. Provide specific predictions with confidence levels.',
            ],
            [
                'role' => 'user',
                'content' => "Based on this historical data:\n{$dataJson}\n\nGenerate a {$days}-day forecast for key metrics including revenue, expenses, and cash flow. Include best case, worst case, and most likely scenarios.",
            ],
        ], [
            'feature' => 'financial_forecast',
            'action' => 'generate',
            'max_tokens' => 800,
        ]);
    }

    /**
     * Build prompt for business insights
     */
    protected function buildBusinessInsightsPrompt(array $data): string
    {
        $prompt = "Analyze the following business data and provide actionable insights:\n\n";

        foreach ($data as $category => $values) {
            $prompt .= ucwords(str_replace('_', ' ', $category)) . ":\n";
            $prompt .= json_encode($values, JSON_PRETTY_PRINT) . "\n\n";
        }

        $prompt .= "Please provide:\n";
        $prompt .= "1. Key trends and patterns\n";
        $prompt .= "2. Potential risks or concerns\n";
        $prompt .= "3. Opportunities for improvement\n";
        $prompt .= "4. Specific actionable recommendations\n";

        return $prompt;
    }

    /**
     * Generate email content from context
     */
    public function generateEmail(string $purpose, array $contactInfo = [], string $tone = 'professional'): ?array
    {
        $contactContext = !empty($contactInfo) ? "\n\nContact Information:\n" . json_encode($contactInfo, JSON_PRETTY_PRINT) : '';

        $toneInstructions = match($tone) {
            'professional' => 'Use a professional, business-appropriate tone.',
            'friendly' => 'Use a warm, friendly, and conversational tone.',
            'casual' => 'Use a casual, relaxed tone.',
            'formal' => 'Use a very formal, respectful tone.',
            default => 'Use a professional tone.',
        };

        $response = $this->chat([
            [
                'role' => 'system',
                'content' => "You are a professional email writer. Generate compelling email content based on the given purpose. {$toneInstructions} Return a JSON object with 'subject' and 'body' fields. The body should be in HTML format with proper paragraphs.",
            ],
            [
                'role' => 'user',
                'content' => "Generate an email for the following purpose:\n{$purpose}{$contactContext}\n\nProvide a compelling subject line and well-structured email body in HTML format.",
            ],
        ], [
            'feature' => 'email_generation',
            'action' => 'generate',
            'temperature' => 0.7,
            'max_tokens' => 800,
        ]);

        if ($response) {
            try {
                // Try to parse as JSON first
                $decoded = json_decode($response, true);
                if ($decoded && isset($decoded['subject']) && isset($decoded['body'])) {
                    return $decoded;
                }

                // If not JSON, try to extract subject and body
                if (preg_match('/Subject:\s*(.+)/i', $response, $subjectMatch)) {
                    $subject = trim($subjectMatch[1]);
                    $body = preg_replace('/Subject:\s*.+/i', '', $response);
                    return [
                        'subject' => $subject,
                        'body' => '<p>' . nl2br(trim($body)) . '</p>',
                    ];
                }

                // Fallback: use first line as subject, rest as body
                $lines = explode("\n", $response);
                return [
                    'subject' => trim($lines[0] ?? 'Email Subject'),
                    'body' => '<p>' . nl2br(trim(implode("\n", array_slice($lines, 1)))) . '</p>',
                ];
            } catch (\Exception $e) {
                Log::error('Failed to parse email generation response', [
                    'response' => $response,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return null;
    }

    /**
     * Improve existing email content
     */
    public function improveEmail(string $subject, string $body, string $instruction = 'improve'): ?array
    {
        $improvementType = match($instruction) {
            'improve' => 'Improve the clarity, professionalism, and effectiveness of this email while maintaining its core message.',
            'shorten' => 'Make this email more concise while keeping the key points.',
            'expand' => 'Expand this email with more details and context while staying on topic.',
            'professional' => 'Make this email more professional and business-appropriate.',
            'friendly' => 'Make this email warmer and more friendly while staying professional.',
            'persuasive' => 'Make this email more persuasive and compelling.',
            default => $instruction,
        };

        $response = $this->chat([
            [
                'role' => 'system',
                'content' => "You are an expert email editor. {$improvementType} Return a JSON object with improved 'subject' and 'body' fields. Keep the body in HTML format.",
            ],
            [
                'role' => 'user',
                'content' => "Current Subject: {$subject}\n\nCurrent Body:\n{$body}\n\nPlease improve this email and return the improved version as JSON with 'subject' and 'body' fields.",
            ],
        ], [
            'feature' => 'email_generation',
            'action' => $instruction,
            'temperature' => 0.5,
            'max_tokens' => 1000,
        ]);

        if ($response) {
            try {
                $decoded = json_decode($response, true);
                if ($decoded && isset($decoded['subject']) && isset($decoded['body'])) {
                    return $decoded;
                }

                // Try to extract manually if not JSON
                if (preg_match('/Subject:\s*(.+)/i', $response, $subjectMatch)) {
                    $subject = trim($subjectMatch[1]);
                    $body = preg_replace('/Subject:\s*.+/i', '', $response);
                    return [
                        'subject' => $subject,
                        'body' => trim($body),
                    ];
                }
            } catch (\Exception $e) {
                Log::error('Failed to parse email improvement response', [
                    'response' => $response,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return null;
    }

    /**
     * Change email tone
     */
    public function changeEmailTone(string $subject, string $body, string $targetTone = 'professional'): ?array
    {
        return $this->improveEmail($subject, $body, $targetTone);
    }

    public function rewriteCampaignDraft(string $subject, string $body, array $context = []): ?array
    {
        $tone = $context['tone'] ?? 'friendly';
        $offer = $context['offer'] ?? 'none';
        $serviceFocus = $context['service_focus'] ?? null;
        $customInstruction = $context['custom_instruction'] ?? null;
        $extraInstruction = $context['extra_instruction'] ?? null;
        $campaignPreset = $context['campaign_preset'] ?? 'campaign';

        $response = $this->chat([
            [
                'role' => 'system',
                'content' => 'You are an expert small-business marketing email editor. Rewrite campaign drafts for service businesses. Return only valid JSON with subject and body fields. Keep the body in clean HTML paragraphs. Preserve all merge variables such as {{first_name}} and all existing links/hrefs exactly unless asked to add wording around them. Do not claim an offer exists unless the requested offer angle requires one.',
            ],
            [
                'role' => 'user',
                'content' => "Campaign type: {$campaignPreset}\nTone: {$tone}\nOffer angle: {$offer}\nService focus: " . ($serviceFocus ?: 'none') . "\nCustom direction: " . ($customInstruction ?: 'none') . "\nExtra instruction: " . ($extraInstruction ?: 'none') . "\n\nCurrent Subject: {$subject}\n\nCurrent Body:\n{$body}\n\nRewrite this draft so it is clearer, more action-oriented, and ready for the owner to review before sending.",
            ],
        ], [
            'feature' => 'email_generation',
            'action' => 'rewrite_campaign_draft',
            'temperature' => 0.55,
            'max_tokens' => 1000,
        ]);

        if (! $response) {
            return null;
        }

        try {
            $json = trim($response);
            $json = preg_replace('/^```(?:json)?\s*/i', '', $json);
            $json = preg_replace('/\s*```$/', '', $json);
            $decoded = json_decode($json, true);

            if ($decoded && isset($decoded['subject']) && isset($decoded['body'])) {
                return $decoded;
            }
        } catch (\Exception $e) {
            Log::error('Failed to parse campaign draft rewrite response', [
                'response' => $response,
                'error' => $e->getMessage(),
            ]);
        }

        return null;
    }
}
