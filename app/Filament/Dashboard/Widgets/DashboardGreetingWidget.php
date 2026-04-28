<?php

namespace App\Filament\Dashboard\Widgets;

use App\Models\Appointment;
use App\Models\ClientInvoice;
use App\Models\Deal;
use App\Models\Task;
use App\Models\FollowUpReminder;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class DashboardGreetingWidget extends Widget
{
    protected static ?int $sort = -10;

    protected static bool $isLazy = false;

    protected int|string|array $columnSpan = 'full';

    protected string $view = 'filament.dashboard.widgets.dashboard-greeting-widget';

    public function getGreetingData(): array
    {
        $user  = Auth::user();
        $hour  = now()->hour;

        $timeOfDay = match(true) {
            $hour < 12 => 'morning',
            $hour < 17 => 'afternoon',
            default    => 'evening',
        };

        $rawName   = trim($user->name ?? '');
        // If name looks like an email or is empty, use a friendly fallback
        $firstName = (str_contains($rawName, '@') || $rawName === '')
            ? 'there'
            : explode(' ', $rawName)[0];

        $attentionCount = Cache::remember(
            "greeting_attention_{$user->id}_" . now()->format('Y-m-d-H'),
            900,
            function () use ($user) {
                $overdue = ClientInvoice::where('user_id', $user->id)
                    ->where('status', 'overdue')
                    ->count();

                $stale = Deal::where('user_id', $user->id)
                    ->whereIn('stage', ['lead', 'qualified', 'proposal', 'negotiation'])
                    ->where('updated_at', '<', now()->subDays(14))
                    ->count();

                $followUps = FollowUpReminder::where('user_id', $user->id)
                    ->where('status', 'pending')
                    ->where('remind_at', '<=', now())
                    ->count();

                $tasksDue = Task::where('user_id', $user->id)
                    ->where('status', '!=', 'completed')
                    ->whereDate('due_date', today())
                    ->count();

                return $overdue + $stale + $followUps + $tasksDue;
            }
        );

        return [
            'name'            => $firstName,
            'time_of_day'     => $timeOfDay,
            'date'            => now()->format('l, j F Y'),
            'attention_count' => $attentionCount,
        ];
    }
}
