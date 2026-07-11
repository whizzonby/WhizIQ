<?php

namespace App\Filament\Dashboard\Widgets;

use App\Models\Appointment;
use App\Models\EmailLog;
use Carbon\Carbon;
use Filament\Widgets\Widget;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class CampaignPerformanceWidget extends Widget
{
    protected static ?int $sort = -1;

    protected static bool $isLazy = true;

    protected int|string|array $columnSpan = 'full';

    protected string $view = 'filament.dashboard.widgets.campaign-performance-widget';

    public function getCampaignStats(): array
    {
        $userId = Auth::id();
        $cacheKey = "campaign_performance_{$userId}_" . now()->format('Y-m-d-H');

        return Cache::remember($cacheKey, 900, function () use ($userId) {
            $presets = [
                'calendar_gap' => [
                    'label' => 'Calendar gap',
                    'description' => 'Fills quiet days from open-slot prompts.',
                ],
                'rebooking' => [
                    'label' => 'Rebooking',
                    'description' => 'Brings previous clients back.',
                ],
            ];

            $emailLogs = EmailLog::where('user_id', $userId)
                ->where('status', 'sent')
                ->where('created_at', '>=', now()->subDays(30))
                ->get(['metadata']);

            return collect($presets)
                ->map(function (array $preset, string $key) use ($emailLogs, $userId) {
                    $sent = $emailLogs
                        ->filter(fn (EmailLog $log) => ($log->metadata['campaign_preset'] ?? null) === $key)
                        ->count();

                    $appointments = Appointment::where('user_id', $userId)
                        ->where('booked_via', 'campaign_' . $key)
                        ->where('created_at', '>=', now()->subDays(30))
                        ->with(['appointmentType:id,name,price', 'clientInvoice:id,total_amount,amount_paid,status'])
                        ->latest()
                        ->get();

                    $booked = $appointments->count();
                    $projectedRevenue = $this->projectedRevenue($appointments);
                    $collectedRevenue = $this->collectedRevenue($appointments);

                    return [
                        'key' => $key,
                        'label' => $preset['label'],
                        'description' => $preset['description'],
                        'sent' => $sent,
                        'booked' => $booked,
                        'conversion' => $sent > 0 ? round(($booked / $sent) * 100, 1) : 0,
                        'projected_revenue' => $projectedRevenue,
                        'collected_revenue' => $collectedRevenue,
                        'formatted_projected_revenue' => $this->formatMoney($projectedRevenue),
                        'formatted_collected_revenue' => $this->formatMoney($collectedRevenue),
                        'top_service' => $this->topService($appointments),
                        'best_day' => $this->bestDay($appointments),
                        'trend' => $this->bookingTrend($appointments),
                        'recent_bookings' => $this->recentBookings($appointments),
                        'launch_url' => route('filament.dashboard.pages.email-composer-page', [
                            'campaign' => $key,
                        ]),
                    ];
                })
                ->values()
                ->all();
        });
    }

    protected function projectedRevenue(Collection $appointments): float
    {
        return (float) $appointments->sum(function (Appointment $appointment): float {
            if ($appointment->clientInvoice) {
                return (float) $appointment->clientInvoice->total_amount;
            }

            return (float) ($appointment->appointmentType?->price ?? 0);
        });
    }

    protected function collectedRevenue(Collection $appointments): float
    {
        return (float) $appointments->sum(fn (Appointment $appointment): float => (float) ($appointment->clientInvoice?->amount_paid ?? 0));
    }

    protected function topService(Collection $appointments): ?array
    {
        $top = $appointments
            ->groupBy(fn (Appointment $appointment): string => $appointment->appointmentType?->name ?? 'Unassigned service')
            ->map(fn (Collection $group): int => $group->count())
            ->sortDesc()
            ->map(fn (int $count, string $name): array => [
                'name' => $name,
                'count' => $count,
            ])
            ->first();

        return $top ?: null;
    }

    protected function bestDay(Collection $appointments): ?array
    {
        $bestDay = $appointments
            ->groupBy(fn (Appointment $appointment): string => $appointment->created_at->format('Y-m-d'))
            ->map(fn (Collection $group): int => $group->count())
            ->sortDesc()
            ->map(fn (int $count, string $date): array => [
                'date' => $date,
                'label' => Carbon::parse($date)->format('M j'),
                'count' => $count,
            ])
            ->first();

        return $bestDay ?: null;
    }

    protected function bookingTrend(Collection $appointments): array
    {
        $countsByDate = $appointments
            ->groupBy(fn (Appointment $appointment): string => $appointment->created_at->format('Y-m-d'))
            ->map(fn (Collection $group): int => $group->count());

        $days = collect(range(13, 0))
            ->map(function (int $daysAgo) use ($countsByDate): array {
                $date = now()->copy()->subDays($daysAgo);
                $key = $date->format('Y-m-d');

                return [
                    'date' => $key,
                    'label' => $date->format('M j'),
                    'short_label' => $date->format('j'),
                    'count' => (int) ($countsByDate[$key] ?? 0),
                ];
            });

        $max = max(1, $days->max('count'));

        return $days
            ->map(fn (array $day): array => array_merge($day, [
                'height' => max(8, (int) round(($day['count'] / $max) * 44)),
            ]))
            ->all();
    }

    protected function recentBookings(Collection $appointments): array
    {
        return $appointments
            ->take(4)
            ->map(function (Appointment $appointment): array {
                $value = $appointment->clientInvoice
                    ? (float) $appointment->clientInvoice->total_amount
                    : (float) ($appointment->appointmentType?->price ?? 0);

                return [
                    'client' => $appointment->attendee_name ?: 'Client',
                    'service' => $appointment->appointmentType?->name ?? 'Unassigned service',
                    'booked_at' => $appointment->created_at->format('M j'),
                    'appointment_at' => $appointment->start_datetime?->format('M j, g:i A') ?? 'Not scheduled',
                    'status' => str($appointment->status)->replace('_', ' ')->title()->toString(),
                    'value' => $this->formatMoney($value),
                ];
            })
            ->values()
            ->all();
    }

    protected function formatMoney(float $amount): string
    {
        return '$' . number_format($amount, 2);
    }

    public static function canView(): bool
    {
        return true;
    }
}
