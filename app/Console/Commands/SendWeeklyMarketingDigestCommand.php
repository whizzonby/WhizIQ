<?php

namespace App\Console\Commands;

use App\Models\EmailCampaign;
use App\Models\NotificationPreference;
use App\Models\User;
use App\Notifications\WeeklyMarketingDigestNotification;
use App\Services\CampaignAudienceService;
use App\Services\MessagingService;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;

class SendWeeklyMarketingDigestCommand extends Command
{
    protected $signature = 'campaigns:weekly-marketing-digest {--user= : Send the digest for one user id}';

    protected $description = 'Send owners a weekly marketing digest with recommendations, drafts, segment learning, and campaign impact';

    public function handle(CampaignAudienceService $campaignAudienceService, MessagingService $messagingService): int
    {
        $this->info('Sending weekly marketing digests...');

        $preferences = NotificationPreference::where('digest_enabled', true)
            ->when($this->option('user'), fn ($query, string $userId) => $query->where('user_id', $userId))
            ->get();

        $sent = 0;

        foreach ($preferences as $preference) {
            $user = User::find($preference->user_id);

            if (! $user) {
                continue;
            }

            try {
                $digest = $this->buildDigest($user, $campaignAudienceService);

                $user->notify(new WeeklyMarketingDigestNotification($digest));

                if ($preference->hasOwnerPhone()) {
                    $messagingService->notifyOwner($user, $this->buildOwnerMessage($digest));
                }

                $sent++;
                $this->line("Sent weekly marketing digest to user #{$user->id}");
            } catch (\Throwable $exception) {
                $this->error("Failed weekly marketing digest for user #{$user->id}: {$exception->getMessage()}");

                Log::error('Weekly marketing digest failed', [
                    'user_id' => $user->id,
                    'error' => $exception->getMessage(),
                ]);
            }
        }

        $this->info("Weekly marketing digests sent: {$sent}");

        return Command::SUCCESS;
    }

    protected function buildDigest(User $user, CampaignAudienceService $campaignAudienceService): array
    {
        $start = now()->subDays(7)->startOfDay();
        $end = now()->endOfDay();
        $recommendations = $campaignAudienceService->recommendationsForUser(
            $user,
            $start->toDateString(),
            $end->toDateString()
        );
        $audiences = collect($recommendations['audiences'] ?? []);
        $drafts = $this->preparedDrafts($user);
        $recentCampaigns = $this->recentCampaigns($user, $start, $end);
        $strongestSegments = $this->segmentSignals($audiences, 'strongest');
        $weakestSegments = $this->segmentSignals($audiences, 'weakest');
        $bookings = (int) $audiences->sum('booked');
        $projected = (float) $audiences->sum('projected');
        $collected = (float) $audiences->sum('collected');
        $recommended = $recommendations['recommended'] ?? null;
        $actions = $this->actionUrls($drafts, $weakestSegments, $recommended);

        return [
            'period_start' => $start->toDateString(),
            'period_end' => $end->toDateString(),
            'recommended' => $recommended,
            'drafts' => $drafts->map(fn (EmailCampaign $draft): array => [
                'id' => $draft->id,
                'name' => $draft->name,
                'total_recipients' => (int) $draft->total_recipients,
                'prepared_at' => $draft->prepared_at?->toDateString(),
            ])->all(),
            'draft_count' => $drafts->count(),
            'sent_campaign_count' => $recentCampaigns->count(),
            'emails_sent' => (int) $recentCampaigns->sum('emails_sent'),
            'bookings' => $bookings,
            'projected' => $projected,
            'collected' => $collected,
            'formatted_projected' => $this->formatMoney($projected),
            'formatted_collected' => $this->formatMoney($collected),
            'strongest_segments' => $strongestSegments,
            'weakest_segments' => $weakestSegments,
            'summary' => $this->summaryLine($recommended, $drafts->count(), $bookings, $collected),
            'report_url' => route('filament.dashboard.pages.campaign-report-page'),
            'actions' => $actions,
        ];
    }

