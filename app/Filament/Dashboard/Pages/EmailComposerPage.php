<?php

namespace App\Filament\Dashboard\Pages;

use App\Filament\Dashboard\Resources\Subscriptions\SubscriptionResource;
use App\Models\CampaignAudience;
use App\Models\Contact;
use App\Models\EmailCampaign;
use App\Models\EmailTemplate;
use App\Services\CampaignAudienceService;
use App\Services\EmailCampaignService;
use App\Services\OpenAIService;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\DateTimePicker;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Actions\Action;
use UnitEnum;
use BackedEnum;

class EmailComposerPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-envelope';

    protected string $view = 'filament.dashboard.pages.email-composer-page';

    protected static ?string $navigationLabel = 'Send Email';

    protected static UnitEnum|string|null $navigationGroup = 'Marketing';

    protected static bool $shouldRegisterNavigation = false;

    protected static ?int $navigationSort = 2;

    protected static ?string $title = 'Compose Email';


    public ?array $data = [];
    public ?string $previewSubject = null;
    public ?string $previewBody = null;
    public bool $showPreviewModal = false;
    public ?string $campaignPreset = null;
    public ?int $campaignAudienceId = null;
    public ?int $campaignDraftId = null;
    public array $draftSegmentVariants = [];

    public function mount(): void
    {
        $draft = $this->currentDraft();
        $audience = $draft?->campaignAudience ?? $this->currentAudience();
        $this->campaignDraftId = $draft?->id;
        $this->draftSegmentVariants = $draft?->segment_variants ?? [];
        $this->campaignAudienceId = $audience?->id;
        $this->campaignPreset = $audience?->campaign_preset ?? request()->query('campaign');

        $this->form->fill(array_merge([
            'from_name' => auth()->user()->name,
            'from_email' => auth()->user()->email,
            'send_now' => true,
        ], $draft ? $this->draftPrefill($draft) : $this->getCampaignPrefill()));
    }

    protected function getCampaignPrefill(): array
    {
        if ($audience = $this->currentAudience()) {
            return app(CampaignAudienceService::class)->prefill($audience);
        }

        return match (request()->query('campaign')) {
            'calendar_gap' => $this->calendarGapPrefill(),
            'rebooking' => $this->rebookingPrefill(),
            default => [],
        };
    }

    protected function calendarGapPrefill(): array
    {
        $date = request()->query('date')
            ? \Carbon\Carbon::parse(request()->query('date'))
            : now()->addDay();

        $contactIds = Contact::where('user_id', auth()->id())
            ->where('status', 'active')
            ->where('type', 'client')
            ->whereNotNull('email')
            ->latest('last_appointment_at')
            ->limit(25)
            ->pluck('id')
            ->all();

        return [
            'contact_ids' => $contactIds,
            'subject' => 'Open appointments available on ' . $date->format('l'),
            'body' => '<p>Hi {{first_name}},</p><p>We have a few appointment times available on ' . $date->format('l, F j') . '.</p><p>If you would like to book in, you can choose a time here: <a href="' . e($this->campaignBookingUrl('calendar_gap')) . '">Book an appointment</a>.</p><p>Warm regards,<br>' . e(auth()->user()?->name) . '</p>',
            'send_now' => true,
        ];
    }

    protected function rebookingPrefill(): array
    {
        $contactIds = Contact::where('user_id', auth()->id())
            ->where('status', 'active')
            ->where('type', 'client')
            ->whereNotNull('email')
            ->whereNotNull('last_appointment_at')
            ->where('last_appointment_at', '<=', now()->subDays(45))
            ->whereDoesntHave('appointments', function ($query) {
                $query->whereIn('status', ['scheduled', 'confirmed'])
                    ->where('start_datetime', '>=', now());
            })
            ->orderBy('last_appointment_at')
            ->limit(50)
            ->pluck('id')
            ->all();

        return [
            'contact_ids' => $contactIds,
            'subject' => 'Ready to book your next visit?',
            'body' => '<p>Hi {{first_name}},</p><p>It has been a little while since your last visit, and we would love to see you again.</p><p>You can book your next appointment here: <a href="' . e($this->campaignBookingUrl('rebooking')) . '">Book your next appointment</a>.</p><p>Warm regards,<br>' . e(auth()->user()?->name) . '</p>',
            'send_now' => true,
        ];
    }

    protected function campaignBookingUrl(string $campaign): string
    {
        $bookingUrl = auth()->user()?->bookingSetting?->booking_url ?? url('/');
        $separator = str_contains($bookingUrl, '?') ? '&' : '?';

        return $bookingUrl . $separator . 'campaign=' . urlencode($campaign) . '&contact={{contact_id}}';
    }

    protected function currentAudience(): ?CampaignAudience
    {
        $slug = request()->query('audience');

        if (! $slug || ! auth()->user()) {
            return null;
        }

        return app(CampaignAudienceService::class)->findForUser(auth()->user(), $slug);
    }

    protected function currentDraft(): ?EmailCampaign
    {
        $draftId = request()->query('draft');

        if (! $draftId || ! auth()->user()) {
            return null;
        }

        return EmailCampaign::where('user_id', auth()->id())
            ->activePreparedDraft()
            ->with('campaignAudience')
            ->find($draftId);
    }

    protected function draftPrefill(EmailCampaign $draft): array
    {
        return [
            'send_to_all' => $draft->recipient_type === 'all_contacts',
            'contact_ids' => $draft->recipient_ids ?? [],
            'subject' => $draft->subject,
            'body' => $draft->body,
            'from_name' => $draft->from_name ?: auth()->user()->name,
            'from_email' => $draft->from_email ?: auth()->user()->email,
            'reply_to' => $draft->reply_to,
            'send_now' => true,
        ];
    }

    public function form(Schema $schema): Schema
    {
        // Check if user has AI email features enabled via subscription (using simple hasFeature method)
        $hasAIEmailFeatures = auth()->user()?->hasFeature('ai_email_features') ?? false;
        
        // Get subscription plans page URL
        $plansUrl = SubscriptionResource::getUrl('index');
        
        return $schema
            ->components([
                Section::make('AI Email Generation')
                    ->schema([
                        Textarea::make('ai_purpose')
                            ->label('Describe Email Purpose')
                            ->placeholder('e.g., Follow up after appointment, Welcome new client, Request testimonial, etc.')
                            ->rows(2)
                            ->helperText('Tell AI what you want to write about')
                            ->columnSpanFull(),

                        Select::make('ai_tone')
                            ->label('Tone')
                            ->options([
                                'professional' => 'Professional',
                                'friendly' => 'Friendly',
                                'casual' => 'Casual',
                                'formal' => 'Formal',
                            ])
                            ->default('professional')
                            ->disabled(!$hasAIEmailFeatures),

                        Actions::make([
                            Action::make('generate_email')
                                ->label('Generate Email with AI')
                                ->icon('heroicon-o-sparkles')
                                ->color('primary')
                                ->disabled(!$hasAIEmailFeatures)
                                ->tooltip(!$hasAIEmailFeatures ? 'Upgrade to Premium to unlock AI email generation' : null)
                                ->action(function (Set $set, Get $get) {
                                    $this->generateEmail($set, $get);
                                }),
                        ]),

                        Placeholder::make('ai_upgrade_banner')
                            ->label('')
                            ->content(new \Illuminate\Support\HtmlString(
                                '<div class="bg-gradient-to-r from-yellow-50 to-orange-50 dark:from-yellow-900/20 dark:to-orange-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
                                    <div class="flex items-start gap-3">
                                        <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                        </svg>
                                        <div class="flex-1">
                                            <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-1">
                                                🚀 Upgrade to Premium for AI Email Features
                                            </h3>
                                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">
                                                Unlock AI-powered email generation and improvements to save hours of time crafting perfect emails.
                                            </p>
                                            <a href="' . $plansUrl . '" class="inline-flex items-center px-3 py-1.5 bg-yellow-600 hover:bg-yellow-700 text-white text-sm font-medium rounded-md transition-colors">
                                                View Premium Plans
                                                <svg class="w-4 h-4 ml-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                                                </svg>
                                            </a>
                                        </div>
                                    </div>
                                </div>'
                            ))
                            ->visible(!$hasAIEmailFeatures)
                            ->columnSpanFull(),
                    ])
                    ->collapsed()
                    ->description($hasAIEmailFeatures
                        ? 'Use AI to generate email content automatically'
                        : '✨ AI Features - Premium Plan Required'),

                Section::make('Draft Improvement')
                    ->schema([
                        Placeholder::make('segment_variants')
                            ->label('Prepared Segments')
                            ->content(function (): string {
                                if (empty($this->draftSegmentVariants)) {
                                    return 'WhizIQ will use the same message for every selected recipient.';
                                }

                                return collect($this->draftSegmentVariants)
                                    ->map(fn (array $variant): string => $variant['label'] ?? 'Segment')
                                    ->unique()
                                    ->join(', ');
                            })
                            ->columnSpanFull(),

                        Select::make('draft_tone')
                            ->label('Rewrite Tone')
                            ->options([
                                'friendly' => 'Friendly',
                                'professional' => 'Professional',
                                'premium' => 'Premium',
                                'urgent' => 'Urgent',
                                'casual' => 'Casual',
                            ])
                            ->default('friendly')
                            ->disabled(!$hasAIEmailFeatures),

                        Select::make('draft_offer')
                            ->label('Offer Angle')
                            ->options([
                                'none' => 'No offer',
                                'limited_slots' => 'Limited slots',
                                'discount' => 'Discount',
                                'loyalty_reward' => 'Loyalty reward',
                                'seasonal' => 'Seasonal reminder',
                            ])
                            ->default('none')
                            ->disabled(!$hasAIEmailFeatures),

                        TextInput::make('draft_service_focus')
                            ->label('Service Focus')
                            ->placeholder('e.g., haircut, consultation, deep clean')
                            ->disabled(!$hasAIEmailFeatures),

                        Textarea::make('draft_custom_instruction')
                            ->label('Custom Direction')
                            ->placeholder('e.g., make this warmer, mention weekend availability, remove discount language')
                            ->rows(2)
                            ->disabled(!$hasAIEmailFeatures)
                            ->columnSpanFull(),

                        Actions::make([
                            Action::make('rewrite_prepared_draft')
                                ->label('Rewrite Draft')
                                ->icon('heroicon-o-pencil-square')
                                ->color('primary')
                                ->disabled(!$hasAIEmailFeatures)
                                ->tooltip(!$hasAIEmailFeatures ? 'Upgrade to Premium for AI draft rewrites' : null)
                                ->action(function (Set $set, Get $get) {
                                    $this->rewritePreparedDraft($set, $get);
                                }),

                            Action::make('stronger_subject')
                                ->label('Stronger Subject')
                                ->icon('heroicon-o-bolt')
                                ->disabled(!$hasAIEmailFeatures)
                                ->tooltip(!$hasAIEmailFeatures ? 'Upgrade to Premium for AI draft rewrites' : null)
                                ->action(function (Set $set, Get $get) {
                                    $this->rewritePreparedDraft($set, $get, 'Create a stronger subject line while keeping the body mostly unchanged.');
                                }),

                            Action::make('clearer_cta')
                                ->label('Clearer CTA')
                                ->icon('heroicon-o-cursor-arrow-rays')
                                ->disabled(!$hasAIEmailFeatures)
                                ->tooltip(!$hasAIEmailFeatures ? 'Upgrade to Premium for AI draft rewrites' : null)
                                ->action(function (Set $set, Get $get) {
                                    $this->rewritePreparedDraft($set, $get, 'Make the call to action clearer and easier to act on.');
                                }),
                        ]),
                    ])
                    ->columns(3)
                    ->collapsed(! $this->campaignDraftId)
                    ->visible(fn (): bool => (bool) $this->campaignDraftId)
                    ->description('Tune WhizIQ prepared drafts before sending.'),

Section::make('Recipients')
                    ->schema([
                        Toggle::make('send_to_all')
                            ->label('Send to All Active Contacts')
                            ->live()
                            ->helperText('Send this email to all your active contacts')
                            ->columnSpanFull(),

                        Select::make('contact_ids')
                            ->label('Select Specific Contacts')
                            ->multiple()
                            ->searchable()
                            ->options(function () {
                                return Contact::where('user_id', auth()->id())
                                    ->where('status', 'active')
                                    ->orderBy('name')
                                    ->pluck('name', 'id');
                            })
                            ->required(fn (Get $get) => $get('send_to_all') !== true)
                            ->hidden(fn (Get $get) => $get('send_to_all') === true)
                            ->helperText('Select specific contacts to send this email to'),

                        Placeholder::make('all_contacts_info')
                            ->label('All Contacts Selected')
                            ->content(function () {
                                $count = Contact::where('user_id', auth()->id())
                                    ->where('status', 'active')
                                    ->count();
                                return "This email will be sent to all {$count} active contacts.";
                            })
                            ->hidden(fn (Get $get) => $get('send_to_all') !== true)
                            ->columnSpanFull(),
                    ]),

Section::make('Email Template (Optional)')
                    ->schema([
Select::make('template_id')
                            ->label('Use Template')
                            ->options(function () {
                                return EmailTemplate::where('user_id', auth()->id())
                                    ->where('is_active', true)
                                    ->orderBy('name')
                                    ->pluck('name', 'id');
                            })
                            ->searchable()
                            ->live()
                            ->afterStateUpdated(function ($state, Set $set) {
                                if ($state) {
                                    $template = EmailTemplate::find($state);
                                    if ($template) {
                                        $set('subject', $template->subject);
                                        $set('body', $template->body);
                                    }
                                }
                            })
                            ->helperText('Load a template or compose from scratch'),
                    ]),

Section::make('Email Content')
                    ->schema([
TextInput::make('subject')
                            ->required()
                            ->maxLength(255)
                            ->helperText('Use {{variable_name}} for dynamic content'),

Actions::make([
                            Action::make('preview_email')
                                ->label('Preview Email')
                                ->icon('heroicon-o-eye')
                                ->color('gray')
                                ->action(function (Get $get) {
                                    $this->showPreview($get);
                                })
                                ->visible(fn (Get $get) => !empty($get('subject')) && !empty($get('body')) && ($get('send_to_all') || !empty($get('contact_ids')))),

                            Action::make('improve_email')
                                ->label('Improve with AI')
                                ->icon('heroicon-o-sparkles')
                                ->color('success')
                                ->disabled(!$hasAIEmailFeatures)
                                ->tooltip(!$hasAIEmailFeatures ? 'Upgrade to Premium for AI improvements' : null)
                                ->action(function (Set $set, Get $get) {
                                    $this->improveEmail($set, $get, 'improve');
                                }),

                            Action::make('shorten_email')
                                ->label('Make Shorter (AI)')
                                ->icon('heroicon-o-arrows-pointing-in')
                                ->color('warning')
                                ->disabled(!$hasAIEmailFeatures)
                                ->tooltip(!$hasAIEmailFeatures ? 'Upgrade to Premium for AI improvements' : null)
                                ->action(function (Set $set, Get $get) {
                                    $this->improveEmail($set, $get, 'shorten');
                                }),

                            Action::make('expand_email')
                                ->label('Make Longer (AI)')
                                ->icon('heroicon-o-arrows-pointing-out')
                                ->color('info')
                                ->disabled(!$hasAIEmailFeatures)
                                ->tooltip(!$hasAIEmailFeatures ? 'Upgrade to Premium for AI improvements' : null)
                                ->action(function (Set $set, Get $get) {
                                    $this->improveEmail($set, $get, 'expand');
                                }),

                            Action::make('make_professional')
                                ->label('More Professional (AI)')
                                ->icon('heroicon-o-briefcase')
                                ->disabled(!$hasAIEmailFeatures)
                                ->tooltip(!$hasAIEmailFeatures ? 'Upgrade to Premium for AI improvements' : null)
                                ->action(function (Set $set, Get $get) {
                                    $this->improveEmail($set, $get, 'professional');
                                }),

                            Action::make('make_friendly')
                                ->label('More Friendly (AI)')
                                ->icon('heroicon-o-face-smile')
                                ->disabled(!$hasAIEmailFeatures)
                                ->tooltip(!$hasAIEmailFeatures ? 'Upgrade to Premium for AI improvements' : null)
                                ->action(function (Set $set, Get $get) {
                                    $this->improveEmail($set, $get, 'friendly');
                                }),
                        ]),

RichEditor::make('body')
                            ->required()
                            ->label('Email Body')
                            ->helperText('Use {{variable_name}} for dynamic content - Variables will be replaced for each recipient')
                            ->toolbarButtons([
                                'bold',
                                'bulletList',
                                'italic',
                                'link',
                                'orderedList',
                                'redo',
                                'strike',
                                'underline',
                                'undo',
                            ])
                            ->columnSpanFull(),

Placeholder::make('available_variables')
                            ->label('Available Variables')
                            ->content(function () {
                                $variables = EmailTemplate::getAvailableVariables();
                                $list = collect($variables)
                                    ->map(fn($label, $key) => "{{" . $key . "}} - " . $label)
                                    ->take(15)
                                    ->join(", ");
                                return $list . ', and more...';
                            })
                            ->columnSpanFull(),
                    ]),

Section::make('Scheduling')
                    ->schema([
Toggle::make('send_now')
                            ->label('Send Immediately')
                            ->default(true)
                            ->live()
                            ->helperText('Turn off to schedule for later'),

DateTimePicker::make('scheduled_at')
                            ->label('Schedule For')
                            ->native(false)
                            ->seconds(false)
                            ->minDate(now())
                            ->helperText('Select when to send this email')
                            ->hidden(fn (Get $get) => $get('send_now') === true)
                            ->required(fn (Get $get) => $get('send_now') === false),
                    ])
                    ->columns(2),

Section::make('Attachments (Optional)')
                    ->schema([
Forms\Components\FileUpload::make('attachments')
                            ->label('Upload Files')
                            ->multiple()
                            ->maxFiles(5)
                            ->maxSize(10240) // 10MB per file
                            ->acceptedFileTypes([
                                'application/pdf',
                                'application/msword',
                                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                                'application/vnd.ms-excel',
                                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                'image/jpeg',
                                'image/png',
                                'image/gif',
                                'text/plain',
                                'application/zip',
                            ])
                            ->directory('email-attachments')
                            ->visibility('private')
                            ->helperText('Max 5 files, 10MB each. Supported: PDF, Word, Excel, Images, Text, ZIP'),
                    ])
                    ->collapsed()
                    ->collapsible(),

Section::make('Sender Information')
                    ->schema([
TextInput::make('from_name')
                            ->label('From Name')
                            ->maxLength(255),

TextInput::make('from_email')
                            ->label('From Email')
                            ->email()
                            ->maxLength(255),

TextInput::make('reply_to')
                            ->label('Reply To Email')
                            ->email()
                            ->maxLength(255)
                            ->helperText('Leave blank to use From Email'),
                    ])
                    ->columns(3)
                    ->collapsed(),
            ])
            ->statePath('data');
    }

    public function generateEmail(Set $set, Get $get)
    {
        $purpose = $get('ai_purpose');
        $tone = $get('ai_tone') ?? 'professional';

        if (empty($purpose)) {
            Notification::make()
                ->title('Purpose Required')
                ->body('Please describe what you want the email to be about.')
                ->warning()
                ->send();
            return;
        }

        Notification::make()
            ->title('Generating Email...')
            ->body('AI is writing your email. This may take a few seconds.')
            ->info()
            ->send();

        try {
            $openAI = app(OpenAIService::class);
            $result = $openAI->generateEmail($purpose, [], $tone);

            if ($result && isset($result['subject']) && isset($result['body'])) {
                $set('subject', $result['subject']);
                $set('body', $result['body']);

                Notification::make()
                    ->title('Email Generated!')
                    ->body('AI has generated your email. Review and customize as needed.')
                    ->success()
                    ->send();
            } else {
                throw new \Exception('Failed to generate email content');
            }
        } catch (\Exception $e) {
            Notification::make()
                ->title('Generation Failed')
                ->body('Could not generate email. Please check your OpenAI configuration.')
                ->danger()
                ->send();
        }
    }

    public function improveEmail(Set $set, Get $get, string $instruction)
    {

        $subject = $get('subject');
        $body = $get('body');

        if (empty($subject) || empty($body)) {
            Notification::make()
                ->title('Content Required')
                ->body('Please add subject and body before using AI improvements.')
                ->warning()
                ->send();
            return;
        }

        Notification::make()
            ->title('Improving Email...')
            ->body('AI is enhancing your email content.')
            ->info()
            ->send();

        try {
            $openAI = app(OpenAIService::class);
            $result = $openAI->improveEmail($subject, $body, $instruction);

            if ($result && isset($result['subject']) && isset($result['body'])) {
                $set('subject', $result['subject']);
                $set('body', $result['body']);

                Notification::make()
                    ->title('Email Improved!')
                    ->body('AI has enhanced your email content.')
                    ->success()
                    ->send();
            } else {
                throw new \Exception('Failed to improve email');
            }
        } catch (\Exception $e) {
            Notification::make()
                ->title('Improvement Failed')
                ->body('Could not improve email. Please check your OpenAI configuration.')
                ->danger()
                ->send();
        }
    }

    public function sendEmail()
    {
        $data = $this->form->getState();

        $service = app(EmailCampaignService::class);
        
        // Get contacts based on selection method
        if ($data['send_to_all'] ?? false) {
            $contacts = Contact::where('user_id', auth()->id())
                ->where('status', 'active')
                ->get();
        } else {
            $contacts = Contact::whereIn('id', $data['contact_ids'] ?? [])->get();
        }

        // Filter out contacts without email addresses
        $contactsWithEmail = $contacts->filter(fn($contact) => !empty($contact->email));
        $contactsWithoutEmail = $contacts->filter(fn($contact) => empty($contact->email));

        if ($contactsWithoutEmail->count() > 0) {
            Notification::make()
                ->title('Warning: Some contacts skipped')
                ->body("{$contactsWithoutEmail->count()} contact(s) were skipped because they don't have email addresses.")
                ->warning()
                ->send();
        }

        if ($contactsWithEmail->count() === 0) {
            Notification::make()
                ->title('No Emails Sent')
                ->body('None of the selected contacts have email addresses.')
                ->danger()
                ->send();
            return;
        }

        // Check if scheduling
        if (!$data['send_now'] && !empty($data['scheduled_at'])) {
            $this->scheduleEmail($data, $contactsWithEmail);
            return;
        }

        // Send immediately
        $sent = 0;
        $failed = 0;

        foreach ($contactsWithEmail as $contact) {
            try {
                $personalized = $this->personalizedDraftContentForContact($contact, $data['subject'], $data['body']);

                $emailLog = $service->sendToContact(
                    user: auth()->user(),
                    contact: $contact,
                    subject: $personalized['subject'],
                    body: $personalized['body'],
                    options: [
                        'from_name' => $data['from_name'] ?? null,
                        'from_email' => $data['from_email'] ?? null,
                        'reply_to' => $data['reply_to'] ?? null,
                        'attachments' => $data['attachments'] ?? null,
                        'email_campaign_id' => $this->campaignDraftId,
                        'metadata' => [
                            'campaign_preset' => $this->campaignPreset,
                            'campaign_audience_id' => $this->campaignAudienceId,
                            'campaign_segment' => $personalized['segment'],
                        ],
                    ]
                );

                if ($emailLog->status === 'sent') {
                    $sent++;
                } else {
                    $failed++;
                }
            } catch (\Exception $e) {
                $failed++;
            }
        }

        if ($sent > 0) {
            Notification::make()
                ->title('Emails Sent Successfully')
                ->body("Sent {$sent} email(s). {$failed} failed.")
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title('Failed to Send Emails')
                ->body("All {$failed} email(s) failed to send. Please check your email configuration.")
                ->danger()
                ->send();
        }

        if ($sent > 0) {
            $this->markCampaignAudienceLaunched();
            $this->markCampaignDraftReviewed('sent', [
                'sent_at' => now(),
                'total_recipients' => $contactsWithEmail->count(),
                'emails_sent' => $sent,
                'emails_failed' => $failed,
            ]);
        }

        // Reset form
        $this->form->fill([
            'send_to_all' => false,
            'contact_ids' => [],
            'template_id' => null,
            'subject' => '',
            'body' => '',
            'from_name' => auth()->user()->name,
            'from_email' => auth()->user()->email,
            'reply_to' => '',
            'send_now' => true,
            'scheduled_at' => null,
        ]);
    }

    protected function scheduleEmail(array $data, $contacts)
    {
        $service = app(EmailCampaignService::class);

        // Create scheduled email logs
        $scheduled = 0;

        foreach ($contacts as $contact) {
            try {
                $personalized = $this->personalizedDraftContentForContact($contact, $data['subject'], $data['body']);

                \App\Models\EmailLog::create([
                    'user_id' => auth()->id(),
                    'contact_id' => $contact->id,
                    'recipient_email' => $contact->email,
                    'recipient_name' => $contact->name,
                    'email_campaign_id' => $this->campaignDraftId,
                    'subject' => $personalized['subject'],
                    'body' => $personalized['body'],
                    'status' => 'scheduled',
                    'scheduled_at' => $data['scheduled_at'],
                    'email_type' => 'manual',
                    'attachments' => $data['attachments'] ?? null,
                    'metadata' => [
                        'from_name' => $data['from_name'] ?? null,
                        'from_email' => $data['from_email'] ?? null,
                        'reply_to' => $data['reply_to'] ?? null,
                        'campaign_preset' => $this->campaignPreset,
                        'campaign_audience_id' => $this->campaignAudienceId,
                        'campaign_segment' => $personalized['segment'],
                    ],
                ]);

                $scheduled++;
            } catch (\Exception $e) {
                \Log::error('Failed to schedule email', [
                    'contact_id' => $contact->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Notification::make()
            ->title('Emails Scheduled!')
            ->body("Scheduled {$scheduled} email(s) for " . \Carbon\Carbon::parse($data['scheduled_at'])->format('M d, Y \a\t g:i A'))
            ->success()
            ->send();

        if ($scheduled > 0) {
            $this->markCampaignAudienceLaunched();
            $this->markCampaignDraftReviewed('scheduled', [
                'scheduled_at' => $data['scheduled_at'],
                'total_recipients' => $scheduled,
            ]);
        }

        // Reset form
        $this->form->fill([
            'send_to_all' => false,
            'contact_ids' => [],
            'template_id' => null,
            'subject' => '',
            'body' => '',
            'from_name' => auth()->user()->name,
            'from_email' => auth()->user()->email,
            'reply_to' => '',
            'send_now' => true,
            'scheduled_at' => null,
        ]);
    }

    public function showPreview(Get $get)
    {
        $subject = $get('subject');
        $body = $get('body');
        $contactIds = $get('contact_ids');
        $sendToAll = $get('send_to_all');

        if (empty($subject) || empty($body)) {
            Notification::make()
                ->title('Missing Information')
                ->body('Please add subject and body before previewing.')
                ->warning()
                ->send();
            return;
        }

        if (!$sendToAll && empty($contactIds)) {
            Notification::make()
                ->title('Missing Information')
                ->body('Please select contacts or choose "Send to All" before previewing.')
                ->warning()
                ->send();
            return;
        }

        // Get first contact for preview
        if ($sendToAll) {
            $contact = Contact::where('user_id', auth()->id())
                ->where('status', 'active')
                ->first();
        } else {
            $contact = Contact::find($contactIds[0]);
        }

        if (!$contact) {
            Notification::make()
                ->title('Contact Not Found')
                ->body('Could not find the selected contact.')
                ->danger()
                ->send();
            return;
        }

        $service = app(EmailCampaignService::class);
        $preview = $service->previewEmail($subject, $body, $contact, auth()->user());

        $this->previewSubject = $preview['subject'];
        $this->previewBody = $preview['body'];
        $this->showPreviewModal = true;

        $this->dispatch('open-modal', id: 'email-preview');
    }

    protected function markCampaignAudienceLaunched(): void
    {
        if (! $this->campaignAudienceId) {
            return;
        }

        $audience = CampaignAudience::where('user_id', auth()->id())->find($this->campaignAudienceId);

        if ($audience) {
            app(CampaignAudienceService::class)->markLaunched($audience);
        }
    }

    protected function personalizedDraftContentForContact(Contact $contact, string $subject, string $body): array
    {
        $segment = app(CampaignAudienceService::class)->segmentForContact($contact);
        $variant = $this->draftSegmentVariants[$segment] ?? $this->draftSegmentVariants['standard'] ?? null;

        if (! $variant) {
            return [
                'segment' => $segment,
                'subject' => $subject,
                'body' => $body,
            ];
        }

        return [
            'segment' => $segment,
            'subject' => $variant['subject'] ?? $subject,
            'body' => $variant['body'] ?? $body,
        ];
    }

    public function rewritePreparedDraft(Set $set, Get $get, ?string $extraInstruction = null): void
    {
        if (! $this->campaignDraftId) {
            Notification::make()
                ->title('No Prepared Draft')
                ->body('Open a WhizIQ prepared draft before using draft rewrite prompts.')
                ->warning()
                ->send();

            return;
        }

        $subject = $get('subject');
        $body = $get('body');

        if (empty($subject) || empty($body)) {
            Notification::make()
                ->title('Content Required')
                ->body('Add subject and body before rewriting this draft.')
                ->warning()
                ->send();

            return;
        }

        Notification::make()
            ->title('Rewriting Draft...')
            ->body('WhizIQ is tuning your prepared campaign draft.')
            ->info()
            ->send();

        try {
            $context = [
                'tone' => $get('draft_tone') ?: 'friendly',
                'offer' => $get('draft_offer') ?: 'none',
                'service_focus' => $get('draft_service_focus'),
                'custom_instruction' => $get('draft_custom_instruction'),
                'extra_instruction' => $extraInstruction,
                'campaign_preset' => $this->campaignPreset,
            ];

            $result = app(OpenAIService::class)->rewriteCampaignDraft($subject, $body, $context);

            if ($result && isset($result['subject']) && isset($result['body'])) {
                $set('subject', $result['subject']);
                $set('body', $result['body']);

                $this->refreshDraftSegmentVariants($result['subject'], $result['body']);
                $this->recordDraftRewrite($context);

                Notification::make()
                    ->title('Draft Rewritten')
                    ->body('Review the updated subject and message before sending.')
                    ->success()
                    ->send();

                return;
            }

            throw new \Exception('Failed to rewrite draft');
        } catch (\Exception $e) {
            Notification::make()
                ->title('Rewrite Failed')
                ->body('Could not rewrite this draft. Please check your OpenAI configuration.')
                ->danger()
                ->send();
        }
    }

    protected function markCampaignDraftReviewed(string $status, array $updates = []): void
    {
        if (! $this->campaignDraftId) {
            return;
        }

        EmailCampaign::where('user_id', auth()->id())
            ->where('id', $this->campaignDraftId)
            ->update(array_merge($updates, [
                'status' => $status,
                'reviewed_at' => now(),
            ]));
    }

    protected function recordDraftRewrite(array $context): void
    {
        if (! $this->campaignDraftId) {
            return;
        }

        $draft = EmailCampaign::where('user_id', auth()->id())
            ->where('id', $this->campaignDraftId)
            ->with('campaignAudience')
            ->first();

        if (! $draft?->campaignAudience) {
            return;
        }

        $history = collect($draft->campaignAudience->recommendation_history ?? [])
            ->prepend([
                'event' => 'draft_rewritten',
                'label' => 'Draft Rewritten',
                'at' => now()->toIso8601String(),
                'metadata' => [
                    'campaign_id' => $draft->id,
                    'tone' => $context['tone'] ?? null,
                    'offer' => $context['offer'] ?? null,
                    'service_focus' => $context['service_focus'] ?? null,
                ],
            ])
            ->take(10)
            ->values()
            ->all();

        $draft->campaignAudience->forceFill([
            'recommendation_history' => $history,
        ])->save();
    }

    protected function refreshDraftSegmentVariants(string $subject, string $body): void
    {
        if (! $this->campaignDraftId) {
            return;
        }

        $draft = EmailCampaign::where('user_id', auth()->id())
            ->where('id', $this->campaignDraftId)
            ->with('campaignAudience')
            ->first();

        if (! $draft?->campaignAudience) {
            return;
        }

        $contacts = Contact::whereIn('id', $this->data['contact_ids'] ?? $draft->recipient_ids ?? [])
            ->where('user_id', auth()->id())
            ->get();

        if ($contacts->isEmpty()) {
            return;
        }

        $variants = app(CampaignAudienceService::class)->segmentVariantsForContacts(
            $draft->campaignAudience,
            $contacts,
            $subject,
            $body
        );

        $this->draftSegmentVariants = $variants;

        $draft->forceFill([
            'segment_variants' => $variants,
        ])->save();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('send')
                ->label('Send Email')
                ->icon('heroicon-o-paper-airplane')
                ->color('primary')
                ->requiresConfirmation()
                ->modalHeading('Send Email')
                ->modalDescription(function () {
                    $data = $this->form->getState();
                    if ($data['send_to_all'] ?? false) {
                        $count = Contact::where('user_id', auth()->id())
                            ->where('status', 'active')
                            ->count();
                        return "Are you sure you want to send this email to all {$count} active contacts?";
                    } else {
                        $count = count($data['contact_ids'] ?? []);
                        return "Are you sure you want to send this email to {$count} contact(s)?";
                    }
                })
                ->modalSubmitActionLabel('Send')
                ->action(fn () => $this->sendEmail()),
        ];
    }
}
