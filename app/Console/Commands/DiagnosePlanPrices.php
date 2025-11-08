<?php

namespace App\Console\Commands;

use App\Models\Currency;
use App\Models\Plan;
use App\Models\PlanPrice;
use App\Services\CurrencyService;
use App\Services\PlanService;
use Illuminate\Console\Command;

class DiagnosePlanPrices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'plans:diagnose-prices';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Diagnose why plan prices are not showing';

    public function __construct(
        private CurrencyService $currencyService,
        private PlanService $planService
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🔍 Diagnosing Plan Prices Issue...');
        $this->info('');

        // Check 1: Default currency config
        $defaultCurrencyCode = config('app.default_currency');
        $this->info("1️⃣  Default Currency Config: {$defaultCurrencyCode}");

        // Check 2: Currency exists in database
        $currency = Currency::where('code', $defaultCurrencyCode)->first();
        if (!$currency) {
            $this->error("   ❌ Currency '{$defaultCurrencyCode}' NOT FOUND in database!");
            $this->warn("   💡 Solution: Run CurrenciesSeeder");
            return 1;
        }
        $this->info("   ✅ Currency found: {$currency->name} (ID: {$currency->id})");

        // Check 3: CurrencyService can get currency
        try {
            $serviceCurrency = $this->currencyService->getCurrency();
            $this->info("   ✅ CurrencyService returns: {$serviceCurrency->code} (ID: {$serviceCurrency->id})");
        } catch (\Exception $e) {
            $this->error("   ❌ CurrencyService error: {$e->getMessage()}");
            return 1;
        }

        // Check 4: Plans exist
        $plans = Plan::where('is_active', true)->get();
        $this->info("");
        $this->info("2️⃣  Active Plans: {$plans->count()}");

        if ($plans->isEmpty()) {
            $this->warn("   ⚠️  No active plans found!");
            return 0;
        }

        // Check 5: Plan prices exist
        $this->info("");
        $this->info("3️⃣  Plan Prices Analysis:");
        $this->info("");

        $plansWithPrices = 0;
        $plansWithoutPrices = 0;
        $plansWithWrongCurrency = 0;

        foreach ($plans as $plan) {
            $prices = PlanPrice::where('plan_id', $plan->id)->get();
            
            if ($prices->isEmpty()) {
                $this->error("   ❌ {$plan->name} (slug: {$plan->slug}) - NO PRICES");
                $plansWithoutPrices++;
                continue;
            }

            $currencyPrice = $prices->where('currency_id', $currency->id)->first();
            
            if (!$currencyPrice) {
                $this->warn("   ⚠️  {$plan->name} (slug: {$plan->slug}) - No price for currency '{$currency->code}'");
                $this->info("      Available currencies: " . $prices->pluck('currency.code')->join(', '));
                $plansWithWrongCurrency++;
                continue;
            }

            $this->info("   ✅ {$plan->name} (slug: {$plan->slug}) - Price: $" . number_format($currencyPrice->price / 100, 2) . " ({$currency->code})");
            $plansWithPrices++;

            // Check if getPlanPrice works
            $retrievedPrice = $this->planService->getPlanPrice($plan);
            if (!$retrievedPrice) {
                $this->error("      ⚠️  PlanService::getPlanPrice() returns NULL for this plan!");
            } else {
                $this->info("      ✅ PlanService::getPlanPrice() works correctly");
            }
        }

        // Summary
        $this->info("");
        $this->info("═══════════════════════════════════════════════════════════");
        $this->info("📊 Summary:");
        $this->info("   • Plans with correct prices: {$plansWithPrices}");
        $this->info("   • Plans without prices: {$plansWithoutPrices}");
        $this->info("   • Plans with wrong currency: {$plansWithWrongCurrency}");
        $this->info("");

        if ($plansWithoutPrices > 0) {
            $this->error("❌ ISSUE: Some plans have no prices!");
            $this->warn("   💡 Solution: Run SubscriptionPlansSeeder or YearlyPlansSeeder");
        }

        if ($plansWithWrongCurrency > 0) {
            $this->error("❌ ISSUE: Some plans don't have prices for currency '{$currency->code}'!");
            $this->warn("   💡 Solution: Create prices for currency '{$currency->code}' or change default_currency in config");
        }

        if ($plansWithPrices === $plans->count() && $plansWithPrices > 0) {
            $this->info("✅ All plans have prices for currency '{$currency->code}'!");
        }

        $this->info("═══════════════════════════════════════════════════════════");

        return 0;
    }
}


