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
        $userId = Auth::id();
        $cacheKey = "attention_digest_{$userId}_" . now()->format('Y-m-d-H');

        return Cache::remember($cacheKey, 900, function () use ($userId) {
            // Overdue invoices
            $overdueInvoices = ClientInvoice::where('user_id', $userId)
                ->where('status', 'overdue')
                ->selectRaw('COUNT(*) as count, COALESCE(SUM(total_amount - amount_paid), 0) as total')
                ->first();

            // Follow-ups due today or overdue
            $followUpsDue = FollowUpReminder::where('user_id', $userId)
                ->where('status', 'pending')
                ->where('remind_at', '<=', now())
                ->count();

            // Stale deals — open deals not updated in 14+ days
            $staleDeals = Deal::where('user_id', $userId)
                ->whereIn('stage', ['lead', 'qualified', 'proposal', 'negotiation'])
                ->where('updated_at', '<', now()->subDays(14))
                ->count();

            // Tasks due today
            $tasksDueToday = Task::where('user_id', $userId)
                ->where('status', '!=', 'completed')
                ->whereDate('due_date', today())
                ->count();

            // Upcoming appointments in next 24 hours
            $upcomingAppointments = Appointment::where('user_id', $userId)
                ->whereIn('status', ['scheduled', 'confirmed'])
                ->whereBetween('start_datetime', [now(), now()->addHours(24)])
                ->count();

            // Overdue tasks (past due, not completed)
            $overdueTasks = Task::where('user_id', $userId)
                ->where('status', '!=', 'completed')
                ->whereNotNull('due_date')
                ->where('due_date', '<', today())
                ->count();

            return [
                'overdue_invoices_count'  => (int) $overdueInvoices->count,
                'overdue_invoices_total'  => (float) $overdueInvoices->total,
                'follow_ups_due'          => $followUpsDue,
                'stale_deals'             => $staleDeals,
                'tasks_due_today'         => $tasksDueToday,
                'overdue_tasks'           => $overdueTasks,
                'upcoming_appointments'   => $upcomingAppointments,
                'total_items'             => (int) $overdueInvoices->count + $followUpsDue + $staleDeals + $tasksDueToday + $overdueTasks,
            ];
        });
    }

    public static function canView(): bool
    {
        return true;
    }
}
