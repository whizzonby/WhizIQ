<?php

namespace App\Console\Commands;

use App\Models\BusinessMetric;
use Illuminate\Console\Command;

class CleanupZeroBusinessMetrics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:cleanup-zero-business-metrics {--dry-run : Show what would be deleted without actually deleting}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete business metric records with zero values (no financial activity)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking for business metrics with zero values...');

        // Find records with all zero values
        $query = BusinessMetric::where('revenue', 0)
            ->where('expenses', 0)
            ->where('profit', 0)
            ->where('cash_flow', 0);

        $count = $query->count();

        if ($count === 0) {
            $this->info('No zero-value records found. Database is clean!');
            return Command::SUCCESS;
        }

        $this->warn("Found {$count} business metric records with zero values.");

        if ($this->option('dry-run')) {
            $this->info('DRY RUN MODE - No records will be deleted.');
            $this->table(
                ['ID', 'User ID', 'Date', 'Revenue', 'Expenses', 'Profit', 'Cash Flow'],
                $query->limit(10)->get()->map(fn ($record) => [
                    $record->id,
                    $record->user_id,
                    $record->date->format('Y-m-d'),
                    $record->revenue,
                    $record->expenses,
                    $record->profit,
                    $record->cash_flow,
                ])->toArray()
            );

            if ($count > 10) {
                $this->info("Showing first 10 of {$count} records...");
            }

            $this->info("Run without --dry-run to actually delete these records.");
            return Command::SUCCESS;
        }

        if (!$this->confirm("Delete {$count} zero-value business metric records?", true)) {
            $this->info('Cancelled. No records were deleted.');
            return Command::SUCCESS;
        }

        $deleted = $query->delete();

        $this->info("Successfully deleted {$deleted} zero-value business metric records.");
        $this->info('Database cleanup complete!');

        return Command::SUCCESS;
    }
}
