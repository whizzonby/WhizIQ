<?php

namespace Database\Seeders;

use App\Models\Currency;
use App\Models\Interval;
use App\Models\Plan;
use App\Models\PlanPrice;
use App\Models\Product;
use Illuminate\Database\Seeder;

class YearlyPlansSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * This seeder creates yearly plans for all existing monthly plans
     * with reasonable pricing (10 months worth = 17% discount).
     */
    public function run(): void
    {
        $this->command->info('🚀 Starting Yearly Plans Seeder...');
        $this->command->info('');

        // Get the yearly interval
        $yearlyInterval = Interval::where('slug', 'year')->first();
        if (!$yearlyInterval) {
            $this->command->error('❌ Yearly interval not found. Run IntervalsSeeder first.');
            return;
        }

        // Get USD currency
        $usdCurrency = Currency::where('code', 'USD')->first();
        if (!$usdCurrency) {
            $this->command->error('❌ USD currency not found. Run CurrenciesSeeder first.');
            return;
        }

        // Get all monthly plans
        $monthlyInterval = Interval::where('slug', 'month')->first();
        if (!$monthlyInterval) {
            $this->command->error('❌ Monthly interval not found. Run IntervalsSeeder first.');
            return;
        }

        $monthlyPlans = Plan::where('interval_id', $monthlyInterval->id)
            ->with('product', 'prices')
            ->get();

        if ($monthlyPlans->isEmpty()) {
            $this->command->warn('⚠️  No monthly plans found. Run SubscriptionPlansSeeder first.');
            return;
        }

        $this->command->info("📦 Found {$monthlyPlans->count()} monthly plan(s) to create yearly versions for...");
        $this->command->info('');

        $createdCount = 0;
        $skippedCount = 0;

        foreach ($monthlyPlans as $monthlyPlan) {
            $product = $monthlyPlan->product;
            
            if (!$product) {
                $this->command->warn("⚠️  Skipping plan '{$monthlyPlan->name}' - no product found");
                $skippedCount++;
                continue;
            }

            // Check if yearly plan already exists
            $yearlySlug = $product->slug . '-yearly';
            $existingYearlyPlan = Plan::where('product_id', $product->id)
                ->where('slug', $yearlySlug)
                ->first();

            if ($existingYearlyPlan) {
                $this->command->info("⏭️  Skipping {$product->name} - yearly plan already exists");
                $skippedCount++;
                continue;
            }

            // Get monthly price
            $monthlyPrice = $monthlyPlan->prices()
                ->where('currency_id', $usdCurrency->id)
                ->first();

            if (!$monthlyPrice) {
                $this->command->warn("⚠️  Skipping {$product->name} - no USD price found for monthly plan");
                $skippedCount++;
                continue;
            }

            // Calculate yearly price (10 months worth = 17% discount)
            // This is a common SaaS pricing strategy
            $monthlyPriceCents = $monthlyPrice->price;
            $yearlyPriceCents = $monthlyPriceCents * 10; // 10 months = save 2 months

            $this->command->info("📦 Creating yearly plan for: {$product->name}");
            $this->command->info("   Monthly: $" . number_format($monthlyPriceCents / 100, 2) . "/month");
            $this->command->info("   Yearly: $" . number_format($yearlyPriceCents / 100, 2) . "/year (Save $" . number_format(($monthlyPriceCents * 2) / 100, 2) . "/year)");

            // Create or update yearly plan (safe for re-running)
            $yearlyPlan = Plan::updateOrCreate(
                [
                    'product_id' => $product->id,
                    'slug' => $yearlySlug,
                ],
                [
                    'name' => $product->name . ' - Yearly',
                    'description' => $monthlyPlan->description,
                    'interval_id' => $yearlyInterval->id,
                    'interval_count' => 1,
                    'has_trial' => $monthlyPlan->has_trial,
                    'trial_interval_id' => $monthlyPlan->trial_interval_id,
                    'trial_interval_count' => $monthlyPlan->trial_interval_count,
                    'is_active' => true,
                    'is_visible' => true,
                    'type' => $monthlyPlan->type ?? 'flat_rate',
                ]
            );

            $this->command->info("   ✓ Plan created/updated: {$yearlyPlan->name}");

            // Create or update plan price (safe for re-running)
            PlanPrice::updateOrCreate(
                [
                    'plan_id' => $yearlyPlan->id,
                    'currency_id' => $usdCurrency->id,
                ],
                [
                    'price' => $yearlyPriceCents,
                ]
            );

            $this->command->info("   ✓ Price set: $" . number_format($yearlyPriceCents / 100, 2) . "/year");
            $this->command->info("   ✅ {$product->name} yearly plan complete!");
            $this->command->info('');

            $createdCount++;
        }

        // Display summary
        $this->displaySummary($createdCount, $skippedCount);
    }

    /**
     * Display a summary of what was created
     */
    private function displaySummary(int $createdCount, int $skippedCount): void
    {
        $this->command->info('');
        $this->command->info('═══════════════════════════════════════════════════════════');
        $this->command->info('✨ YEARLY PLANS SEEDER COMPLETED! ✨');
        $this->command->info('═══════════════════════════════════════════════════════════');
        $this->command->info('');
        $this->command->info('📊 Summary:');
        $this->command->info("   • Yearly Plans Created: {$createdCount}");
        $this->command->info("   • Plans Skipped: {$skippedCount}");
        $this->command->info('');
        $this->command->info('💰 Pricing Strategy:');
        $this->command->info('   • Yearly plans are priced at 10 months worth');
        $this->command->info('   • Customers save 2 months (17% discount)');
        $this->command->info('   • Same metadata as monthly plans (shared product)');
        $this->command->info('');
        $this->command->info('🎯 Next Steps:');
        $this->command->info('   1. Verify yearly plans in admin panel');
        $this->command->info('   2. Configure payment provider settings for yearly plans');
        $this->command->info('   3. Test subscription flow with yearly plans');
        $this->command->info('   4. Update marketing materials to highlight savings');
        $this->command->info('');
        $this->command->info('═══════════════════════════════════════════════════════════');
    }
}

