<?php

use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Schedule::command('app:generate-sitemap')->everyOddHour();

Schedule::command('app:metrics-beat')->dailyAt('00:01');

Schedule::command('app:local-subscription-expiring-soon-reminder')->dailyAt('00:01');

Schedule::command('app:cleanup-local-subscription-statuses')->hourly();

// Sync calendar events every 15 minutes (configurable via GOOGLE_CALENDAR_SYNC_INTERVAL)
Schedule::job(new \App\Jobs\SyncCalendarEvents())
    ->everyFifteenMinutes()
    ->name('sync-calendar-events')
    ->withoutOverlapping();

// Send scheduled emails every 5 minutes
Schedule::command('emails:send-scheduled')
    ->everyFiveMinutes()
    ->name('send-scheduled-emails')
    ->withoutOverlapping();

// Send invoice reminders daily at 9 AM
Schedule::command('invoices:send-reminders')
    ->dailyAt('09:00')
    ->name('send-invoice-reminders')
    ->withoutOverlapping();

// Send contact follow-up reminders daily at 8 AM
Schedule::command('contacts:send-reminders')
    ->dailyAt('08:00')
    ->name('send-contact-reminders')
    ->withoutOverlapping();

// Send appointment reminders every hour (24 hours before)
Schedule::command('appointments:send-reminders --hours=24')
    ->hourly()
    ->name('send-appointment-reminders-24h')
    ->withoutOverlapping();

// Send appointment reminders for same-day appointments (1 hour before)
Schedule::command('appointments:send-reminders --hours=1')
    ->everyThirtyMinutes()
    ->name('send-appointment-reminders-1h')
    ->withoutOverlapping();

// Send task reminders every 5 minutes (for tasks with reminder_enabled = true)
Schedule::command('tasks:send-reminders')
    ->everyFiveMinutes()
    ->name('send-task-reminders')
    ->withoutOverlapping();

// Send tax deadline reminders daily at 9 AM
Schedule::command('tax:send-reminders')
    ->dailyAt('09:00')
    ->name('send-tax-deadline-reminders')
    ->withoutOverlapping();

// Create tax periods for new year automatically on January 1st
Schedule::command('tax:create-periods')
    ->yearlyOn(1, 1, '00:30') // January 1st at 12:30 AM
    ->name('create-tax-periods')
    ->withoutOverlapping();

// Clear stale cache tags weekly (prevents file buildup)
Schedule::command('cache:prune-stale-tags')
    ->weekly()
    ->sundays()
    ->at('03:00')
    ->name('prune-stale-cache')
    ->withoutOverlapping();

// Clear compiled view cache weekly
Schedule::command('view:clear')
    ->weekly()
    ->sundays()
    ->at('03:30')
    ->name('clear-view-cache')
    ->withoutOverlapping();

// Clear route cache weekly
Schedule::command('route:clear')
    ->weekly()
    ->sundays()
    ->at('03:45')
    ->name('clear-route-cache')
    ->withoutOverlapping();

// Clear config cache weekly
Schedule::command('config:clear')
    ->weekly()
    ->sundays()
    ->at('03:50')
    ->name('clear-config-cache')
    ->withoutOverlapping();

// Refresh business metrics daily at midnight (keeps dashboard data accurate)
Schedule::command('app:refresh-business-metrics')
    ->dailyAt('00:05')
    ->name('refresh-business-metrics')
    ->withoutOverlapping();
