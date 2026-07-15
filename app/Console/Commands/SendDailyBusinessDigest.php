<?php

namespace App\Console\Commands;

use App\Models\Appointment;
use App\Models\ClientInvoice;
use App\Models\Deal;
use App\Models\NotificationPreference;
use App\Models\Task;
use App\Models\User;
use App\Services\MessagingService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendDailyBusinessDigest extends Command
{
    protected $signature = 'WhizzIQ:daily-digest';

    protected $description = 'Send the daily business digest to all owners who have it enabled';

    public function handle(MessagingService $messaging)
    {
        $this->info('Sending daily business digests...');

        // Get all users with digest enabled whose digest time is within the last 5 minutes
        $prefs = NotificationPreference::where('digest_enabled', true)
            ->whereNotNull('owner_phone')
            ->get();

        $sent = 0;

        foreach ($prefs as $pref) {
            // Check if it's the right time for this user (within a 5-minute window)
            $digestTime = now()->setTimeFromTimeString($pref->digest_time);
            if (now()->diffInMinutes($digestTime, false) < -5 || now()->diffInMinutes($digestTime, false) > 0) {
                continue;
            }

            $user = User::find($pref->user_id);
            if (! $user) {
                continue;
            }

            try {
                $message = $this->buildDigest($user);

                $messaging->notifyOwner($user, $message);
                $sent++;

                $this->line("✓ Digest sent to: {$user->name}");
                Log::info("Daily digest sent to user #{$user->id}");
            } catch (\Exception $e) {
                $this->error("✗ Failed for user {$user->name}: {$e->getMessage()}");
                Log::error("Daily digest failed for user #{$user->id}: {$e->getMessage()}");
            }
        }

        $this->info("Daily digests sent: {$sent}");
        return 0;
    }

    private function buildDigest(User $user): string
    {
        $today = now()->format('D j M Y');

        // Today's appointments
        $todayAppointments = Appointment::where('user_id', $user->id)
            ->whereDate('start_datetime', today())
            ->whereIn('status', ['confirmed', 'scheduled'])
            ->count();

        // Outstanding invoices
        $outstandingInvoices = ClientInvoice::forUser($user->id)
            ->whereIn('status', ['sent', 'partial', 'overdue'])
            ->count();

        $overdueInvoices = ClientInvoice::forUser($user->id)
            ->overdue()
            ->count();

        $overdueAmount = ClientInvoice::forUser($user->id)
            ->overdue()
            ->sum('total_amount');

        // Open deals
        $openDeals = Deal::where('user_id', $user->id)
            ->open()
            ->count();

        $staleDeals = Deal::where('user_id', $user->id)
            ->open()
            ->where('updated_at', '<', now()->subDays(14))
            ->count();

        // Tasks due today
        $tasksDueToday = Task::where('user_id', $user->id)
            ->whereDate('due_date', today())
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->count();

        $lines = ["☀️ Good morning! Here's your WhizzIQ brief for {$today}"];
        $lines[] = '';

        if ($todayAppointments > 0) {
            $lines[] = "📅 Appointments today: {$todayAppointments}";
        }

        if ($tasksDueToday > 0) {
            $lines[] = "📋 Tasks due today: {$tasksDueToday}";
        }

        if ($outstandingInvoices > 0) {
            $lines[] = "🧾 Unpaid invoices: {$outstandingInvoices}";
        }

        if ($overdueInvoices > 0) {
            $amount = '£' . number_format($overdueAmount, 0);
            $lines[] = "⚠️ Overdue: {$overdueInvoices} invoice(s) totalling {$amount}";
        }

        if ($openDeals > 0) {
            $lines[] = "💼 Open deals: {$openDeals}";
        }

        if ($staleDeals > 0) {
            $lines[] = "🔴 Stale deals (14+ days): {$staleDeals}";
        }

        if (count($lines) === 2) {
            $lines[] = "✅ All clear — no outstanding items today!";
        }

        $lines[] = '';
        $lines[] = 'Have a great day! — WhizzIQ';

        return implode("\n", $lines);
    }
}
