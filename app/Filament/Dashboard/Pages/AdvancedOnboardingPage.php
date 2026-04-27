<?php

namespace App\Filament\Dashboard\Pages;

use App\Models\BusinessProfile;
use App\Services\CountriesService;
use App\Services\DemoDataService;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Saasykit\FilamentOnboarding\Pages\OnboardingPage;

class AdvancedOnboardingPage extends OnboardingPage
{

    // Essential Business Information
    public string $biz_registered_name = '';
    public string $biz_country = '';
    public string $biz_incorporation_date = '';
    public string $biz_legal_type = '';
    public int $ops_employee_count = 1;
    public string $comp_tax_cycle = '';

    // Revenue & Preferences
    public float $rev_monthly_avg = 0;
    public string $prefs_insight_freq = 'weekly';
    public string $prefs_detail_level = 'basic';

    // Startup mode
    public string $startup_mode = 'demo';

    protected function getFormSchema(): array
    {
        return [
            Wizard::make([
                // Step 1: Business Essentials
                Step::make('Business Essentials')
                    ->icon('heroicon-o-building-storefront')
                    ->schema([
                        Section::make('Tell us about your business')
                            ->description('Just the essentials - you can add more details later in Business Metrics')
                            ->schema([
                                    TextInput::make('biz_registered_name')
                                        ->label('Business Name')
                                        ->required()
                                        ->maxLength(80)
                                        ->placeholder('e.g., Acme Consulting LLC')
                                        ->helperText('Your official business or trading name'),

                                    Grid::make(2)->schema([
                                        Select::make('biz_country')
                                            ->label('Country')
                                            ->options(CountriesService::getAllCountries())
                                            ->required()
                                            ->searchable(),

                                        Select::make('biz_legal_type')
                                            ->label('Business Structure')
                                            ->options([
                                                'sole_trader' => 'Sole Proprietor / Freelancer',
                                                'partnership' => 'Partnership',
                                                'llc' => 'Limited Liability Company (LLC)',
                                                'cooperative' => 'Co-operative',
                                                'other' => 'Other',
                                            ])
                                            ->required()
                                            ->default('sole_trader'),
                                    ]),

                                    Grid::make(3)->schema([
                                        DatePicker::make('biz_incorporation_date')
                                            ->label('Start Date')
                                            ->required()
                                            ->maxDate(now())
                                            ->default(now())
                                            ->helperText('When did you start?'),

                                        TextInput::make('ops_employee_count')
                                            ->label('Team Size')
                                            ->numeric()
                                            ->required()
                                            ->minValue(1)
                                            ->default(1)
                                            ->helperText('Including yourself'),

                                        Select::make('comp_tax_cycle')
                                            ->label('Tax Filing')
                                            ->options([
                                                'monthly' => 'Monthly',
                                                'quarterly' => 'Quarterly',
                                                'annually' => 'Annually',
                                            ])
                                            ->required()
                                            ->default('annually')
                                            ->helperText('How often do you file?'),
                                    ]),
                            ]),
                    ]),

                // Step 2: Quick Setup
                Step::make('Quick Setup')
                    ->icon('heroicon-o-cog-6-tooth')
                    ->schema([
                        Section::make('Revenue & Preferences')
                            ->description('Help us tailor WhizIQ to your needs')
                            ->schema([
                                    TextInput::make('rev_monthly_avg')
                                        ->label('Average Monthly Revenue')
                                        ->numeric()
                                        ->prefix('$')
                                        ->required()
                                        ->default(0)
                                        ->helperText('Approximate monthly sales/revenue (we\'ll help you track this better)')
                                        ->placeholder('e.g., 5000'),

                                    Grid::make(2)->schema([
                                        Select::make('prefs_insight_freq')
                                            ->label('How often do you want insights?')
                                            ->options([
                                                'daily' => 'Daily - I want regular updates',
                                                'weekly' => 'Weekly - Keep me informed',
                                                'monthly' => 'Monthly - Just the highlights',
                                            ])
                                            ->required()
                                            ->default('weekly')
                                            ->helperText('AI-powered business insights'),

                                        Select::make('prefs_detail_level')
                                            ->label('Detail level for reports')
                                            ->options([
                                                'basic' => 'Basic - Simple & clear',
                                                'advanced' => 'Advanced - All the details',
                                            ])
                                            ->required()
                                            ->default('basic')
                                            ->helperText('How detailed should reports be?'),
                                    ]),

                                Section::make('📊 What happens next?')
                                    ->schema([])
                                    ->description('
                                        **You\'re all set!** After this, you can:
                                        - Add detailed financial info in Business Metrics
                                        - Set up your appointment types and availability
                                        - Start managing contacts and deals
                                        - Track revenue and expenses

                                        *You can always update these details later.*
                                    ')
                                    ->collapsible()
                                    ->collapsed(false),
                            ]),
                    ]),

                // Step 3: Demo Data
                Step::make('Get Started')
                    ->icon('heroicon-o-rocket-launch')
                    ->schema([
                        Section::make('How would you like to start?')
                            ->description('Choose how you want your dashboard to look on day one.')
                            ->schema([
                                Radio::make('startup_mode')
                                    ->label('')
                                    ->options([
                                        'demo'    => 'Load sample data — show me a working dashboard with example clients, invoices, and tasks so I can explore everything right away.',
                                        'scratch' => 'Start fresh — I\'ll add my own data. My dashboard will be empty until I do.',
                                    ])
                                    ->default('demo')
                                    ->required()
                                    ->descriptions([
                                        'demo'    => 'You can remove the sample data at any time from the dashboard.',
                                        'scratch' => 'Great if you already have clients and want to import your own information.',
                                    ]),
                            ]),
                    ]),
            ])
                ->submitAction(view('filament.components.wizard-submit-action'))
                ->columnSpanFull(),
        ];
    }

    public function submit()
    {
        $this->validate();

        // Create or update business profile with smart defaults
        $businessProfile = BusinessProfile::updateOrCreate(
            ['user_id' => auth()->id()],
            [

            // Required fields from form
            'biz_registered_name' => $this->biz_registered_name,
            'biz_country' => $this->biz_country,
            'biz_incorporation_date' => $this->biz_incorporation_date,
            'biz_legal_type' => $this->biz_legal_type,
            'ops_employee_count' => $this->ops_employee_count,
            'comp_tax_cycle' => $this->comp_tax_cycle,
            'rev_monthly_avg' => $this->rev_monthly_avg,
            'prefs_insight_freq' => $this->prefs_insight_freq,
            'prefs_detail_level' => $this->prefs_detail_level,

            // Smart defaults for optional fields
            'biz_trading_name' => null,
            'biz_tax_id' => null,
            'ops_location' => null,
            'ops_hours' => null,
            'ops_systems' => [],

            // Revenue defaults
            'rev_yoy_change' => 0,
            'rev_payment_methods' => [],
            'rev_channels' => [],
            'rev_top_customers' => [],

            // Expense defaults (all zero - users can add later)
            'exp_fixed_monthly' => 0,
            'exp_variable_monthly' => 0,
            'exp_payroll' => 0,
            'exp_marketing' => 0,
            'exp_loans' => 0,

            // HR defaults
            'hr_full_time' => $this->ops_employee_count > 0 ? $this->ops_employee_count : 1,
            'hr_part_time' => 0,
            'hr_avg_salary' => 0,
            'hr_roles' => [],
            'hr_contractors' => null,

            // Marketing defaults
            'mkt_platforms' => [],
            'mkt_followers' => [],
            'mkt_budget' => 0,
            'mkt_traffic' => null,
            'mkt_bounce_rate' => null,

            // Compliance defaults
            'comp_licenses' => [],
            'comp_bookkeeping_type' => null,
            'comp_accountant_name' => null,

            // Financial defaults
            'fin_ar_days' => null,
            'fin_ap_days' => null,
            'fin_bank_balance' => null,
            'fin_debt_amount' => null,

            // Strategy defaults
            'strat_goals' => [],
            'strat_investments' => [],
            'strat_challenges' => [],

            // Preferences
            'prefs_report_format' => 'interactive',
            'prefs_ai_actions' => true,
            ]
        );

        // Calculate initial metrics
        $businessProfile->calculateMetrics();

        // Optionally seed demo data
        if ($this->startup_mode === 'demo') {
            $user = auth()->user();
            if ($user && ! $user->has_demo_data) {
                app(DemoDataService::class)->loadForUser($user);
            }
        }

        // Mark as onboarded and redirect based on subscription status
        $this->onboarded();
    }

    protected function onboarded()
    {
        $this->onboardingManager->onboarded();

        // Redirect based on subscription status (similar to RedirectAwareTrait logic)
        $user = auth()->user();
        
        if ($user && ! $user->isSubscribed()) {
            // User doesn't have a subscription - redirect to plans/subscriptions page
            $this->redirect(route('filament.dashboard.resources.subscriptions.index'));
        } else {
            // User has subscription or is admin - redirect to dashboard
            $this->redirect(route('filament.dashboard.pages.dashboard'));
        }
    }
}
