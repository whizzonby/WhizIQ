<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\BusinessMetricAggregationService;
use Illuminate\Console\Command;
use Carbon\Carbon;

class RefreshBusinessMetrics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:refresh-business-metrics
                            {--user= : Specific user ID to refresh (optional)}
                            {--days=30 : Number of days to recalculate (default: 30)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh business metrics dashboard data (revenue, expenses, profit, cash flow)';

    /**
     * Execute the console command.
     */
    public function handle(BusinessMetricAggregationService $service)
    {
        $this->info('🔄 Refreshing Business Metrics...');
        $this->newLine();

        $days = (int) $this->option('days');
        $startDate = Carbon::today()->subDays($days);
        $endDate = Carbon::today();

        // If specific user ID provided
        if ($userId = $this->option('user')) {
            $user = User::find($userId);

            if (!$user) {
                $this->error("❌ User ID {$userId} not found!");
                return 1;
            }

            $this->info("👤 Refreshing metrics for: {$user->name} (ID: {$userId})");
            $this->info("📅 Date range: {$startDate->toDateString()} to {$endDate->toDateString()}");
            $this->newLine();

            $result = $service->aggregateMetrics($userId, $startDate, $endDate);

            $this->displayResults($result, $user->name);

            // Also refresh cash flow history
            $this->info('💰 Refreshing cash flow history...');
            $cashFlowResult = $service->generateCashFlowHistory($userId);
            $this->info("   ✅ Created: {$cashFlowResult['created']}, Updated: {$cashFlowResult['updated']}");

            return 0;
        }

        // Refresh for all users with financial data
        $this->info('👥 Refreshing metrics for all users with financial data...');
        $this->info("📅 Date range: {$startDate->toDateString()} to {$endDate->toDateString()}");
        $this->newLine();

        $result = $service->aggregateAllUsers($startDate, $endDate);

        if ($result['success']) {
            $this->info("✅ Successfully processed {$result['users_processed']} users");
            $this->newLine();

            foreach ($result['results'] as $userId => $userResult) {
                $user = User::find($userId);
                $this->line("  • {$user->name}: Created {$userResult['created']}, Updated {$userResult['updated']}");
            }
        } else {
            $this->error('❌ Aggregation failed!');
            return 1;
        }

        $this->newLine();
        $this->info('✨ Business metrics refresh complete!');
        $this->info('💡 Tip: Dashboard widgets are cached for 1 hour. Clear cache with: php artisan cache:clear');

        return 0;
    }

    private function displayResults(array $result, string $userName): void
    {
        if ($result['success']) {
            $this->info("✅ Metrics refreshed for {$userName}");
            $this->info("   📊 Created: {$result['created']} records");
            $this->info("   🔄 Updated: {$result['updated']} records");

            if (!empty($result['errors'])) {
                $this->warn("⚠️  Some errors occurred:");
                foreach ($result['errors'] as $error) {
                    $this->line("   • {$error}");
                }
            }
        } else {
            $this->error("❌ Failed to refresh metrics for {$userName}");
            if (!empty($result['errors'])) {
                foreach ($result['errors'] as $error) {
                    $this->line("   • {$error}");
                }
            }
        }
    }
}
