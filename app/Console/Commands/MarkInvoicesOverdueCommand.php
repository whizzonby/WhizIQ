<?php

namespace App\Console\Commands;

use App\Models\ClientInvoice;
use App\Services\MessagingService;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class MarkInvoicesOverdueCommand extends Command
{
    protected $signature   = 'invoices:mark-overdue';
    protected $description = 'Mark past-due invoices as overdue and alert owners';

    public function handle(MessagingService $messaging): int
    {
        $updated = ClientInvoice::whereIn('status', ['sent', 'partial'])
            ->whereDate('due_date', '<', now()->toDateString())
            ->get();

        $count = 0;

        foreach ($updated as $invoice) {
            $invoice->status = 'overdue';
            $invoice->save();
            $count++;

            // Alert the business owner
            $owner = User::find($invoice->user_id);
            if ($owner) {
                $invoice->loadMissing('client');
                $messaging->sendOverdueInvoiceAlert(
                    $owner,
                    $invoice->client?->name ?? 'Unknown client',
                    (float) $invoice->balance_due,
                    $invoice->invoice_number,
                    now()->diffInDays($invoice->due_date)
                );
            }
        }

        Log::info("MarkInvoicesOverdue: marked {$count} invoices as overdue");
        $this->info("Marked {$count} invoices as overdue.");

        return self::SUCCESS;
    }
}
