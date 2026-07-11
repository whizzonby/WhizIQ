<?php

namespace App\Filament\Dashboard\Pages;

use App\Models\AppointmentType;
use App\Models\BookingSetting;
use App\Models\BusinessProfile;
use App\Models\Contact;
use App\Models\NotificationPreference;
use App\Services\CountriesService;
use App\Services\CurrencyService;
use App\Services\EmailCampaignService;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Illuminate\Support\Str;
use Saasykit\FilamentOnboarding\Pages\OnboardingPage;

class CustomOnboardingPage extends OnboardingPage
{
    // Step 1 — Your Business
    public string $biz_registered_name = '';
    public string $biz_country = '';
    public string $currency = 'USD';
    public string $comp_tax_cycle = 'annually';

    // Step 2 — Your First Service
    public string $service_name = '';
    public int|string $service_duration = 60;
    public float|string $service_price = 0;
    public string $service_payment_type = 'invoice';
    public string $service_format = 'in_person';

    // Step 3 — Your First Client (optional)
    public string $client_name = '';
    public string $client_email = '';
    public string $client_phone = '';
    public bool $send_booking_link = false;

    // Step 4 — Stay Connected
    public string $owner_phone = '';
    public string $preferred_channel = 'sms';

    protected function getFormSchema(): array
    {
        return [
            Wizard::make([

                Step::make('Your Business')
                    ->icon('heroicon-o-building-storefront')
                    ->schema([
                        Section::make('Tell us about your business')
                            ->description('Just the essentials — you can add more detail later.')
                            ->schema([
                                TextInput::make('biz_registered_name')
                                    ->label('Business Name')
                                    ->required()
                                    ->maxLength(80)
                                    ->placeholder('e.g., Acme Consulting'),

                                Grid::make(2)->schema([
                                    Select::make('biz_country')
                                        ->label('Country')
                                        ->options(CountriesService::getAllCountries())
                                        ->required()
                                        ->searchable(),

                                    Select::make('currency')
                                        ->label('Currency')
                                        ->required()
                                        ->default('USD')
                                        ->searchable()
                                        ->options(
                                            app(CurrencyService::class)
                                                ->getAllCurrencies()
                                                ->mapWithKeys(fn ($c) => [$c->code => $c->code . ' — ' . $c->name])
                                                ->toArray()
                                        ),
                                ]),

                                Select::make('comp_tax_cycle')
                                    ->label('Tax Filing Cycle')
                                    ->required()
                                    ->default('annually')
                                    ->options([
                                        'monthly'   => 'Monthly',
                                        'quarterly' => 'Quarterly',
                                        'annually'  => 'Annually',
                                    ]),
                            ]),
                    ]),

                Step::make('Your First Service')
                    ->icon('heroicon-o-briefcase')
                    ->schema([
                        Section::make('Set up your first bookable service')
                            ->description('This turns on your booking page so clients can book you online.')
                            ->schema([
                                TextInput::make('service_name')
                                    ->label('Service Name')
                                    ->required()
                                    ->placeholder('e.g. Consultation, Haircut, 1-on-1 Session'),

                                Grid::make(2)->schema([
                                    Select::make('service_duration')
                                        ->label('Duration')
                                        ->required()
                                        ->default(60)
                                        ->options([
                                            15  => '15 minutes',
                                            30  => '30 minutes',
                                            45  => '45 minutes',
                                            60  => '60 minutes',
                                            90  => '90 minutes',
                                            120 => '2 hours',
                                        ]),

                                    TextInput::make('service_price')
                                        ->label('Price')
                                        ->numeric()
                                        ->default(0)
                                        ->minValue(0)
                                        ->prefix(fn () => $this->currency ?: 'USD')
                                        ->helperText('Set to 0 for free services'),
                                ]),

                                Grid::make(2)->schema([
                                    Select::make('service_payment_type')
                                        ->label('Payment Requirement')
                                        ->required()
                                        ->default('invoice')
                                        ->options([
                                            'none'    => 'No payment required',
                                            'invoice' => 'Invoice after booking (pay later)',
                                        ]),

                                    Select::make('service_format')
                                        ->label('Format')
                                        ->required()
                                        ->default('in_person')
                                        ->options([
                                            'in_person' => 'In-Person',
                                            'online'    => 'Online',
                                            'phone'     => 'Phone Call',
                                        ]),
                                ]),
                            ]),
                    ]),

                Step::make('Your First Client')
                    ->icon('heroicon-o-user-plus')
                    ->schema([
                        Section::make('Add a client (optional)')
                            ->description('Add one client now. You can import or add more later.')
                            ->schema([
                                TextInput::make('client_name')
                                    ->label('Client Name')
                                    ->placeholder('e.g. Jane Smith'),

                                Grid::make(2)->schema([
                                    TextInput::make('client_email')
                                        ->label('Email')
                                        ->email()
                                        ->placeholder('jane@example.com'),

                                    TextInput::make('client_phone')
                                        ->label('Phone')
                                        ->tel()
                                        ->placeholder('+1 555 000 0000'),
                                ]),

                                Toggle::make('send_booking_link')
                                    ->label('Send them your booking link by email')
                                    ->default(false)
                                    ->visible(fn () => filled($this->client_email)),
                            ]),
                    ]),

                Step::make('Stay Connected')
                    ->icon('heroicon-o-bell')
                    ->schema([
                        Section::make('Get notified instantly')
                            ->description('WhizIQ alerts you the moment a booking comes in, an invoice is overdue, or a deal goes stale.')
                            ->schema([
                                TextInput::make('owner_phone')
                                    ->label('Your Phone Number')
                                    ->tel()
                                    ->placeholder('+1 555 000 0000')
                                    ->helperText('For SMS/WhatsApp booking alerts'),

                                Select::make('preferred_channel')
                                    ->label('Preferred Alert Channel')
                                    ->default('sms')
                                    ->options([
                                        'sms'       => 'SMS',
                                        'whatsapp'  => 'WhatsApp',
                                        'both'      => 'Both SMS & WhatsApp',
                                    ])
                                    ->visible(fn () => filled($this->owner_phone)),

                                Placeholder::make('booking_url_preview')
                                    ->label('Your booking page will be live at')
                                    ->content(function () {
                                        $slug = Str::slug($this->biz_registered_name ?: auth()->user()->name);
                                        return url('book/' . $slug);
                                    }),
                            ]),
                    ]),

            ])
                ->submitAction(view('filament.components.wizard-submit-action'))
                ->columnSpanFull(),
        ];
    }

