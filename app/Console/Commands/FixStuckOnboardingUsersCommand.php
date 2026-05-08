<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class FixStuckOnboardingUsersCommand extends Command
{
    protected $signature   = 'users:fix-stuck-onboarding
                                {--dry-run : List affected users without sending emails}';
    protected $description = 'Resend verification emails to users who registered but never verified their email';

    public function handle(): int
    {
        $users = User::whereNull('email_verified_at')
            ->where('is_admin', false)
            ->get();

        if ($users->isEmpty()) {
            $this->info('No unverified users found.');
            return self::SUCCESS;
        }

        $this->info("Found {$users->count()} unverified user(s).");

        if ($this->option('dry-run')) {
            $this->table(['ID', 'Name', 'Email', 'Registered'], $users->map(fn ($u) => [
                $u->id, $u->name, $u->email, $u->created_at->toDateTimeString(),
            ])->toArray());
            return self::SUCCESS;
        }

        $sent = 0;
        foreach ($users as $user) {
            try {
                $user->sendEmailVerificationNotification();
                $this->line("  Sent to {$user->email}");
                $sent++;
            } catch (\Exception $e) {
                $this->warn("  Failed for {$user->email}: {$e->getMessage()}");
            }
        }

        $this->info("Done. Verification email sent to {$sent} user(s).");

        return self::SUCCESS;
    }
}
