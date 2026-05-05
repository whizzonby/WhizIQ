<?php

namespace Database\Seeders;

use App\Models\ClientInvoice;
use App\Models\ClientInvoiceItem;
use App\Models\Contact;
use App\Models\Deal;
use App\Models\Expense;
use App\Models\Goal;
use App\Models\InvoiceClient;
use App\Models\Task;
use App\Models\User;
use App\Models\BusinessProfile;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('email', 'demo@whiziq.com')->first();

        if (! $user) {
            $this->command->error('Demo user not found. Create it first with the tinker command.');
            return;
        }

        $this->command->info('Seeding demo data for: ' . $user->email);

        $this->seedBusinessProfile($user);

        $clients = $this->seedInvoiceClients($user);

        // Wrap invoice + item creates to prevent the item saved event from
        // triggering invoice recalculation on every single row insert
        ClientInvoice::withoutEvents(function () use ($user, $clients) {
            ClientInvoiceItem::withoutEvents(function () use ($user, $clients) {
                $this->seedInvoices($user, $clients);
            });
        });

        // Wrap expense creates to prevent AI auto-categorization API calls
        Expense::withoutEvents(function () use ($user) {
            $this->seedExpenses($user);
        });

        $contacts = $this->seedContacts($user);

        // Wrap deal creates to prevent Twilio SMS on stage changes
        Deal::withoutEvents(function () use ($user, $contacts) {
            $this->seedDeals($user, $contacts);
        });

        $this->seedGoals($user);
        $this->seedTasks($user);

        $this->command->info('Demo data seeded successfully.');
    }

    private function seedBusinessProfile(User $user): void
    {
        BusinessProfile::updateOrCreate(
            ['user_id' => $user->id],
            [
                'biz_registered_name'  => 'Apex Digital Solutions Ltd',
                'biz_trading_name'     => 'Apex Digital',
                'biz_country'          => 'GH',
                'biz_legal_type'       => 'limited_company',
                'biz_incorporation_date' => '2022-03-15',
                'ops_employee_count'   => 8,
                'ops_location'         => 'Accra, Ghana',
                'ops_hours'            => 'Mon–Fri, 8am–6pm',
                'ops_systems'          => ['WhizIQ', 'Google Workspace', 'Slack'],
                'rev_monthly_avg'      => 18500,
                'rev_yoy_change'       => 34,
                'rev_payment_methods'  => ['Bank Transfer', 'Mobile Money', 'Card'],
                'rev_channels'         => ['Retainer Clients', 'Project Work', 'Referrals'],
                'exp_fixed_monthly'    => 4200,
                'exp_variable_monthly' => 2800,
                'exp_payroll'          => 9500,
                'exp_marketing'        => 1200,
                'hr_full_time'         => 6,
                'hr_part_time'         => 2,
                'hr_avg_salary'        => 1580,
                'mkt_platforms'        => ['LinkedIn', 'Instagram', 'Google Ads'],
                'mkt_followers'        => ['LinkedIn' => 1840, 'Instagram' => 3200, 'Google Ads' => 0],
                'mkt_budget'           => 1200,
                'comp_tax_cycle'       => 'quarterly',
                'comp_bookkeeping_type' => 'software',
                'fin_bank_balance'     => 42000,
                'strat_goals'          => ['Grow to GHS 300k ARR', 'Hire 2 senior engineers', 'Launch SaaS product'],
                'strat_challenges'     => ['Late client payments', 'Talent acquisition'],
                'monthly_net_profit'   => 4800,
                'profit_margin'        => 26,
                'growth_score'         => 72,
            ]
        );
    }

    private function seedInvoiceClients(User $user): array
    {
        $clientData = [
            [
                'name'    => 'Kofi Mensah',
                'email'   => 'kofi@mensahgroup.com',
                'company' => 'Mensah Group',
                'phone'   => '+233244112233',
                'address' => '14 Independence Ave',
                'city'    => 'Accra',
                'country' => 'GH',
            ],
            [
                'name'    => 'Ama Owusu',
                'email'   => 'ama@brightfuturesconsult.com',
                'company' => 'Bright Futures Consulting',
                'phone'   => '+233200998877',
                'address' => '5 Liberation Road',
                'city'    => 'Accra',
                'country' => 'GH',
            ],
            [
                'name'    => 'James Osei',
                'email'   => 'josei@primerealty.gh',
                'company' => 'Prime Realty GH',
                'phone'   => '+233277443322',
                'address' => 'Airport City',
                'city'    => 'Accra',
                'country' => 'GH',
            ],
            [
                'name'    => 'Abena Darko',
                'email'   => 'abena@starlogistics.com',
                'company' => 'Star Logistics Ltd',
                'phone'   => '+233266554433',
                'address' => 'Tema Industrial Area',
                'city'    => 'Tema',
                'country' => 'GH',
            ],
            [
                'name'    => 'Kweku Asante',
                'email'   => 'k.asante@techbridgegh.io',
                'company' => 'TechBridge Ghana',
                'phone'   => '+233555001122',
                'address' => '3 Switchback Road',
                'city'    => 'Accra',
                'country' => 'GH',
            ],
        ];

        $clients = [];
        foreach ($clientData as $data) {
            $clients[] = InvoiceClient::firstOrCreate(
                ['user_id' => $user->id, 'email' => $data['email']],
                array_merge($data, ['user_id' => $user->id, 'is_active' => true])
            );
        }

        return $clients;
    }

    private function seedInvoices(User $user, array $clients): void
    {
        $invoices = [
            // Paid invoice - 2 months ago
            [
                'client'       => $clients[0],
                'status'       => 'paid',
                'invoice_date' => Carbon::now()->subMonths(2)->startOfMonth(),
                'due_date'     => Carbon::now()->subMonths(2)->startOfMonth()->addDays(30),
                'paid_date'    => Carbon::now()->subMonths(2)->startOfMonth()->addDays(18),
                'currency'     => 'GHS',
                'notes'        => 'Thank you for your continued partnership.',
                'template'     => 'modern',
                'primary_color' => '#6366f1',
                'items'        => [
                    ['description' => 'Brand Strategy & Identity Design', 'details' => 'Full brand audit, logo redesign, and brand guidelines document', 'quantity' => 1, 'unit_price' => 4500.00],
                    ['description' => 'Social Media Setup (3 platforms)', 'details' => 'Profile optimisation, cover art, and content templates', 'quantity' => 1, 'unit_price' => 1200.00],
                ],
            ],
            // Paid invoice - last month
            [
                'client'       => $clients[1],
                'status'       => 'paid',
                'invoice_date' => Carbon::now()->subMonth()->startOfMonth(),
                'due_date'     => Carbon::now()->subMonth()->startOfMonth()->addDays(14),
                'paid_date'    => Carbon::now()->subMonth()->startOfMonth()->addDays(10),
                'currency'     => 'GHS',
                'notes'        => 'Monthly retainer - April.',
                'template'     => 'elegant',
                'primary_color' => '#0ea5e9',
                'items'        => [
                    ['description' => 'Monthly Marketing Retainer', 'details' => 'SEO management, Google Ads, monthly reporting, and strategy sessions', 'quantity' => 1, 'unit_price' => 3800.00],
                    ['description' => 'Content Creation (8 posts)', 'details' => 'Copywriting, graphics, and scheduling for LinkedIn and Instagram', 'quantity' => 8, 'unit_price' => 150.00],
                ],
            ],
            // Sent (awaiting payment) - this month
            [
                'client'       => $clients[2],
                'status'       => 'sent',
                'invoice_date' => Carbon::now()->subDays(12),
                'due_date'     => Carbon::now()->addDays(18),
                'currency'     => 'GHS',
                'notes'        => 'Payment via bank transfer or mobile money accepted.',
                'template'     => 'modern',
                'primary_color' => '#6366f1',
                'items'        => [
                    ['description' => 'Website Redesign – Phase 1', 'details' => 'UX audit, wireframes, and homepage design mockups', 'quantity' => 1, 'unit_price' => 5500.00],
                    ['description' => 'Project Management Fee', 'details' => '10% of project value', 'quantity' => 1, 'unit_price' => 550.00],
                ],
            ],
            // Overdue invoice
            [
                'client'       => $clients[3],
                'status'       => 'overdue',
                'invoice_date' => Carbon::now()->subDays(45),
                'due_date'     => Carbon::now()->subDays(15),
                'currency'     => 'GHS',
                'notes'        => 'Please note payment is now overdue. Contact us to arrange settlement.',
                'template'     => 'minimal',
                'primary_color' => '#ef4444',
                'items'        => [
                    ['description' => 'Logistics App UI Design', 'details' => 'Mobile-first UI for driver and dispatcher dashboards', 'quantity' => 1, 'unit_price' => 7200.00],
                ],
            ],
            // Partial payment
            [
                'client'       => $clients[4],
                'status'       => 'partial',
                'invoice_date' => Carbon::now()->subDays(20),
                'due_date'     => Carbon::now()->addDays(10),
                'currency'     => 'GHS',
                'notes'        => 'Deposit received. Balance due on project completion.',
                'template'     => 'vibrant',
                'primary_color' => '#8b5cf6',
                'items'        => [
                    ['description' => 'SaaS Product Discovery Sprint', 'details' => 'User research, competitive analysis, and product roadmap', 'quantity' => 1, 'unit_price' => 6000.00],
                    ['description' => 'Technical Architecture Document', 'details' => 'System design, stack recommendation, and cost projection', 'quantity' => 1, 'unit_price' => 2000.00],
                ],
            ],
            // Draft invoice
            [
                'client'       => $clients[0],
                'status'       => 'draft',
                'invoice_date' => Carbon::now(),
                'due_date'     => Carbon::now()->addDays(30),
                'currency'     => 'GHS',
                'notes'        => '',
                'template'     => 'modern',
                'primary_color' => '#6366f1',
                'items'        => [
                    ['description' => 'Monthly Retainer – May 2026', 'details' => 'SEO, paid ads management, and monthly analytics report', 'quantity' => 1, 'unit_price' => 3800.00],
                ],
            ],
        ];

        $counter = 1;
        foreach ($invoices as $data) {
            $subtotal = collect($data['items'])->sum(fn($i) => $i['quantity'] * $i['unit_price']);
            $taxRate  = 15.0;
            $taxAmount = round($subtotal * ($taxRate / 100), 2);
            $total     = $subtotal + $taxAmount;
            $amountPaid = match($data['status']) {
                'paid'    => $total,
                'partial' => round($total * 0.5, 2),
                default   => 0,
            };

            $invoice = ClientInvoice::create([
                'user_id'          => $user->id,
                'invoice_client_id' => $data['client']->id,
                'invoice_number'   => 'INV-2026-' . str_pad($counter++, 3, '0', STR_PAD_LEFT),
                'status'           => $data['status'],
                'invoice_date'     => $data['invoice_date'],
                'due_date'         => $data['due_date'],
                'paid_date'        => $data['paid_date'] ?? null,
                'currency'         => $data['currency'],
                'notes'            => $data['notes'],
                'terms'            => 'Payment is due within the specified period. Late payments may incur a 2% monthly fee.',
                'footer'           => 'Apex Digital Solutions Ltd · hello@apexdigital.gh · +233 55 000 1234',
                'template'         => $data['template'],
                'primary_color'    => $data['primary_color'],
                'accent_color'     => '#f59e0b',
                'subtotal'         => $subtotal,
                'tax_rate'         => $taxRate,
                'tax_amount'       => $taxAmount,
                'discount_amount'  => 0,
                'total_amount'     => $total,
                'amount_paid'      => $amountPaid,
                'balance_due'      => $total - $amountPaid,
                'reminder_count'   => $data['status'] === 'overdue' ? 2 : 0,
            ]);

            foreach ($data['items'] as $index => $item) {
                ClientInvoiceItem::create([
                    'client_invoice_id' => $invoice->id,
                    'description'       => $item['description'],
                    'details'           => $item['details'],
                    'quantity'          => $item['quantity'],
                    'unit_price'        => $item['unit_price'],
                    'amount'            => $item['quantity'] * $item['unit_price'],
                    'sort_order'        => $index + 1,
                ]);
            }
        }
    }

    private function seedExpenses(User $user): void
    {
        $expenses = [
            // Month -2
            ['date' => Carbon::now()->subMonths(2)->setDay(3),  'category' => 'Software & Tools',    'amount' => 420.00,  'description' => 'Adobe Creative Cloud (annual – monthly allocation)'],
            ['date' => Carbon::now()->subMonths(2)->setDay(5),  'category' => 'Office & Rent',        'amount' => 2800.00, 'description' => 'Office space rental – February'],
            ['date' => Carbon::now()->subMonths(2)->setDay(8),  'category' => 'Marketing & Ads',      'amount' => 1100.00, 'description' => 'Google Ads campaign – client acquisition'],
            ['date' => Carbon::now()->subMonths(2)->setDay(12), 'category' => 'Salaries & Payroll',   'amount' => 9500.00, 'description' => 'Staff payroll – February'],
            ['date' => Carbon::now()->subMonths(2)->setDay(15), 'category' => 'Travel & Transport',   'amount' => 380.00,  'description' => 'Client visits – Uber and fuel'],
            ['date' => Carbon::now()->subMonths(2)->setDay(18), 'category' => 'Equipment',            'amount' => 2200.00, 'description' => 'External monitor and keyboard for new hire'],
            ['date' => Carbon::now()->subMonths(2)->setDay(22), 'category' => 'Professional Services', 'amount' => 600.00, 'description' => 'Accountant – monthly bookkeeping fee'],
            ['date' => Carbon::now()->subMonths(2)->setDay(28), 'category' => 'Meals & Entertainment', 'amount' => 320.00, 'description' => 'Client lunch – Prime Realty deal closure'],

            // Month -1
            ['date' => Carbon::now()->subMonth()->setDay(2),   'category' => 'Software & Tools',     'amount' => 420.00,  'description' => 'Adobe Creative Cloud'],
            ['date' => Carbon::now()->subMonth()->setDay(5),   'category' => 'Office & Rent',         'amount' => 2800.00, 'description' => 'Office space rental – March'],
            ['date' => Carbon::now()->subMonth()->setDay(7),   'category' => 'Marketing & Ads',       'amount' => 1350.00, 'description' => 'Meta Ads – Instagram campaign'],
            ['date' => Carbon::now()->subMonth()->setDay(10),  'category' => 'Salaries & Payroll',    'amount' => 9500.00, 'description' => 'Staff payroll – March'],
            ['date' => Carbon::now()->subMonth()->setDay(14),  'category' => 'Training & Development', 'amount' => 750.00, 'description' => 'Team Figma Advanced course – 3 designers'],
            ['date' => Carbon::now()->subMonth()->setDay(17),  'category' => 'Software & Tools',      'amount' => 180.00,  'description' => 'Notion Team Plan – annual'],
            ['date' => Carbon::now()->subMonth()->setDay(20),  'category' => 'Professional Services',  'amount' => 600.00, 'description' => 'Accountant – monthly bookkeeping fee'],
            ['date' => Carbon::now()->subMonth()->setDay(25),  'category' => 'Travel & Transport',    'amount' => 210.00,  'description' => 'Travels for Tema client site visit'],

            // This month
            ['date' => Carbon::now()->setDay(2),  'category' => 'Office & Rent',         'amount' => 2800.00, 'description' => 'Office space rental – April'],
            ['date' => Carbon::now()->setDay(3),  'category' => 'Software & Tools',      'amount' => 420.00,  'description' => 'Adobe Creative Cloud'],
            ['date' => Carbon::now()->setDay(5),  'category' => 'Salaries & Payroll',    'amount' => 9500.00, 'description' => 'Staff payroll – April'],
            ['date' => Carbon::now()->setDay(8),  'category' => 'Marketing & Ads',       'amount' => 900.00,  'description' => 'LinkedIn Ads – B2B lead generation'],
            ['date' => Carbon::now()->setDay(10), 'category' => 'Professional Services', 'amount' => 600.00,  'description' => 'Accountant – monthly bookkeeping fee'],
            ['date' => Carbon::now()->setDay(12), 'category' => 'Meals & Entertainment', 'amount' => 450.00,  'description' => 'Team offsite lunch – quarterly planning'],
        ];

        foreach ($expenses as $data) {
            Expense::create([
                'user_id'          => $user->id,
                'date'             => $data['date'],
                'category'         => $data['category'],
                'amount'           => $data['amount'],
                'description'      => $data['description'],
                'is_tax_deductible' => in_array($data['category'], ['Software & Tools', 'Marketing & Ads', 'Professional Services', 'Training & Development', 'Equipment']),
                'deductible_amount' => in_array($data['category'], ['Software & Tools', 'Marketing & Ads', 'Professional Services', 'Training & Development', 'Equipment']) ? $data['amount'] : 0,
            ]);
        }
    }

    private function seedContacts(User $user): array
    {
        $contactData = [
            [
                'name'                  => 'Kofi Mensah',
                'email'                 => 'kofi@mensahgroup.com',
                'phone'                 => '+233244112233',
                'company'               => 'Mensah Group',
                'job_title'             => 'CEO',
                'type'                  => 'client',
                'status'                => 'active',
                'priority'              => 'vip',
                'relationship_strength' => 'hot',
                'lifetime_value'        => 28500,
                'deals_count'           => 4,
                'last_contact_date'     => Carbon::now()->subDays(3),
                'source'                => 'referral',
                'notes'                 => 'Long-term client. Prefers WhatsApp for quick updates.',
            ],
            [
                'name'                  => 'Ama Owusu',
                'email'                 => 'ama@brightfuturesconsult.com',
                'phone'                 => '+233200998877',
                'company'               => 'Bright Futures Consulting',
                'job_title'             => 'Managing Director',
                'type'                  => 'client',
                'status'                => 'active',
                'priority'              => 'high',
                'relationship_strength' => 'hot',
                'lifetime_value'        => 14400,
                'deals_count'           => 3,
                'last_contact_date'     => Carbon::now()->subDays(7),
                'source'                => 'linkedin',
                'notes'                 => 'On monthly retainer. Very responsive.',
            ],
            [
                'name'                  => 'James Osei',
                'email'                 => 'josei@primerealty.gh',
                'phone'                 => '+233277443322',
                'company'               => 'Prime Realty GH',
                'job_title'             => 'Head of Marketing',
                'type'                  => 'client',
                'status'                => 'active',
                'priority'              => 'high',
                'relationship_strength' => 'warm',
                'lifetime_value'        => 7200,
                'deals_count'           => 2,
                'last_contact_date'     => Carbon::now()->subDays(14),
                'source'                => 'website',
            ],
            [
                'name'                  => 'Abena Darko',
                'email'                 => 'abena@starlogistics.com',
                'phone'                 => '+233266554433',
                'company'               => 'Star Logistics Ltd',
                'job_title'             => 'Operations Director',
                'type'                  => 'client',
                'status'                => 'active',
                'priority'              => 'medium',
                'relationship_strength' => 'cold',
                'lifetime_value'        => 7200,
                'deals_count'           => 1,
                'last_contact_date'     => Carbon::now()->subDays(50),
                'source'                => 'cold_outreach',
                'notes'                 => 'Invoice overdue. Follow up urgently.',
            ],
            [
                'name'                  => 'Kweku Asante',
                'email'                 => 'k.asante@techbridgegh.io',
                'phone'                 => '+233555001122',
                'company'               => 'TechBridge Ghana',
                'job_title'             => 'Co-founder & CTO',
                'type'                  => 'client',
                'status'                => 'active',
                'priority'              => 'high',
                'relationship_strength' => 'warm',
                'lifetime_value'        => 8000,
                'deals_count'           => 1,
                'last_contact_date'     => Carbon::now()->subDays(5),
                'source'                => 'conference',
            ],
            [
                'name'                  => 'Efua Boateng',
                'email'                 => 'efua.boateng@greenwavegh.com',
                'phone'                 => '+233302445566',
                'company'               => 'GreenWave Ghana',
                'job_title'             => 'Founder',
                'type'                  => 'lead',
                'status'                => 'active',
                'priority'              => 'high',
                'relationship_strength' => 'warm',
                'lifetime_value'        => 0,
                'deals_count'           => 0,
                'last_contact_date'     => Carbon::now()->subDays(2),
                'next_follow_up_date'   => Carbon::now()->addDays(3),
                'source'                => 'referral',
                'notes'                 => 'Interested in full rebrand + website. Budget approx GHS 12k.',
            ],
            [
                'name'                  => 'Samuel Nkrumah',
                'email'                 => 'snkrumah@accelventures.vc',
                'phone'                 => '+233501122334',
                'company'               => 'Accel Ventures',
                'job_title'             => 'Investment Associate',
                'type'                  => 'investor',
                'status'                => 'active',
                'priority'              => 'vip',
                'relationship_strength' => 'warm',
                'lifetime_value'        => 0,
                'deals_count'           => 0,
                'last_contact_date'     => Carbon::now()->subDays(10),
                'next_follow_up_date'   => Carbon::now()->addDays(7),
                'source'                => 'conference',
                'notes'                 => 'Met at Ghana Tech Summit. Reviewing our pitch deck.',
            ],
        ];

        $contacts = [];
        foreach ($contactData as $data) {
            $contacts[] = Contact::firstOrCreate(
                ['user_id' => $user->id, 'email' => $data['email']],
                array_merge($data, ['user_id' => $user->id])
            );
        }

        return $contacts;
    }

    private function seedDeals(User $user, array $contacts): void
    {
        $deals = [
            [
                'contact'     => $contacts[5], // GreenWave lead
                'title'       => 'GreenWave Ghana – Full Rebrand & Website',
                'description' => 'Complete brand identity redesign, website development, and launch campaign.',
                'stage'       => 'proposal',
                'value'       => 12500.00,
                'currency'    => 'GHS',
                'probability' => 65,
                'expected_close_date' => Carbon::now()->addDays(21),
            ],
            [
                'contact'     => $contacts[2], // Prime Realty
                'title'       => 'Prime Realty – Website Redesign Phase 2',
                'description' => 'Inner pages, property listings module, and CMS setup.',
                'stage'       => 'negotiation',
                'value'       => 8800.00,
                'currency'    => 'GHS',
                'probability' => 80,
                'expected_close_date' => Carbon::now()->addDays(14),
            ],
            [
                'contact'     => $contacts[4], // TechBridge
                'title'       => 'TechBridge – SaaS MVP Design',
                'description' => 'Full product design for SaaS dashboard including onboarding and core flows.',
                'stage'       => 'qualified',
                'value'       => 15000.00,
                'currency'    => 'GHS',
                'probability' => 50,
                'expected_close_date' => Carbon::now()->addDays(30),
            ],
            [
                'contact'     => $contacts[0], // Mensah Group
                'title'       => 'Mensah Group – Q2 Retainer Renewal',
                'description' => 'Renewal of 6-month marketing retainer with expanded scope.',
                'stage'       => 'won',
                'value'       => 22800.00,
                'currency'    => 'GHS',
                'probability' => 100,
                'expected_close_date' => Carbon::now()->subDays(5),
            ],
            [
                'contact'     => $contacts[3], // Star Logistics
                'title'       => 'Star Logistics – Mobile App UI',
                'description' => 'Driver-facing mobile app UI design.',
                'stage'       => 'lead',
                'value'       => 9500.00,
                'currency'    => 'GHS',
                'probability' => 25,
                'expected_close_date' => Carbon::now()->addDays(45),
            ],
        ];

        foreach ($deals as $data) {
            Deal::create([
                'user_id'              => $user->id,
                'contact_id'           => $data['contact']->id,
                'title'                => $data['title'],
                'description'          => $data['description'],
                'stage'                => $data['stage'],
                'value'                => $data['value'],
                'currency'             => $data['currency'],
                'probability'          => $data['probability'],
                'expected_close_date'  => $data['expected_close_date'],
            ]);
        }
    }

    private function seedGoals(User $user): void
    {
        $goals = [
            [
                'title'               => 'Reach GHS 300,000 Annual Revenue',
                'description'         => 'Scale from current GHS 222k ARR to GHS 300k by end of 2026 through retainer growth and new client acquisition.',
                'type'                => 'annual',
                'category'            => 'revenue',
                'start_date'          => Carbon::now()->startOfYear(),
                'target_date'         => Carbon::now()->endOfYear(),
                'status'              => 'on_track',
                'progress_percentage' => 42,
            ],
            [
                'title'               => 'Sign 3 New Retainer Clients This Quarter',
                'description'         => 'Add 3 clients on monthly retainers of at least GHS 3,500/month.',
                'type'                => 'quarterly',
                'category'            => 'customers',
                'start_date'          => Carbon::now()->startOfQuarter(),
                'target_date'         => Carbon::now()->endOfQuarter(),
                'status'              => 'in_progress',
                'progress_percentage' => 33,
            ],
            [
                'title'               => 'Launch WhizIQ-Powered Client Reporting Feature',
                'description'         => 'Use WhizIQ to auto-generate monthly performance reports for all retainer clients.',
                'type'                => 'quarterly',
                'category'            => 'product',
                'start_date'          => Carbon::now()->startOfQuarter(),
                'target_date'         => Carbon::now()->endOfQuarter(),
                'status'              => 'in_progress',
                'progress_percentage' => 60,
            ],
            [
                'title'               => 'Reduce Outstanding Receivables by 40%',
                'description'         => 'Implement stricter payment terms and follow-up automation to reduce overdue invoices.',
                'type'                => 'monthly',
                'category'            => 'operational',
                'start_date'          => Carbon::now()->startOfMonth(),
                'target_date'         => Carbon::now()->endOfMonth(),
                'status'              => 'at_risk',
                'progress_percentage' => 15,
            ],
            [
                'title'               => 'Hire 2 Senior Creatives',
                'description'         => 'Recruit a senior UI/UX designer and a senior copywriter to support team growth.',
                'type'                => 'quarterly',
                'category'            => 'team',
                'start_date'          => Carbon::now()->startOfQuarter(),
                'target_date'         => Carbon::now()->endOfQuarter(),
                'status'              => 'not_started',
                'progress_percentage' => 0,
            ],
        ];

        foreach ($goals as $data) {
            Goal::create(array_merge($data, ['user_id' => $user->id]));
        }
    }

    private function seedTasks(User $user): void
    {
        $tasks = [
            [
                'title'       => 'Follow up with Star Logistics on overdue invoice',
                'description' => 'INV-2026-004 is 15 days overdue. Call Abena and send formal reminder email.',
                'priority'    => 'urgent',
                'status'      => 'pending',
                'due_date'    => Carbon::now()->addDay(),
            ],
            [
                'title'       => 'Send GreenWave proposal document',
                'description' => 'Finalise scope of work and pricing for the rebrand project and send to Efua.',
                'priority'    => 'high',
                'status'      => 'in_progress',
                'due_date'    => Carbon::now()->addDays(2),
            ],
            [
                'title'       => 'Prepare Q2 performance report for Mensah Group',
                'description' => 'Pull analytics from Google Ads, Meta, and SEO tools. Present in WhizIQ report format.',
                'priority'    => 'high',
                'status'      => 'pending',
                'due_date'    => Carbon::now()->addDays(5),
            ],
            [
                'title'       => 'Onboard new junior designer',
                'description' => 'Set up Figma access, Slack workspace, project briefs, and schedule intro calls.',
                'priority'    => 'medium',
                'status'      => 'in_progress',
                'due_date'    => Carbon::now()->addDays(3),
            ],
            [
                'title'       => 'Review and finalise May invoices',
                'description' => 'Check all draft invoices, apply correct tax rates, and send to clients.',
                'priority'    => 'medium',
                'status'      => 'pending',
                'due_date'    => Carbon::now()->addDays(7),
            ],
            [
                'title'       => 'Schedule quarterly strategy meeting',
                'description' => 'Book venue and send calendar invites for the Q2 team planning session.',
                'priority'    => 'low',
                'status'      => 'pending',
                'due_date'    => Carbon::now()->addDays(10),
            ],
            [
                'title'       => 'Update company LinkedIn with recent case studies',
                'description' => 'Write and design 3 case study posts from Mensah Group, TechBridge, and Prime Realty projects.',
                'priority'    => 'low',
                'status'      => 'pending',
                'due_date'    => Carbon::now()->addDays(14),
            ],
            [
                'title'       => 'Close TechBridge SaaS discovery contract',
                'description' => 'Send contract via email, follow up on signature, and collect 50% deposit.',
                'priority'    => 'high',
                'status'      => 'completed',
                'due_date'    => Carbon::now()->subDays(2),
                'completed_at' => Carbon::now()->subDay(),
            ],
        ];

        foreach ($tasks as $data) {
            Task::create(array_merge($data, [
                'user_id' => $user->id,
                'source'  => 'manual',
            ]));
        }
    }
}
