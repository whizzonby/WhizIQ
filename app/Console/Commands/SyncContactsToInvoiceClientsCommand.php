<?php

namespace App\Console\Commands;

use App\Models\Contact;
use App\Models\InvoiceClient;
use Illuminate\Console\Command;

class SyncContactsToInvoiceClientsCommand extends Command
{
    protected $signature   = 'contacts:sync-invoice-clients';
    protected $description = 'Backfill invoice_clients rows for existing contacts that have none';

    public function handle(): int
    {
        $contacts = Contact::whereNotExists(function ($q) {
            $q->from('invoice_clients')
              ->whereColumn('invoice_clients.contact_id', 'contacts.id')
              ->whereNull('invoice_clients.deleted_at');
        })->get();

        $this->info("Found {$contacts->count()} contacts without an invoice client record.");

        $synced = 0;
        foreach ($contacts as $contact) {
            try {
                InvoiceClient::updateOrCreate(
                    ['user_id' => $contact->user_id, 'contact_id' => $contact->id],
                    [
                        'name'      => $contact->name,
                        'email'     => $contact->email,
                        'phone'     => $contact->phone,
                        'company'   => $contact->company,
                        'address'   => $contact->address,
                        'city'      => $contact->city,
                        'state'     => $contact->state,
                        'zip'       => $contact->zip,
                        'country'   => $contact->country,
                        'is_active' => true,
                    ]
                );
                $synced++;
            } catch (\Exception $e) {
                $this->warn("Failed for contact {$contact->id}: {$e->getMessage()}");
            }
        }

        $this->info("Done. Synced {$synced} contacts.");

        return self::SUCCESS;
    }
}
