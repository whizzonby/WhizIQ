<?php

namespace App\Http\Controllers;

use App\Models\CampaignAudience;
use App\Models\EmailCampaign;
use App\Services\CampaignAudienceService;
use App\Services\OpenAIService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CampaignDigestActionController extends Controller
{
    public function reviewDraft(Request $request, EmailCampaign $campaign): RedirectResponse
    {
        $this->authorizeCampaign($request, $campaign);

        return redirect()->route('filament.dashboard.pages.email-composer-page', [
            'draft' => $campaign->id,
        ]);
    }

    public function improveWeakSegment(Request $request, EmailCampaign $campaign, string $segment, OpenAIService $openAIService): RedirectResponse
    {
        $this->authorizeCampaign($request, $campaign);

        $variants = $campaign->segment_variants ?? [];

        abort_unless(isset($variants[$segment]), 404);

        $variant = $variants[$segment];
        $rewrite = $openAIService->rewriteCampaignDraft(
            $variant['subject'] ?? $campaign->subject,
            $variant['body'] ?? $campaign->body,
            [
                'tone' => 'direct and reassuring',
                'offer' => 'make booking feel easy',
                'campaign_preset' => $this->campaignAudience($campaign)?->campaign_preset ?? 'campaign',
                'extra_instruction' => 'This is the weakest-performing segment from the weekly digest. Improve clarity, urgency, and the call to action for this segment only.',
            ]
        );

        if ($rewrite) {
            $variants[$segment]['subject'] = $rewrite['subject'];
            $variants[$segment]['body'] = $rewrite['body'];
            $variants[$segment]['improved_at'] = now()->toIso8601String();
            $variants[$segment]['improved_from'] = 'weekly_marketing_digest';

            $campaign->forceFill([
                'segment_variants' => $variants,
            ])->save();

            $this->recordSegmentImprovement($campaign, $segment);
        }

        return redirect()
            ->route('filament.dashboard.pages.email-composer-page', [
                'draft' => $campaign->id,
            ])
            ->with('status', $rewrite ? 'Weakest segment draft improved for review.' : 'Could not improve the segment automatically. Please review the draft.');
    }

    public function snoozeRecommendation(Request $request, CampaignAudience $audience, CampaignAudienceService $campaignAudienceService): RedirectResponse
    {
        abort_unless($audience->user_id === $request->user()->id, 404);

        $campaignAudienceService->snoozeRecommendation($audience, 7);

        return redirect()
            ->route('filament.dashboard.pages.campaign-report-page')
            ->with('status', 'Campaign recommendation snoozed for 7 days.');
    }

    protected function authorizeCampaign(Request $request, EmailCampaign $campaign): void
    {
        abort_unless($campaign->user_id === $request->user()->id, 404);
        abort_unless($campaign->automation_source === 'campaign_recommendation', 404);
        abort_unless($campaign->status === 'draft', 404);
    }

    protected function recordSegmentImprovement(EmailCampaign $campaign, string $segment): void
    {
        $audience = $this->campaignAudience($campaign);

        if (! $audience) {
            return;
        }

        $history = collect($audience->recommendation_history ?? [])
            ->prepend([
                'event' => 'segment_rewritten',
                'label' => 'Segment Rewritten',
                'at' => now()->toIso8601String(),
                'metadata' => [
                    'campaign_id' => $campaign->id,
                    'segment' => $segment,
                    'source' => 'weekly_marketing_digest',
                ],
            ])
            ->take(10)
            ->values()
            ->all();

        $audience->forceFill([
            'recommendation_history' => $history,
        ])->save();
    }

    protected function campaignAudience(EmailCampaign $campaign): ?CampaignAudience
    {
        if (! $campaign->campaign_audience_id) {
            return null;
        }

        return CampaignAudience::where('user_id', $campaign->user_id)
            ->find($campaign->campaign_audience_id);
    }
}
