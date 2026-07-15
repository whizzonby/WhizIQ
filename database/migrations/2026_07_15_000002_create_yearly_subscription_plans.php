<?php

use App\Models\Currency;
use App\Models\Interval;
use App\Models\Plan;
use App\Models\PlanPrice;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $yearlyInterval = Interval::where('slug', 'year')->first();
        $monthlyInterval = Interval::where('slug', 'month')->first();
        $usdCurrency = Currency::where('code', 'USD')->first();

        if (! $yearlyInterval || ! $monthlyInterval || ! $usdCurrency) {
            return;
        }

        Plan::where('interval_id', $monthlyInterval->id)
            ->with(['product', 'prices'])
            ->get()
            ->each(function (Plan $monthlyPlan) use ($yearlyInterval, $usdCurrency): void {
                $product = $monthlyPlan->product;
                $monthlyPrice = $monthlyPlan->prices
                    ->where('currency_id', $usdCurrency->id)
                    ->first();

                if (! $product || ! $monthlyPrice) {
                    return;
                }

                $yearlyPlan = Plan::updateOrCreate(
                    [
                        'product_id' => $product->id,
                        'slug' => $product->slug . '-yearly',
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
                        'type' => $monthlyPlan->type,
                    ]
                );

                PlanPrice::updateOrCreate(
                    [
                        'plan_id' => $yearlyPlan->id,
                        'currency_id' => $usdCurrency->id,
                    ],
                    [
                        'price' => (int) ceil($monthlyPrice->price * 12 * 0.8 / 100) * 100,
                    ]
                );
            });
    }

    public function down(): void
    {
        $yearlyInterval = Interval::where('slug', 'year')->first();

        if (! $yearlyInterval) {
            return;
        }

        Plan::where('interval_id', $yearlyInterval->id)
            ->whereIn('slug', ['starter-yearly', 'pro-yearly', 'premium-yearly'])
            ->get()
            ->each(fn (Plan $plan) => $plan->delete());
    }
};
