<?php

namespace App\Console\Commands;

use App\Models\Deal;
use App\Models\NotificationPreference;
use App\Models\User;
use App\Services\MessagingService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendStaleDealAlerts extends Command
{
    protected $signature = 'whiziq:stale-deal-alerts {--days=14 : Days without movement to consider a deal stale}';

    protected $description = 'Alert business owners about deals that have not moved in 14+ days';

    public function handle(MessagingService $messaging)
    {
        $days = (int) $this->option('days');
        $this->info("Checking for stale deals (no movement in {$days}+ days)...");

        // Group stale deals by user
        $staleByUser = Deal::open()
            ->where('updated_at', '<', now()->subDays($days))
            ->get()
            ->groupBy('user_id');

        if ($staleByUser->isEmpty()) {
            $this->info('No stale deals found.');
            return 0;
        }

        $sent = 0;

        foreach ($staleByUser as $userId => $deals) {
            $prefs = NotificationPreference::forUser($userId);

            if (! $prefs->alert_stale_deals) {
                continue;
            }

            $user = User::find($userId);
            if (! $user) {
                continue;
            }

            try {
                $messaging->sendStaleDealAlert($user, $deals->count());
                $sent++;

                $this->line("✓ Alert sent to {$user->name} — {$deals->count()} stale deal(s)");
                Log::info("Stale deal alert sent to user #{$userId}: {$deals->count()} deals");
            } catch (\Exception $e) {
                $this->error("✗ Failed for user {$user->name}: {$e->getMessage()}");
                Log::error("Stale deal alert failed for user #{$userId}: {$e->getMessage()}");
            }
        }

        $this->info("Stale deal alerts sent: {$sent}");
        return 0;
    }
}