    protected function preparedDrafts(User $user): Collection
    {
        return EmailCampaign::where('user_id', $user->id)
            ->where('automation_source', 'campaign_recommendation')
            ->activePreparedDraft()
            ->latest('prepared_at')
            ->limit(5)
            ->get();
    }

    protected function recentCampaigns(User $user, $start, $end): Collection
    {
        return EmailCampaign::where('user_id', $user->id)
            ->where('status', 'sent')
            ->whereBetween('sent_at', [$start, $end])
            ->get(['id', 'name', 'emails_sent', 'sent_at']);
    }

    protected function segmentSignals(Collection $audiences, string $type): array
    {
        $labelKey = $type === 'strongest' ? 'strongest_label' : 'weakest_label';
        $segmentKey = $type === 'strongest' ? 'strongest_segment' : 'weakest_segment';

        return $audiences
            ->filter(fn (array $audience): bool => ! empty($audience['segment_learning'][$labelKey]))
            ->map(fn (array $audience): array => [
                'audience_id' => $audience['id'],
                'audience' => $audience['name'],
                'segment' => $audience['segment_learning'][$segmentKey],
                'label' => $audience['segment_learning'][$labelKey],
            ])
            ->values()
            ->all();
    }

    protected function actionUrls(Collection $drafts, array $weakestSegments, ?array $recommended): array
    {
        $reviewDraft = $drafts->first();
        $weakestSegment = collect($weakestSegments)->first();
        $weakestDraft = $weakestSegment
            ? $drafts->first(fn (EmailCampaign $draft): bool => $draft->campaign_audience_id === $weakestSegment['audience_id']
                && isset(($draft->segment_variants ?? [])[$weakestSegment['segment']]))
            : null;

        return [
            'review_draft_url' => $reviewDraft ? URL::signedRoute('campaign-digest.draft.review', [
                'campaign' => $reviewDraft->id,
            ]) : null,
            'improve_weakest_segment_url' => $weakestDraft ? URL::signedRoute('campaign-digest.segment.improve', [
                'campaign' => $weakestDraft->id,
                'segment' => $weakestSegment['segment'],
            ]) : null,
            'snooze_recommendation_url' => $recommended ? URL::signedRoute('campaign-digest.audience.snooze', [
                'audience' => $recommended['id'],
            ]) : null,
        ];
    }

    protected function buildOwnerMessage(array $digest): string
    {
        $lines = ['WhizIQ weekly marketing digest'];

        if ($digest['recommended']) {
            $lines[] = 'Next campaign: ' . $digest['recommended']['name'];
        }

        if ($digest['draft_count'] > 0) {
            $lines[] = 'Drafts ready: ' . $digest['draft_count'];
        }

        $lines[] = 'Impact: ' . $digest['bookings'] . ' booking(s), ' . $digest['formatted_collected'] . ' collected';

        if (! empty($digest['strongest_segments'])) {
            $segment = $digest['strongest_segments'][0];
            $lines[] = 'Strongest segment: ' . $segment['label'] . ' in ' . $segment['audience'];
        }

        if (! empty($digest['weakest_segments'])) {
            $segment = $digest['weakest_segments'][0];
            $lines[] = 'Needs attention: ' . $segment['label'] . ' in ' . $segment['audience'];
        }

        $lines[] = $digest['actions']['review_draft_url']
            ?? $digest['actions']['improve_weakest_segment_url']
            ?? $digest['actions']['snooze_recommendation_url']
            ?? $digest['report_url'];

        return implode("\n", $lines);
    }

    protected function summaryLine(?array $recommended, int $draftCount, int $bookings, float $collected): string
    {
        if ($recommended) {
            return 'Recommended: ' . $recommended['name'] . '. ' . $draftCount . ' draft(s) ready, ' . $bookings . ' booking(s), ' . $this->formatMoney($collected) . ' collected.';
        }

        return $draftCount . ' draft(s) ready, ' . $bookings . ' booking(s), ' . $this->formatMoney($collected) . ' collected.';
    }

    protected function formatMoney(float $amount): string
    {
        return '$' . number_format($amount, 2);
    }
}
