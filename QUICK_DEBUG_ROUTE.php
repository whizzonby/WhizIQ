<?php

/**
 * TEMPORARY DEBUG ROUTE
 * 
 * Add this to routes/web.php temporarily to debug plans without loading full page
 * REMOVE AFTER DEBUGGING!
 * 
 * Usage: Visit https://your-site.com/debug-plans-simple
 */

Route::get('/debug-plans-simple', function() {
    try {
        $planService = app(\App\Services\PlanService::class);
        $currencyService = app(\App\Services\CurrencyService::class);
        
        // Get currency
        $currency = $currencyService->getCurrency();
        $result = [
            'currency' => [
                'id' => $currency->id,
                'code' => $currency->code,
                'name' => $currency->name,
            ],
            'plans' => [],
        ];
        
        // Get all active plans directly (bypass service to avoid filtering)
        $plans = \App\Models\Plan::where('is_active', true)
            ->where('is_visible', true)
            ->with(['product', 'interval', 'prices.currency'])
            ->get();
        
        foreach ($plans as $plan) {
            $planData = [
                'id' => $plan->id,
                'name' => $plan->name,
                'slug' => $plan->slug,
                'product' => $plan->product->name ?? 'N/A',
                'interval' => $plan->interval->name ?? 'N/A',
                'is_active' => $plan->is_active,
                'is_visible' => $plan->is_visible,
                'prices' => [],
            ];
            
            // Check all prices
            foreach ($plan->prices as $price) {
                $planData['prices'][] = [
                    'id' => $price->id,
                    'price' => $price->price / 100,
                    'currency_id' => $price->currency_id,
                    'currency_code' => $price->currency->code ?? 'N/A',
                    'matches_default' => $price->currency_id === $currency->id,
                ];
            }
            
            // Try getPlanPrice
            try {
                $planPrice = $planService->getPlanPrice($plan);
                $planData['getPlanPrice_result'] = $planPrice ? [
                    'found' => true,
                    'price' => $planPrice->price / 100,
                    'currency' => $planPrice->currency->code ?? 'N/A',
                ] : [
                    'found' => false,
                    'reason' => 'No price found for default currency',
                ];
            } catch (\Exception $e) {
                $planData['getPlanPrice_result'] = [
                    'error' => $e->getMessage(),
                ];
            }
            
            $result['plans'][] = $planData;
        }
        
        return response()->json($result, 200, [], JSON_PRETTY_PRINT);
        
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => explode("\n", $e->getTraceAsString()),
        ], 500);
    }
})->middleware('auth'); // IMPORTANT: Protect this route!


