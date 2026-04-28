<?php

namespace App\Filament\Dashboard\Widgets;

use App\Models\Appointment;
use App\Models\ClientInvoice;
use App\Models\Deal;
use App\Models\FollowUpReminder;
use App\Models\Task;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class AttentionDigestWidget extends Widget
{
    protected static ?int $sort = -2;

    protected static bool $isLazy = true;

    protected int|string|array $columnSpan = 'full';

    protected string $view = 'filament.dashboard.widgets.attention-digest-widget';

    public function getDigestData(): array
    {
        $userId   = Auth::id();
        $cacheKey = "attention_digest_{$userId}_" . now()->format('Y-m-d-H');

        return Cache::remember($cacheKey, 900, function () use ($userId) {
            $items = collect();

            // Overdue invoices — show up to 3, highest amount first
            ClientInvoice::with('client')
                ->where('user_id', $userId)
                ->where('status', 'overdue')
                ->orderByRaw('(total_amount - amount_paid) DESC')
                ->limit(3)
                ->get()
                ->each(function ($inv) use ($items) {
                    $owed = $inv->total_amount - $inv->amount_paid;
                    $days = max(0, (int) now()->diffInDays($inv->due_date));
                    $name = optional($inv->client)->name ?? 'Client';
                    $items->push([
                        'type'         => 'overdue_invoice',
                        'border_color' => '#ef4444',
                        'title'        => $name . ' invoice overdue',
                        'subtitle'     => '£' . number_format($owed, 0) . ' · ' . $days . ' ' . ($days === 1 ? 'day' : 'days') . ' past due',
                        'action_label' => 'Send reminder',
                        'action_color' => '#fef2f2',
                        'action_text'  => '#dc2626',
                        'action_url'   => route('filament.dashboard.resources.client-invoices.index'),
                    ]);
                });

            // Stale deals — show up to 2, longest stale first
            Deal::where('user_id', $userId)
                ->whereIn('stage', ['lead', 'qualified', 'proposal', 'negotiation'])
                ->where('updated_at', '<', now()->subDays(14))
                ->orderBy('updated_at')
                ->limit(2)
                ->get()
                ->each(function ($deal) use ($items) {
                    $days = (int) now()->diffInDays($deal->updated_at);
                    $items->push([
                        'type'         => 'stale_deal',
                        'border_color' => '#8b5cf6',
                        'title'        => ($deal->title ?? 'Deal') . ' going cold',
                        'subtitle'     => '£' . number_format($deal->value ?? 0, 0) . ' · ' . $days . ' days no contact',
                        'action_label' => 'Follow up',
                        'action_color' => '#f5f3ff',
                        'action_text'  => '#7c3aed',
                        'action_url'   => route('filament.dashboard.resources.deals.index'),
                    ]);
                });

            // Today's appointments
            Appointment::with('contact')
                ->where('user_id', $userId)
                ->whereIn('status', ['scheduled', 'confirmed'])
                ->whereDate('start_datetime', today())
                ->orderBy('start_datetime')
                ->limit(3)
                ->get()
                ->each(function ($appt) use ($items) {
                    $clientName = optional($appt->contact)->name ?? ($appt->attendee_name ?? 'Client');
                    $time       = $appt->start_datetime->format('g:i A');
                    $label      = ucfirst($appt->status);
                    $items->push([
                        'type'         => 'appointment',
                        'border_color' => '#22c55e',
                        'title'        => ($appt->title ?? 'Appointment') . ' with ' . $clientName,
                        'subtitle'     => 'Today at ' . $time . ' · ' . $label,
                        'action_label' => $label,
                        'action_color' => '#f0fdf4',
                        'action_text'  => '#16a34a',
                        'action_url'   => route('filament.dashboard.pages.appointment-calendar'),
                    ]);
                });

            return [
                'items'       => $items->all(),
                'total_items' => $items->count(),
            ];
        });
    }

    public static function canView(): bool
    {
        return true;
    }
}
