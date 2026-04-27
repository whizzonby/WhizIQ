<?php

namespace App\Services;

use App\Models\ClientInvoice;
use App\Models\ClientInvoiceItem;
use App\Models\Contact;
use App\Models\Deal;
use App\Models\Expense;
use App\Models\InvoiceClient;
use App\Models\RevenueSource;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DemoDataService
{
    /**
     * Load realistic demo data for a new user so the dashboard
     * looks populated and meaningful on day one.
     */
    public function loadForUser(User $user): void
    {
        DB::transaction(function () use ($user) {
            $contacts = $this->createContacts($user);
            $this->createDeals($user, $contacts);
            $this->createExpenses($user);
            $this->createRevenueSources($user);
            $this->createInvoices($user, $contacts);
            $this->createTasks($user);

            $user->update([
                'has_demo_data'      => true,
                'demo_data_loaded_at' => now(),
            ]);
        });
    }

    /**
     * Remove all demo data that was seeded for this user.
     */
    public function clearForUser(User $user): void
    {
        if (! $user->has_demo_data) {
            return;
        }

        DB::transaction(function () use ($user) {
            // Delete in a safe order (children first)
            $clientIds = InvoiceClient::where('user_id', $user->id)
                ->where('notes', 'LIKE', '%[demo]%')
                ->pluck('id');

            ClientInvoice::where('user_id', $user->id)
                ->whereIn('invoice_client_id', $clientIds)
                ->each(fn($inv) => $inv->items()->delete() || $inv->forceDelete());

            InvoiceClient::whereIn('id', $clientIds)->forceDelete();

            Contact::where('user_id', $user->id)
                ->where('notes', 'LIKE', '%[demo]%')
                ->each(fn($c) => $c->forceDelete());

            Deal::where('user_id', $user->id)
                ->where('notes', 'LIKE', '%[demo]%')
                ->each(fn($d) => $d->forceDelete());

            Expense::where('user_id', $user->id)
                ->where('description', 'LIKE', '%[demo]%')
                ->delete();

            RevenueSource::where('user_id', $user->id)
                ->where('source', 'LIKE', '%[demo]%')
                ->delete();

            Task::where('user_id', $user->id)
                ->where('notes', 'LIKE', '%[demo]%')
                ->each(fn($t) => $t->forceDelete());

            $user->update([
                'has_demo_data'      => false,
                'demo_data_loaded_at' => null,
            ]);
        });
    }

    // ---------------------------------------------------------------
    // Private seeders
    // ---------------------------------------------------------------

    private function createContacts(User $user): array
    {
        $contactsData = [
            [
                'name'                 => 'Sarah Mitchell',
                'email'                => 'sarah.mitchell@brightcorp.example',
                'phone'                => '+1 555-0101',
                'company'              => 'BrightCorp Solutions',
                'type'                 => 'client',
                'status'               => 'active',
                'priority'             => 'high',
                'relationship_strength'=> 'hot',
                'lifetime_value'       => 4800.00,
                'interactions_count'   => 12,
                'last_contact_date'    => now()->subDays(3),
                'next_follow_up_date'  => now()->addDays(7),
                'notes'                => 'Key retainer client. Monthly design work. [demo]',
                'source'               => 'referral',
            ],
            [
                'name'                 => 'James Okonkwo',
                'email'                => 'james@verdantlogic.example',
                'phone'                => '+1 555-0102',
                'company'              => 'Verdant Logic',
                'type'                 => 'client',
                'status'               => 'active',
                'priority'             => 'medium',
                'relationship_strength'=> 'warm',
                'lifetime_value'       => 2200.00,
                'interactions_count'   => 6,
                'last_contact_date'    => now()->subDays(18),
                'next_follow_up_date'  => now()->addDays(3),
                'notes'                => 'Project-based client. Website redesign pending. [demo]',
                'source'               => 'website',
            ],
            [
                'name'                 => 'Priya Sharma',
                'email'                => 'priya@novalinkmedia.example',
                'phone'                => '+1 555-0103',
                'company'              => 'Novalink Media',
                'type'                 => 'client',
                'status'               => 'active',
                'priority'             => 'vip',
                'relationship_strength'=> 'hot',
                'lifetime_value'       => 9500.00,
                'interactions_count'   => 24,
                'last_contact_date'    => now()->subDays(1),
                'notes'                => 'VIP client. Ongoing social media management contract. [demo]',
                'source'               => 'referral',
            ],
            [
                'name'                 => 'Tom Bergmann',
                'email'                => 'tom.bergmann@alphatechco.example',
                'phone'                => '+1 555-0104',
                'company'              => 'Alpha Tech Co.',
                'type'                 => 'lead',
                'status'               => 'active',
                'priority'             => 'high',
                'relationship_strength'=> 'warm',
                'lifetime_value'       => 0,
                'interactions_count'   => 3,
                'last_contact_date'    => now()->subDays(7),
                'next_follow_up_date'  => now()->addDays(2),
                'notes'                => 'Hot lead from LinkedIn. Interested in monthly SEO package. [demo]',
                'source'               => 'linkedin',
            ],
            [
                'name'                 => 'Amara Diallo',
                'email'                => 'amara@craftedspaces.example',
                'phone'                => '+1 555-0105',
                'company'              => 'Crafted Spaces',
                'type'                 => 'lead',
                'status'               => 'active',
                'priority'             => 'medium',
                'relationship_strength'=> 'cold',
                'lifetime_value'       => 0,
                'interactions_count'   => 1,
                'last_contact_date'    => now()->subDays(30),
                'next_follow_up_date'  => now()->subDays(2), // overdue — triggers digest alert
                'notes'                => 'Inbound inquiry about branding package. Not responded yet. [demo]',
                'source'               => 'website',
            ],
        ];

        $contacts = [];
        foreach ($contactsData as $data) {
            $contacts[] = Contact::create(array_merge($data, ['user_id' => $user->id]));
        }

        return $contacts;
    }

    private function createDeals(User $user, array $contacts): void
    {
        $dealsData = [
            [
                'contact_id'          => $contacts[0]->id, // Sarah
                'title'               => 'BrightCorp — Q3 Design Retainer',
                'stage'               => 'won',
                'value'               => 1600.00,
                'probability'         => 100,
                'expected_close_date' => now()->subMonths(2),
                'actual_close_date'   => now()->subMonths(2),
                'source'              => 'referral',
                'priority'            => 'high',
                'notes'               => 'Recurring monthly retainer. Auto-renews quarterly. [demo]',
            ],
            [
                'contact_id'          => $contacts[2]->id, // Priya
                'title'               => 'Novalink Media — Social Management Q2',
                'stage'               => 'won',
                'value'               => 2400.00,
                'probability'         => 100,
                'expected_close_date' => now()->subMonth(),
                'actual_close_date'   => now()->subMonth(),
                'source'              => 'referral',
                'priority'            => 'high',
                'notes'               => 'Includes 4 platforms. Paid upfront. [demo]',
            ],
            [
                'contact_id'          => $contacts[1]->id, // James
                'title'               => 'Verdant Logic — Website Redesign',
                'stage'               => 'proposal',
                'value'               => 3500.00,
                'probability'         => 60,
                'expected_close_date' => now()->addDays(14),
                'source'              => 'website',
                'priority'            => 'medium',
                'notes'               => 'Proposal sent. Awaiting feedback on scope. [demo]',
            ],
            [
                'contact_id'          => $contacts[3]->id, // Tom
                'title'               => 'Alpha Tech Co. — SEO Monthly Package',
                'stage'               => 'negotiation',
                'value'               => 800.00,
                'probability'         => 75,
                'expected_close_date' => now()->addDays(7),
                'source'              => 'linkedin',
                'priority'            => 'high',
                'notes'               => 'Negotiating on start date and deliverables. [demo]',
            ],
            [
                'contact_id'          => $contacts[4]->id, // Amara
                'title'               => 'Crafted Spaces — Brand Identity Package',
                'stage'               => 'lead',
                'value'               => 2000.00,
                'probability'         => 25,
                'expected_close_date' => now()->addDays(30),
                'source'              => 'website',
                'priority'            => 'low',
                'notes'               => 'Initial inquiry. Needs follow-up call. [demo]',
            ],
        ];

        foreach ($dealsData as $data) {
            Deal::create(array_merge($data, [
                'user_id'  => $user->id,
                'currency' => 'USD',
            ]));
        }
    }

    private function createExpenses(User $user): void
    {
        // 3 months of realistic solo-operator expenses
        $months = [
            ['offset' => 2, 'label' => 'two months ago'],
            ['offset' => 1, 'label' => 'last month'],
            ['offset' => 0, 'label' => 'this month'],
        ];

        $expenseTemplates = [
            ['category' => 'Software & Subscriptions', 'amount' => 89,   'day' => 1],
            ['category' => 'Software & Subscriptions', 'amount' => 29,   'day' => 1],
            ['category' => 'Marketing & Advertising',  'amount' => 150,  'day' => 5],
            ['category' => 'Office & Supplies',        'amount' => 45,   'day' => 10],
            ['category' => 'Professional Services',    'amount' => 200,  'day' => 15],
            ['category' => 'Communication',            'amount' => 35,   'day' => 20],
        ];

        foreach ($months as $month) {
            $base = Carbon::today()->subMonths($month['offset'])->startOfMonth();
            foreach ($expenseTemplates as $tpl) {
                Expense::create([
                    'user_id'     => $user->id,
                    'date'        => $base->copy()->addDays($tpl['day'] - 1),
                    'category'    => $tpl['category'],
                    'amount'      => $tpl['amount'],
                    'description' => $tpl['category'] . ' — ' . $base->format('M Y') . ' [demo]',
                ]);
            }
        }

        // One larger expense this month to make alerts interesting
        Expense::create([
            'user_id'     => $user->id,
            'date'        => Carbon::today()->startOfMonth()->addDays(2),
            'category'    => 'Equipment',
            'amount'      => 650,
            'description' => 'New monitor for home office [demo]',
        ]);
    }

    private function createRevenueSources(User $user): void
    {
        $revenueData = [
            // 2 months ago
            ['source' => 'Consulting [demo]', 'amount' => 2400, 'months_ago' => 2, 'day' => 3],
            ['source' => 'Retainer [demo]',   'amount' => 1600, 'months_ago' => 2, 'day' => 1],
            ['source' => 'Project Work [demo]','amount' => 800,  'months_ago' => 2, 'day' => 20],

            // Last month
            ['source' => 'Consulting [demo]', 'amount' => 2800, 'months_ago' => 1, 'day' => 4],
            ['source' => 'Retainer [demo]',   'amount' => 1600, 'months_ago' => 1, 'day' => 1],
            ['source' => 'Social Media [demo]','amount' => 2400, 'months_ago' => 1, 'day' => 1],

            // This month (partial — realistic)
            ['source' => 'Retainer [demo]',   'amount' => 1600, 'months_ago' => 0, 'day' => 1],
            ['source' => 'Social Media [demo]','amount' => 2400, 'months_ago' => 0, 'day' => 1],
            ['source' => 'Consulting [demo]', 'amount' => 900,  'months_ago' => 0, 'day' => 10],
        ];

        foreach ($revenueData as $item) {
            $date = Carbon::today()
                ->subMonths($item['months_ago'])
                ->startOfMonth()
                ->addDays($item['day'] - 1);

            RevenueSource::create([
                'user_id'    => $user->id,
                'date'       => $date,
                'source'     => $item['source'],
                'amount'     => $item['amount'],
                'percentage' => 0,
            ]);
        }
    }

    private function createInvoices(User $user, array $contacts): void
    {
        // Create InvoiceClients for the first 3 contacts
        $invoiceClients = [];
        $clientDefs = [
            ['contact' => $contacts[0], 'company' => 'BrightCorp Solutions'],
            ['contact' => $contacts[2], 'company' => 'Novalink Media'],
            ['contact' => $contacts[1], 'company' => 'Verdant Logic'],
        ];

        foreach ($clientDefs as $def) {
            $invoiceClients[] = InvoiceClient::create([
                'user_id'    => $user->id,
                'contact_id' => $def['contact']->id,
                'name'       => $def['contact']->name,
                'email'      => $def['contact']->email,
                'phone'      => $def['contact']->phone,
                'company'    => $def['company'],
                'is_active'  => true,
                'notes'      => '[demo]',
            ]);
        }

        $invoiceDefs = [
            // Paid invoice — 5 weeks ago
            [
                'client'         => $invoiceClients[1], // Priya / Novalink
                'invoice_number' => 'INV-001',
                'status'         => 'paid',
                'invoice_date'   => now()->subWeeks(5),
                'due_date'       => now()->subWeeks(3),
                'paid_date'      => now()->subWeeks(3)->addDays(2),
                'subtotal'       => 2400.00,
                'total_amount'   => 2400.00,
                'amount_paid'    => 2400.00,
                'balance_due'    => 0.00,
                'items'          => [
                    ['description' => 'Social Media Management — April', 'quantity' => 1, 'unit_price' => 2400.00],
                ],
            ],
            // Pending invoice — current month
            [
                'client'         => $invoiceClients[0], // Sarah / BrightCorp
                'invoice_number' => 'INV-002',
                'status'         => 'sent',
                'invoice_date'   => now()->startOfMonth()->addDays(1),
                'due_date'       => now()->startOfMonth()->addDays(30),
                'subtotal'       => 1600.00,
                'total_amount'   => 1600.00,
                'amount_paid'    => 0.00,
                'balance_due'    => 1600.00,
                'items'          => [
                    ['description' => 'Design Retainer — May', 'quantity' => 1, 'unit_price' => 1600.00],
                ],
            ],
            // Overdue invoice — triggers alert
            [
                'client'         => $invoiceClients[2], // James / Verdant Logic
                'invoice_number' => 'INV-003',
                'status'         => 'overdue',
                'invoice_date'   => now()->subWeeks(6),
                'due_date'       => now()->subWeeks(2),
                'subtotal'       => 750.00,
                'total_amount'   => 750.00,
                'amount_paid'    => 0.00,
                'balance_due'    => 750.00,
                'items'          => [
                    ['description' => 'Website Consultation', 'quantity' => 5, 'unit_price' => 150.00],
                ],
            ],
        ];

        foreach ($invoiceDefs as $def) {
            $invoice = ClientInvoice::create([
                'user_id'          => $user->id,
                'invoice_client_id'=> $def['client']->id,
                'invoice_number'   => $def['invoice_number'],
                'status'           => $def['status'],
                'invoice_date'     => $def['invoice_date'],
                'due_date'         => $def['due_date'],
                'paid_date'        => $def['paid_date'] ?? null,
                'subtotal'         => $def['subtotal'],
                'tax_rate'         => 0,
                'tax_amount'       => 0,
                'discount_amount'  => 0,
                'total_amount'     => $def['total_amount'],
                'amount_paid'      => $def['amount_paid'],
                'balance_due'      => $def['balance_due'],
                'currency'         => 'USD',
            ]);

            foreach ($def['items'] as $item) {
                ClientInvoiceItem::create([
                    'client_invoice_id' => $invoice->id,
                    'description'       => $item['description'],
                    'quantity'          => $item['quantity'],
                    'unit_price'        => $item['unit_price'],
                    'amount'            => $item['quantity'] * $item['unit_price'],
                ]);
            }
        }
    }

    private function createTasks(User $user): void
    {
        $tasksData = [
            [
                'title'       => 'Send revised proposal to Verdant Logic',
                'priority'    => 'high',
                'status'      => 'pending',
                'due_date'    => now()->addDays(2),
                'notes'       => '[demo]',
            ],
            [
                'title'       => 'Follow up: Alpha Tech SEO contract',
                'priority'    => 'high',
                'status'      => 'pending',
                'due_date'    => now()->addDay(),
                'notes'       => '[demo]',
            ],
            [
                'title'       => 'Prepare May social content calendar for Novalink',
                'priority'    => 'medium',
                'status'      => 'in_progress',
                'due_date'    => now()->addDays(5),
                'notes'       => '[demo]',
            ],
            [
                'title'       => 'Chase overdue invoice INV-003 (Verdant Logic)',
                'priority'    => 'high',
                'status'      => 'pending',
                'due_date'    => now(),
                'notes'       => '[demo]',
            ],
            [
                'title'       => 'Review Q2 expenses and categorise',
                'priority'    => 'medium',
                'status'      => 'pending',
                'due_date'    => now()->addDays(7),
                'notes'       => '[demo]',
            ],
            [
                'title'       => 'Send onboarding questionnaire to BrightCorp',
                'priority'    => 'low',
                'status'      => 'completed',
                'due_date'    => now()->subDays(5),
                'completed_at'=> now()->subDays(4),
                'notes'       => '[demo]',
            ],
        ];

        foreach ($tasksData as $data) {
            Task::create(array_merge($data, ['user_id' => $user->id]));
        }
    }
}
