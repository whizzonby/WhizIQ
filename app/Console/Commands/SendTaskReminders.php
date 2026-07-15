<?php

namespace App\Console\Commands;

use App\Models\NotificationPreference;
use App\Models\Task;
use App\Notifications\TaskReminderNotification;
use App\Services\MessagingService;
use Illuminate\Console\Command;

class SendTaskReminders extends Command
{
    protected $signature = 'tasks:send-reminders';

    protected $description = 'Send reminders for tasks that are due via email and SMS/WhatsApp';

    public function handle(MessagingService $messaging)
    {
        $this->info('Checking for task reminders...');

        $tasks = Task::where('reminder_enabled', true)
            ->whereNotNull('reminder_date')
            ->where('reminder_date', '<=', now()->addMinutes(5))
            ->where('reminder_date', '>', now()->subHour())
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->with('user')
            ->get();

        if ($tasks->isEmpty()) {
            $this->info('No reminders to send.');
            return 0;
        }

        $count = 0;

        foreach ($tasks as $task) {
            try {
                // In-app / email notification
                $task->user->notify(new TaskReminderNotification($task));

                // SMS to owner if enabled
                $prefs = NotificationPreference::forUser($task->user_id);
                if ($prefs->alert_task_due) {
                    $dueLabel = $task->due_date ? $task->due_date->format('D j M') : 'today';

                    $messaging->notifyOwner(
                        $task->user,
                        "📋 Task due: {$task->title}\nDue: {$dueLabel}\n\n— WhizzIQ"
                    );
                }

                $this->line("✓ Reminder sent for task: {$task->title} (User: {$task->user->name})");
                $count++;
            } catch (\Exception $e) {
                $this->error("✗ Failed for task: {$task->title} — {$e->getMessage()}");
            }
        }

        $this->info("\nSent {$count} reminder(s) successfully.");
        return 0;
    }
}
