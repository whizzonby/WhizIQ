<?php

namespace App\Console\Commands;

use App\Models\CampaignAudience;
use App\Models\EmailCampaign;
use App\Models\NotificationPreference;
use App\Models\User;
use App\Notifications\CampaignDraftPreparedNotification;
use App\Notifications\CampaignDraftRescuedNotification;
use App\Services\CampaignAudienceService;
use App\Services\MessagingService;
use Illuminate\Console\Command;

class PrepareCampaignRecommendationDraftsCommand extends Command
{
    protected $signature = 'campaigns:prepare-recommendation-drafts';

    protected $description = 'Prepare owner-approved campaign drafts when recommendation cadence windows reopen';

    public function handle(CampaignAudienceService $campaignAudienceService, MessagingService $messagingService): int
    {
        $prepared = 0;
        $rescuedDrafts = $campaignAudienceService->rescueStalePreparedDrafts();
        $expired = $campaignAudienceService->expireStaleRecommendationDrafts();

        $rescuedDrafts->each(function (EmailCampaign $draft) use ($messagingService): void {
            $this->notifyRescuedOwner($draft, $messagingService);
        });

        User::query()
            ->chunkById(100, function ($users) use ($campaignAudienceService, $messagingService, &$prepared): void {
                foreach ($users as $user) {
                    $recommendation = $campaignAudienceService->recommendationsForUser($user)['recommended'] ?? null;

                    if (! $recommendation) {
                        continue;
                    }

                    if (! empty($recommendation['prepared_campaign_draft'])) {
                        continue;
                    }

                    $audience = CampaignAudience::where('user_id', $user->id)
                        ->find($recommendation['id']);

                    if (! $audience) {
                        continue;
                    }

                    $draft = $campaignAudienceService->prepareRecommendationDraft($audience);

                    if ($draft && $draft->wasRecentlyCreated) {
                        $prepared++;
                        $this->notifyOwner($user, $draft, $messagingService);
                    }
                }
            });

        $this->info("Rescued {$rescuedDrafts->count()} stale campaign recommendation draft(s).");
        $this->info("Expired {$expired} stale campaign recommendation draft(s).");
        $this->info("Prepared {$prepared} campaign recommendation draft(s).");

        return Command::SUCCESS;
    }

    protected function notifyOwner(User $user, EmailCampaign $draft, MessagingService $messagingService): void
    {
        $preferences = NotificationPreference::forUser($user->id);

        if (! $preferences->alert_campaign_draft_prepared) {
            return;
        }

        $user->notify(new CampaignDraftPreparedNotification($draft));

        $messagingService->sendCampaignDraftPreparedAlert(
            $user,
            $draft->name,
            (int) $draft->total_recipients
        );
    }

    protected function notifyRescuedOwner(EmailCampaign $draft, MessagingService $messagingService): void
    {
        $user = $draft->user;

        if (! $user) {
            return;
        }

        $preferences = NotificationPreference::forUser($user->id);

        if (! $preferences->alert_campaign_draft_prepared) {
            return;
        }

        $user->notify(new CampaignDraftRescuedNotification($draft));

        $messagingService->sendCampaignDraftPreparedAlert(
            $user,
            $draft->name . ' was improved before expiry',
            (int) $draft->total_recipients
        );
    }
}
