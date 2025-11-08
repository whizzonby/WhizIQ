<?php

namespace App\Console\Commands;

use App\Models\PlanPrice;
use App\Services\CurrencyService;
use Illuminate\Console\Command;

class FixPlanPriceCurrencies extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'plans:fix-currency-ids';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix plan price currency IDs after database import (fixes currency ID mismatches)';

    public function __construct(
        private CurrencyService $currencyService
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🔧 Fixing Plan Price Currency IDs...');
        $this->info('');

        try {
            $currency = $this->currencyService->getCurrency();
            $this->info("Target Currency: {$currency->code} (ID: {$currency->id})");
            $this->info('');

            // Find all plan prices with wrong currency
            $wrongPrices = PlanPrice::where('currency_id', '!=', $currency->id)
                ->with(['plan', 'currency'])
                ->get();

            if ($wrongPrices->isEmpty()) {
                $this->info('✅ All plan prices already use the correct currency!');
                return 0;
            }

            $this->warn("Found {$wrongPrices->count()} plan price(s) with incorrect currency:");
            $this->info('');

            $updated = 0;
            $skipped = 0;

            foreach ($wrongPrices as $price) {
                $planName = $price->plan->name ?? "Plan ID {$price->plan_id}";
                $oldCurrency = $price->currency->code ?? "ID {$price->currency_id}";
                
                $this->line("  • {$planName}: Currency {$oldCurrency} → {$currency->code}");

                // Check if a price already exists for this plan with correct currency
                $existing = PlanPrice::where('plan_id', $price->plan_id)
                    ->where('currency_id', $currency->id)
                    ->first();

                if ($existing) {
                    $this->warn("    ⚠️  Plan already has price for {$currency->code}, skipping...");
                    $skipped++;
                } else {
                    // Update to correct currency
                    $price->currency_id = $currency->id;
                    $price->save();
                    $this->info("    ✅ Updated");
                    $updated++;
                }
            }

            $this->info('');
            $this->info('═══════════════════════════════════════════════════════════');
            $this->info('📊 Summary:');
            $this->info("   • Updated: {$updated}");
            $this->info("   • Skipped: {$skipped}");
            $this->info('═══════════════════════════════════════════════════════════');
            $this->info('');

            if ($updated > 0) {
                $this->info('✅ Currency IDs fixed! Prices should now display correctly.');
            }

            return 0;

        } catch (\Exception $e) {
            $this->error("❌ Error: {$e->getMessage()}");
            return 1;
        }
    }
}


