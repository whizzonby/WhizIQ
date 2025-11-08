<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class RunSeedersInOrder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:seed-ordered 
                            {--step : Run seeders one at a time, waiting for confirmation}
                            {--skip= : Comma-separated list of seeders to skip}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run database seeders in the correct order (after fresh migration)';

    /**
     * Seeders to run in order (dependencies first)
     */
    protected array $seeders = [
        'IntervalsSeeder' => 'Creates day, week, month, year intervals',
        'CurrenciesSeeder' => 'Creates currencies (USD, etc.)',
        'SubscriptionPlansSeeder' => 'Creates products and monthly plans',
        'YearlyPlansSeeder' => 'Creates yearly plans for all monthly plans',
    ];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🌱 Database Seeder Runner');
        $this->info('═══════════════════════════════════════════════════════════');
        $this->info('');

        // Check if fresh migration was run
        if (!$this->confirm('Have you run "php artisan migrate:fresh" already?', true)) {
            $this->warn('⚠️  Please run "php artisan migrate:fresh" first!');
            return 1;
        }

        $this->info('');
        $this->info('📋 Seeders to run (in order):');
        $this->info('');

        $skipList = $this->option('skip') ? explode(',', $this->option('skip')) : [];
        $skipList = array_map('trim', $skipList);

        $seedersToRun = [];
        foreach ($this->seeders as $seeder => $description) {
            if (in_array($seeder, $skipList)) {
                $this->line("   ⏭️  {$seeder} - {$description} (SKIPPED)");
            } else {
                $this->line("   ✅ {$seeder} - {$description}");
                $seedersToRun[] = $seeder;
            }
        }

        if (empty($seedersToRun)) {
            $this->warn('⚠️  No seeders to run!');
            return 0;
        }

        $this->info('');
        $this->info('═══════════════════════════════════════════════════════════');
        $this->info('');

        if ($this->option('step')) {
            return $this->runStepByStep($seedersToRun);
        } else {
            return $this->runAll($seedersToRun);
        }
    }

    /**
     * Run all seeders at once
     */
    protected function runAll(array $seeders): int
    {
        if (!$this->confirm('Run all seeders now?', true)) {
            $this->info('Cancelled.');
            return 0;
        }

        $this->info('');
        $this->info('🚀 Running seeders...');
        $this->info('');

        $successCount = 0;
        $failCount = 0;

        foreach ($seeders as $index => $seeder) {
            $this->info("[" . ($index + 1) . "/" . count($seeders) . "] Running {$seeder}...");
            
            try {
                $exitCode = Artisan::call('db:seed', [
                    '--class' => $seeder,
                ]);

                if ($exitCode === 0) {
                    $this->info("   ✅ {$seeder} completed successfully");
                    $successCount++;
                } else {
                    $this->error("   ❌ {$seeder} failed (exit code: {$exitCode})");
                    $failCount++;
                    
                    if (!$this->confirm("   Continue with next seeder?", true)) {
                        break;
                    }
                }
            } catch (\Exception $e) {
                $this->error("   ❌ {$seeder} failed: {$e->getMessage()}");
                $failCount++;
                
                if (!$this->confirm("   Continue with next seeder?", true)) {
                    break;
                }
            }

            $this->info('');
        }

        $this->info('═══════════════════════════════════════════════════════════');
        $this->info('📊 Summary:');
        $this->info("   ✅ Successful: {$successCount}");
        $this->info("   ❌ Failed: {$failCount}");
        $this->info('═══════════════════════════════════════════════════════════');

        return $failCount > 0 ? 1 : 0;
    }

    /**
     * Run seeders one by one with confirmation
     */
    protected function runStepByStep(array $seeders): int
    {
        $this->info('Running seeders step by step...');
        $this->info('');

        $successCount = 0;
        $failCount = 0;

        foreach ($seeders as $index => $seeder) {
            $this->info("[" . ($index + 1) . "/" . count($seeders) . "] Next: {$seeder}");
            $this->info("   {$this->seeders[$seeder]}");
            $this->info('');

            if (!$this->confirm("Run {$seeder} now?", true)) {
                $this->warn("   ⏭️  Skipped {$seeder}");
                $this->info('');
                continue;
            }

            $this->info("   🚀 Running {$seeder}...");

            try {
                $exitCode = Artisan::call('db:seed', [
                    '--class' => $seeder,
                ]);

                // Display output from seeder
                $output = Artisan::output();
                if (!empty(trim($output))) {
                    $this->line($output);
                }

                if ($exitCode === 0) {
                    $this->info("   ✅ {$seeder} completed successfully");
                    $successCount++;
                } else {
                    $this->error("   ❌ {$seeder} failed (exit code: {$exitCode})");
                    $failCount++;
                    
                    if (!$this->confirm("   Continue with next seeder?", true)) {
                        break;
                    }
                }
            } catch (\Exception $e) {
                $this->error("   ❌ {$seeder} failed: {$e->getMessage()}");
                $this->error("   Stack trace: " . $e->getTraceAsString());
                $failCount++;
                
                if (!$this->confirm("   Continue with next seeder?", true)) {
                    break;
                }
            }

            $this->info('');
        }

        $this->info('═══════════════════════════════════════════════════════════');
        $this->info('📊 Summary:');
        $this->info("   ✅ Successful: {$successCount}");
        $this->info("   ❌ Failed: {$failCount}");
        $this->info('═══════════════════════════════════════════════════════════');

        return $failCount > 0 ? 1 : 0;
    }
}


