<?php

namespace App\Filament\Dashboard\Widgets;

use App\Models\Deal;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class DealPipelineWidget extends BaseWidget
{
    protected static ?int $sort = 32;

    protected static bool $isLazy = true;


    public function getHeading(): string
    {
        return '💼 Sales Pipeline';
    }

    protected function getStats(): array
    {
        $userId = Auth::id();
        $cacheKey = "deal_pipeline_{$userId}_" . now()->format('Y-m-d-H');

        $d = Cache::remember($cacheKey, 1800, function () use ($userId) {
            $stages = ['lead', 'qualified', 'proposal', 'negotiation'];
            $result = [];

            foreach ($stages as $stage) {
                $result["{$stage}_count"] = Deal::where('user_id', $userId)->where('stage', $stage)->count();
                $result["{$stage}_value"] = Deal::where('user_id', $userId)->where('stage', $stage)->sum('value');
            }

            $result['totalWeightedValue'] = Deal::where('user_id', $userId)
                ->whereIn('stage', $stages)
                ->get()
                ->sum('weighted_value');

            $result['wonThisMonth'] = Deal::where('user_id', $userId)
                ->where('stage', 'won')
                ->whereBetween('actual_close_date', [now()->startOfMonth(), now()->endOfMonth()])
                ->sum('value');

            return $result;
        });

        $leadCount        = $d['lead_count'];
        $leadValue        = $d['lead_value'];
        $qualifiedCount   = $d['qualified_count'];
        $qualifiedValue   = $d['qualified_value'];
        $proposalCount    = $d['proposal_count'];
        $proposalValue    = $d['proposal_value'];
        $negotiationCount = $d['negotiation_count'];
        $negotiationValue = $d['negotiation_value'];
        $totalWeightedValue = $d['totalWeightedValue'];
        $wonThisMonth       = $d['wonThisMonth'];

        return [
            Stat::make('Pipeline Value', '$' . number_format($totalWeightedValue, 0))
                ->description('Weighted value of open deals')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('primary')
                ->chart($this->getPipelineChart()),

            Stat::make('Lead', '$' . number_format($leadValue, 0))
                ->description("{$leadCount} deals")
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('secondary')
                ->url(route('filament.dashboard.resources.deals.index', ['tab' => 'lead'])),

            Stat::make('Qualified', '$' . number_format($qualifiedValue, 0))
                ->description("{$qualifiedCount} deals")
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('info')
                ->url(route('filament.dashboard.resources.deals.index', ['tab' => 'qualified'])),

            Stat::make('Proposal', '$' . number_format($proposalValue, 0))
                ->description("{$proposalCount} deals")
                ->descriptionIcon('heroicon-m-document-text')
                ->color('warning')
                ->url(route('filament.dashboard.resources.deals.index', ['tab' => 'proposal'])),

            Stat::make('Negotiation', '$' . number_format($negotiationValue, 0))
                ->description("{$negotiationCount} deals")
                ->descriptionIcon('heroicon-m-chat-bubble-left-right')
                ->color('purple')
                ->url(route('filament.dashboard.resources.deals.index', ['tab' => 'negotiation'])),

            Stat::make('Won This Month', '$' . number_format($wonThisMonth, 0))
                ->description('Closed deals')
                ->descriptionIcon('heroicon-m-trophy')
                ->color('success')
                ->url(route('filament.dashboard.resources.deals.index', ['tab' => 'won'])),
        ];
    }

    protected function getPipelineChart(): array
    {
        // Get last 7 days of won deals
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $value = Deal::where('user_id', Auth::id())
                ->where('stage', 'won')
                ->whereDate('actual_close_date', $date)
                ->sum('value');
            $data[] = (float) $value;
        }

        return $data;
    }
}
