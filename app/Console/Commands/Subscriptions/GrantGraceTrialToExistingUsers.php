<?php

namespace App\Console\Commands\Subscriptions;

use App\Constants\SubscriptionStatus;
use App\Models\Plan;
use App\Models\User;
use App\Notifications\GraceTrialGrantedNotification;
use App\Services\SubscriptionService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class GrantGraceTrialToExistingUsers extends Command
{
    /**
     * One-off, manually-run migration step — deliberately not scheduled.
     * Run once at deploy time after closing the default-product revenue
     * leak, so existing users who were living on that leak get a fair
     * warning window instead of an instant cutoff.
     */
    protected $signature = 'app:grant-grace-trial-to-existing-users {--days=14} {--dry-run}';

    protected $description = 'Grant a fresh local trial to existing users who currently have no active subscription, ahead of enforcing the subscription gate.';

    public function handle(SubscriptionService $subscriptionService): int
    {
        $days = (int) $this->option('days');
        $dryRun = (bool) $this->option('dry-run');

        $trialPlan = Plan::where('is_active', true)
            ->where('has_trial', true)
            ->where('trial_interval_count', '>', 0)
            ->orderBy('id')
            ->first();

        if (! $trialPlan) {
            $this->error('No active, trial-eligible plan found. Aborting.');

            return self::FAILURE;
        }

        $this->info("Using plan '{$trialPlan->slug}' for the grace trial.");

        $query = User::query()
            ->where('is_admin', false)
            ->whereDoesntHave('subscriptions', function ($q) {
                $q->where('status', SubscriptionStatus::ACTIVE->value)
                    ->where('ends_at', '>', now());
            });

        $affectedCount = (clone $query)->count();
        $this->info("{$affectedCount} user(s) currently have no active subscription.");

        if ($dryRun) {
            $this->info('Dry run — no changes made.');

            return self::SUCCESS;
        }

        $granted = 0;
        $failed = 0;

        $query->chunkById(100, function ($users) use ($subscriptionService, $trialPlan, $days, &$granted, &$failed) {
            foreach ($users as $user) {
                try {
                    $subscription = $subscriptionService->create(
                        planSlug: $trialPlan->slug,
                        userId: $user->id,
                        localSubscription: true,
                        endsAt: now()->addDays($days),
                    );

                    $user->notify(new GraceTrialGrantedNotification($subscription, $days));

                    $granted++;
                } catch (\Throwable $e) {
                    $failed++;
                    Log::error('Failed to grant grace trial to existing user', [
                        'user_id' => $user->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        });

        $this->info("Granted grace trials to {$granted} user(s). {$failed} failure(s) — see logs.");

        return self::SUCCESS;
    }
}
