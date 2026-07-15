<?php

namespace App\View\Components\Filament\Plans;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;

class All extends \App\View\Components\Plans\All
{
    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.filament.plans.all', $this->calculateViewData());
    }

    protected function calculateViewData()
    {
        $subscription = null;
        if ($this->currentSubscriptionUuid !== null) {
            $subscription = $this->subscriptionService->findActiveByUserAndSubscriptionUuid(auth()->user()->id, $this->currentSubscriptionUuid);
        }

        $planType = null;
        if ($subscription !== null) {
            $planType = $subscription->plan->type;
        }

        $plans = $this->planService->getAllPlansWithPrices(
            $this->products,
            $planType,
            onlyVisible: true,
        );

        $viewData['subscription'] = $subscription;
        $viewData['planProductRanks'] = $this->buildPlanProductRanks($plans);

        return $this->enrichViewData($viewData, $plans);
    }

    private function buildPlanProductRanks(Collection $plans): array
    {
        $intervalWeeks = [
            'day' => 1 / 7,
            'week' => 1,
            'month' => 4,
            'year' => 48,
        ];

        return $plans
            ->groupBy('product_id')
            ->map(function (Collection $productPlans) use ($intervalWeeks): float {
                return $productPlans
                    ->map(function ($plan) use ($intervalWeeks): float {
                        $price = $plan->prices->first()?->price ?? PHP_INT_MAX;
                        $weeks = $intervalWeeks[$plan->interval?->name] ?? 1;

                        return $price / max($weeks, 0.01);
                    })
                    ->min();
            })
            ->sort()
            ->keys()
            ->values()
            ->flip()
            ->all();
    }
}
