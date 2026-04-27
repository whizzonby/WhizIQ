<?php

namespace App\Filament\Dashboard\Widgets;

use App\Models\ClientPayment;
use App\Models\RevenueSource;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Cache;

class RevenueSourcesWidget extends ChartWidget
{
    protected ?string $heading = '💰 Key Revenue Sources (Last 30 Days)';

    protected static ?int $sort = 3;

    protected static bool $isLazy = true;

    protected function getData(): array
    {
        $user = auth()->user();
        $last30Days = Carbon::today()->subDays(30);
        $now = Carbon::now();

        // Cache revenue sources data for 1 hour to improve performance
        $cacheKey = "revenue_sources_widget_{$user->id}_" . now()->format('Y-m-d-H');

        $data = Cache::remember($cacheKey, 3600, function () use ($user, $last30Days, $now) {
            // Get revenue from RevenueSource table, grouped by source
            $revenueSources = RevenueSource::where('user_id', $user->id)
                ->whereBetween('date', [$last30Days, $now])
                ->selectRaw('source, SUM(amount) as total')
                ->groupBy('source')
                ->get();

            // Get invoice payment revenue (excluding tax portion)
            $invoiceRevenue = ClientPayment::where('client_payments.user_id', $user->id)
                ->whereBetween('client_payments.payment_date', [$last30Days, $now])
                ->join('client_invoices', 'client_payments.client_invoice_id', '=', 'client_invoices.id')
                ->selectRaw('SUM(
                    CASE
                        WHEN client_invoices.total_amount > 0
                        THEN client_payments.amount * (client_invoices.subtotal / client_invoices.total_amount)
                        ELSE client_payments.amount
                    END
                ) as total')
                ->value('total') ?? 0;

            // Combine all sources
            $allSources = collect();

            // Add revenue sources
            foreach ($revenueSources as $source) {
                $allSources->push([
                    'source' => $source->source,
                    'total' => (float) $source->total,
                ]);
            }

            // Add invoice payments as a source
            if ($invoiceRevenue > 0) {
                $allSources->push([
                    'source' => 'client_invoices',
                    'total' => (float) $invoiceRevenue,
                ]);
            }

            return $allSources->sortByDesc('total')->take(6)->values();
        });

        if ($data->isEmpty()) {
            return [
                'datasets' => [
                    [
                        'data' => [],
                        'backgroundColor' => [],
                    ],
                ],
                'labels' => [],
            ];
        }

        // More vibrant and diverse colors
        $colors = [
            'rgba(59, 130, 246, 0.9)',   // Blue
            'rgba(16, 185, 129, 0.9)',   // Green
            'rgba(245, 158, 11, 0.9)',   // Amber
            'rgba(139, 92, 246, 0.9)',   // Purple
            'rgba(236, 72, 153, 0.9)',   // Pink
            'rgba(20, 184, 166, 0.9)',   // Teal
        ];

        return [
            'datasets' => [
                [
                    'data' => $data->pluck('total')->toArray(),
                    'backgroundColor' => array_slice($colors, 0, $data->count()),
                    'borderWidth' => 2,
                    'borderColor' => '#ffffff',
                ],
            ],
            'labels' => $data->pluck('source')->map(function ($source) {
                return ucwords(str_replace('_', ' ', $source));
            })->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'bottom',
                    'labels' => [
                        'padding' => 15,
                        'font' => [
                            'size' => 12,
                            'weight' => '500',
                        ],
                        'usePointStyle' => true,
                        'pointStyle' => 'circle',
                    ],
                ],
                'tooltip' => [
                    'backgroundColor' => 'rgba(0, 0, 0, 0.8)',
                    'padding' => 12,
                    'titleFont' => [
                        'size' => 14,
                        'weight' => 'bold',
                    ],
                    'bodyFont' => [
                        'size' => 13,
                    ],
                    'callbacks' => [
                        'label' => [
                            'formatter' => 'function(context) {
                                let label = context.label || "";
                                let value = context.parsed || 0;
                                let total = context.dataset.data.reduce((a, b) => a + b, 0);
                                let percentage = ((value / total) * 100).toFixed(1);
                                return label + ": $" + value.toLocaleString() + " (" + percentage + "%)";
                            }',
                        ],
                    ],
                ],
            ],
            'cutout' => '60%',
            'responsive' => true,
            'maintainAspectRatio' => true,
        ];
    }
}
