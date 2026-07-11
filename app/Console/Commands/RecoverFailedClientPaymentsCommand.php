<?php

namespace App\Console\Commands;

use App\Services\ClientPaymentRecoveryService;
use Illuminate\Console\Command;

class RecoverFailedClientPaymentsCommand extends Command
{
    protected $signature = 'payments:recover-failed-client-payments {--limit=50}';

    protected $description = 'Send recovery reminders for failed client invoice payment attempts';

    public function handle(ClientPaymentRecoveryService $recoveryService): int
    {
        $sent = $recoveryService->recoverFailedAttempts((int) $this->option('limit'));

        $this->info("Sent {$sent} failed-payment recovery reminder(s).");

        return self::SUCCESS;
    }
}