    public function submit(): void
    {
        $this->validate();

        $userId = auth()->id();
        $user   = auth()->user();

        // Step 1 — Create/update BusinessProfile
        $businessProfile = BusinessProfile::updateOrCreate(
            ['user_id' => $userId],
            [
                'biz_registered_name'  => $this->biz_registered_name,
                'biz_country'          => $this->biz_country,
                'comp_tax_cycle'       => $this->comp_tax_cycle,
                'biz_trading_name'     => null,
                'biz_tax_id'           => null,
                'biz_legal_type'       => 'sole_trader',
                'biz_incorporation_date' => now()->toDateString(),
                'ops_employee_count'   => 1,
                'ops_location'         => null,
                'ops_hours'            => null,
                'ops_systems'          => [],
                'rev_monthly_avg'      => 0,
                'rev_yoy_change'       => 0,
                'rev_payment_methods'  => [],
                'rev_channels'         => [],
                'rev_top_customers'    => [],
                'exp_fixed_monthly'    => 0,
                'exp_variable_monthly' => 0,
                'exp_payroll'          => 0,
                'exp_marketing'        => 0,
                'exp_loans'            => 0,
                'hr_full_time'         => 1,
                'hr_part_time'         => 0,
                'hr_avg_salary'        => 0,
                'hr_roles'             => [],
                'hr_contractors'       => null,
                'mkt_platforms'        => [],
                'mkt_followers'        => [],
                'mkt_budget'           => 0,
                'mkt_traffic'          => null,
                'mkt_bounce_rate'      => null,
                'comp_licenses'        => [],
                'comp_bookkeeping_type' => null,
                'comp_accountant_name' => null,
                'fin_ar_days'          => null,
                'fin_ap_days'          => null,
                'fin_bank_balance'     => null,
                'fin_debt_amount'      => null,
                'strat_goals'          => [],
                'strat_investments'    => [],
                'strat_challenges'     => [],
                'prefs_insight_freq'   => 'weekly',
                'prefs_detail_level'   => 'basic',
                'prefs_report_format'  => 'interactive',
                'prefs_ai_actions'     => true,
            ]
        );

        // Step 2 — Create BookingSetting (slug auto-generated by boot method)
        $bookingSetting = BookingSetting::firstOrCreate(
            ['user_id' => $userId],
            [
                'is_booking_enabled' => true,
                'currency'           => $this->currency,
                'display_name'       => $this->biz_registered_name,
            ]
        );

        // Step 2 — Create AppointmentType
        AppointmentType::updateOrCreate(
            [
                'user_id' => $userId,
                'name' => $this->service_name,
            ],
            [
                'duration_minutes' => (int) $this->service_duration,
                'price' => (float) $this->service_price,
                'payment_type' => $this->service_payment_type,
                'appointment_format' => $this->service_format,
                'is_active' => true,
            ]
        );

        // Step 3 — Create Contact (optional)
        if (filled($this->client_name)) {
            $contactLookup = filled($this->client_email)
                ? ['user_id' => $userId, 'email' => $this->client_email]
                : ['user_id' => $userId, 'name' => $this->client_name];

            $contact = Contact::updateOrCreate(
                $contactLookup,
                [
                    'name' => $this->client_name,
                    'email' => $this->client_email ?: null,
                    'phone' => $this->client_phone ?: null,
                    'type' => 'client',
                    'status' => 'active',
                    'priority' => 'medium',
                    'source' => 'onboarding',
                ]
            );

            if ($this->send_booking_link && filled($this->client_email)) {
                try {
                    $bookingUrl   = url('book/' . $bookingSetting->booking_slug);
                    $businessName = $this->biz_registered_name;
                    $firstName    = explode(' ', $this->client_name)[0];

                    $subject = "Your booking link from {$businessName}";
                    $body    = "<p>Hi {$firstName},</p>"
                        . "<p>{$businessName} has set up an online booking page for you.</p>"
                        . "<p><a href=\"{$bookingUrl}\">Click here to book your first appointment</a></p>"
                        . "<p>Looking forward to seeing you!</p>"
                        . "<p>{$businessName}</p>";

                    app(EmailCampaignService::class)->sendToContact($user, $contact, $subject, $body, [
                        'email_type' => 'transactional',
                        'metadata'   => ['trigger' => 'onboarding_booking_link'],
                    ]);
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::warning('Failed to send booking link to client during onboarding', [
                        'user_id'    => $userId,
                        'contact_id' => $contact->id,
                        'error'      => $e->getMessage(),
                    ]);
                }
            }
        }

        // Step 4 — Create/update NotificationPreference
        $prefs = NotificationPreference::forUser($userId);
        $prefs->update([
            'owner_phone'       => $this->owner_phone ?: null,
            'preferred_channel' => $this->preferred_channel,
        ]);

        // Calculate initial metrics
        $businessProfile->calculateMetrics();

        // Show the post-onboarding welcome modal on the dashboard
        session()->put('show_welcome_modal', true);

        $this->onboarded();
    }

    protected function onboarded(): void
    {
        $this->onboardingManager->onboarded();

        $user = auth()->user();

        if ($user && ! $user->isSubscribed()) {
            $trialPlan = \App\Models\Plan::where('is_active', true)
                ->where('has_trial', true)
                ->where('trial_interval_count', '>', 0)
                ->orderBy('id')
                ->first();

            if ($trialPlan) {
                try {
                    app(\App\Services\CheckoutService::class)
                        ->initLocalSubscriptionCheckout($trialPlan->slug);
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::warning('Auto-trial creation failed during onboarding', [
                        'user_id' => $user->id,
                        'error'   => $e->getMessage(),
                    ]);
                }
            }
        }

        $this->redirect(route('filament.dashboard.pages.dashboard'));
    }
}
