<?php

namespace App\Services\SocialMedia;

use App\Models\SocialMediaConnection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MetaApiService
{
    protected string $baseUrl = 'https://graph.facebook.com/v18.0';

    /**
     * Fetch metrics from Meta (Facebook/Instagram)
     */
    public function fetchMetrics(SocialMediaConnection $connection): ?array
    {
        $this->checkAndRefreshToken($connection);

        $metrics = $this->fetchInsights($connection);

        if (!$metrics) {
            return null;
        }

        return $this->transformMetricsToStandardFormat($metrics, $connection->platform);
    }

    /**
     * Fetch insights from Meta Graph API
     */
    protected function fetchInsights(SocialMediaConnection $connection): ?array
    {
        $accountId = $connection->account_id;
        $accessToken = $connection->access_token;

        // Try to fetch ads data first if account_id looks like an ad account
        if (str_starts_with($accountId, 'act_')) {
            $adsData = $this->fetchAdsInsights($accountId, $accessToken);
            if ($adsData) {
                return $adsData;
            }
        }

        // Fallback to page insights (organic)
        $endpoint = "/{$accountId}/insights";

        try {
            $response = Http::get($this->baseUrl . $endpoint, [
                'access_token' => $accessToken,
                'metric' => implode(',', [
                    'page_impressions',
                    'page_engaged_users',
                    'page_post_engagements',
                    'page_fans',
                    'page_video_views',
                    'page_actions_post_reactions_total',
                ]),
                'period' => 'day',
                'since' => now()->subDay()->format('Y-m-d'),
                'until' => now()->format('Y-m-d'),
            ]);

            if ($response->successful()) {
                return $response->json('data');
            }

            Log::error('Meta API error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Meta API exception', ['message' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Fetch ads insights from Meta Marketing API
     */
    protected function fetchAdsInsights(string $adAccountId, string $accessToken): ?array
    {
        try {
            $response = Http::get($this->baseUrl . "/{$adAccountId}/insights", [
                'access_token' => $accessToken,
                'fields' => implode(',', [
                    'impressions',
                    'reach',
                    'clicks',
                    'spend',
                    'cpc',
                    'cpm',
                    'actions',
                ]),
                'time_range' => json_encode([
                    'since' => now()->subDay()->format('Y-m-d'),
                    'until' => now()->format('Y-m-d'),
                ]),
                'level' => 'account',
            ]);

            if ($response->successful()) {
                $data = $response->json('data');
                if (!empty($data)) {
                    return ['type' => 'ads', 'data' => $data[0] ?? []];
                }
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Meta Ads API exception', ['message' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Transform Meta metrics to standard format
     */
    protected function transformMetricsToStandardFormat(array $metrics, string $platform): array
    {
        // Check if this is ads data
        if (isset($metrics['type']) && $metrics['type'] === 'ads') {
            return $this->transformAdsMetrics($metrics['data'], $platform);
        }

        // Extract values from organic metrics array
        $data = [];
        foreach ($metrics as $metric) {
            $name = $metric['name'] ?? null;
            $value = $metric['values'][0]['value'] ?? 0;

            if ($name) {
                $data[$name] = $value;
            }
        }

        // Map organic data to standard format
        return [
            'platform' => $platform,
            'followers' => $data['page_fans'] ?? 0,
            'impressions' => $data['page_impressions'] ?? 0,
            'reach' => $data['page_impressions'] ?? 0,
            'engagement' => $data['page_post_engagements'] ?? 0,
            'clicks' => $data['page_engaged_users'] ?? 0,
            'engagement_rate' => $this->calculateEngagementRate(
                $data['page_post_engagements'] ?? 0,
                $data['page_fans'] ?? 1
            ),
            'awareness' => $data['page_impressions'] ?? 0,
            'leads' => 0,
            'conversions' => 0,
            'retention_count' => 0,
            'cost_per_click' => 0,
            'cost_per_conversion' => 0,
            'ad_spend' => 0,
            'conversion_rate' => 0,
            'customer_lifetime_value' => 0,
            'customer_acquisition_cost' => 0,
            'clv_cac_ratio' => 0,
            'roi' => 0,
        ];
    }

    /**
     * Transform ads metrics to standard format
     */
    protected function transformAdsMetrics(array $data, string $platform): array
    {
        $impressions = (int)($data['impressions'] ?? 0);
        $clicks = (int)($data['clicks'] ?? 0);
        $spend = (float)($data['spend'] ?? 0);
        $reach = (int)($data['reach'] ?? 0);

        // Extract conversions from actions array
        $conversions = 0;
        $leads = 0;
        if (isset($data['actions']) && is_array($data['actions'])) {
            foreach ($data['actions'] as $action) {
                if ($action['action_type'] === 'lead') {
                    $leads = (int)($action['value'] ?? 0);
                } elseif (in_array($action['action_type'], ['purchase', 'complete_registration'])) {
                    $conversions += (int)($action['value'] ?? 0);
                }
            }
        }

        $cpc = $clicks > 0 ? $spend / $clicks : 0;
        $conversionRate = $clicks > 0 ? ($conversions / $clicks) * 100 : 0;
        $costPerConversion = $conversions > 0 ? $spend / $conversions : 0;

        return [
            'platform' => $platform,
            'followers' => 0,
            'impressions' => $impressions,
            'reach' => $reach,
            'engagement' => 0,
            'clicks' => $clicks,
            'engagement_rate' => $impressions > 0 ? ($clicks / $impressions) * 100 : 0,
            'awareness' => $impressions,
            'leads' => $leads,
            'conversions' => $conversions,
            'retention_count' => 0,
            'cost_per_click' => round($cpc, 2),
            'cost_per_conversion' => round($costPerConversion, 2),
            'ad_spend' => round($spend, 2),
            'conversion_rate' => round($conversionRate, 2),
            'customer_lifetime_value' => 0,
            'customer_acquisition_cost' => round($costPerConversion, 2),
            'clv_cac_ratio' => 0,
            'roi' => $spend > 0 ? round((($conversions * 100) - $spend) / $spend * 100, 2) : 0,
        ];
    }

    /**
     * Calculate engagement rate
     */
    protected function calculateEngagementRate(int $engagement, int $followers): float
    {
        if ($followers == 0) {
            return 0;
        }

        return round(($engagement / $followers) * 100, 2);
    }

    /**
     * Refresh access token
     */
    public function refreshAccessToken(SocialMediaConnection $connection): void
    {
        try {
            $response = Http::get($this->baseUrl . '/oauth/access_token', [
                'grant_type' => 'fb_exchange_token',
                'client_id' => config('services.facebook.client_id'),
                'client_secret' => config('services.facebook.client_secret'),
                'fb_exchange_token' => $connection->access_token,
            ]);

            if ($response->successful()) {
                $data = $response->json();

                $connection->update([
                    'access_token' => $data['access_token'],
                    'token_expires_at' => now()->addSeconds($data['expires_in'] ?? 5184000), // Default 60 days
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to refresh Meta token', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Check and refresh token if needed
     */
    protected function checkAndRefreshToken(SocialMediaConnection $connection): void
    {
        if ($connection->isTokenExpired()) {
            $this->refreshAccessToken($connection);
        }
    }
}
