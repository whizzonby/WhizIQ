<?php

namespace App\Filament\Dashboard\Resources;

use App\Filament\Dashboard\Resources\ContactResource\Pages;
use App\Filament\Dashboard\Resources\ContactResource\RelationManagers\FollowUpRulesRelationManager;
use App\Models\Contact;
use App\Services\CountriesService;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;
use BackedEnum;

class ContactResource extends Resource
{

    protected static ?string $model = Contact::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationLabel = 'Contacts';

    protected static UnitEnum|string|null $navigationGroup = 'Clients';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Basic Information')
                    ->icon('heroicon-o-user')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('Full Name')
                                    ->required()
                                    ->maxLength(255),

                                Forms\Components\TextInput::make('company')
                                    ->label('Company')
                                    ->maxLength(255),

                                Forms\Components\TextInput::make('job_title')
                                    ->label('Job Title')
                                    ->maxLength(255),

                                Forms\Components\Select::make('type')
                                    ->options([
                                        'client' => 'Client',
                                        'lead'   => 'Lead',
                                        'other'  => 'Other',
                                    ])
                                    ->default('lead')
                                    ->native(false),

                                Forms\Components\Select::make('status')
                                    ->options([
                                        'active' => 'Active',
                                        'inactive' => 'Inactive',
                                        'archived' => 'Archived',
                                    ])
                                    ->default('active')
                                    ->native(false),

                                Forms\Components\Select::make('priority')
                                    ->options([
                                        'low' => 'Low',
                                        'medium' => 'Medium',
                                        'high' => 'High',
                                        'vip' => 'VIP',
                                    ])
                                    ->default('medium')
                                    ->native(false),
                            ]),
                    ]),

                Section::make('Contact Details')
                    ->icon('heroicon-o-phone')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('email')
                                    ->email()
                                    ->required()
                                    ->maxLength(255),

                                Forms\Components\TextInput::make('phone')
                                    ->tel()
                                    ->maxLength(255),

                                Forms\Components\TextInput::make('website')
                                    ->url()
                                    ->maxLength(255)
                                    ->prefix('https://'),
                            ]),

                        Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('linkedin_url')
                                    ->label('LinkedIn URL')
                                    ->url()
                                    ->maxLength(255)
                                    ->prefix('https://linkedin.com/in/'),

                                Forms\Components\TextInput::make('twitter_handle')
                                    ->label('Twitter Handle')
                                    ->maxLength(255)
                                    ->prefix('@'),
                            ]),
                    ])
                    ->collapsed(),

                Section::make('Address')
                    ->icon('heroicon-o-map-pin')
                    ->schema([
                        Forms\Components\Textarea::make('address')
                            ->label('Street Address')
                            ->rows(2)
                            ->columnSpanFull(),

                        Grid::make(4)
                            ->schema([
                                Forms\Components\TextInput::make('city')
                                    ->maxLength(255),

                                Forms\Components\TextInput::make('state')
                                    ->maxLength(255),

                                Forms\Components\TextInput::make('zip')
                                    ->label('ZIP/Postal')
                                    ->maxLength(255),

                                Forms\Components\Select::make('country')
                                    ->options(CountriesService::getAllCountries())
                                    ->default('US')
                                    ->native(false)
                                    ->searchable()
                                    ->placeholder('Select a country')
                                    ->helperText('Search by country name or select from the list'),
                            ]),
                    ])
                    ->collapsed(),

                Section::make('Relationship Management')
                    ->icon('heroicon-o-heart')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Forms\Components\DatePicker::make('last_contact_date')
                                    ->label('Last Contact Date'),

                                Forms\Components\DatePicker::make('next_follow_up_date')
                                    ->label('Next Follow-Up Date'),

                                Forms\Components\Select::make('relationship_strength')
                                    ->label('Relationship')
                                    ->options([
                                        'cold' => 'Cold',
                                        'warm' => 'Warm',
                                        'hot' => 'Hot',
                                    ])
                                    ->default('warm')
                                    ->native(false),
                            ]),

                        Forms\Components\TextInput::make('source')
                            ->label('Lead Source')
                            ->maxLength(255)
                            ->placeholder('e.g., referral, website, event'),

                        Forms\Components\TagsInput::make('tags')
                            ->label('Tags')
                            ->placeholder('Add tags'),
                    ])
                    ->collapsed(),

                Section::make('Business Value')
                    ->icon('heroicon-o-currency-dollar')
                    ->schema([
                        Forms\Components\TextInput::make('lifetime_value')
                            ->label('Lifetime Value')
                            ->numeric()
                            ->prefix('$')
                            ->default(0)
                            ->disabled()
                            ->helperText('Auto-calculated from won deals'),
                    ])
                    ->collapsed(),

                Section::make('Notes')
                    ->icon('heroicon-o-document-text')
                    ->schema([
                        Forms\Components\Textarea::make('notes')
                            ->rows(4)
                            ->columnSpanFull(),
                    ])
                    ->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\IconColumn::make('is_blocked')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-s-shield-exclamation')
                    ->falseIcon('heroicon-s-check-circle')
                    ->trueColor('danger')
                    ->falseColor('success')
                    ->getStateUsing(function (Contact $record): bool {
                        // Check both is_blocked field and active BlockedClient records
                        $hasActiveBlocks = \App\Models\BlockedClient::where('contact_id', $record->id)
                            ->active()
                            ->exists();
                        return $record->is_blocked || $hasActiveBlocks;
                    })
                    ->tooltip(function (Contact $record): string {
                        $hasActiveBlocks = \App\Models\BlockedClient::where('contact_id', $record->id)
                            ->active()
                            ->exists();
                        
                        if ($record->is_blocked || $hasActiveBlocks) {
                            $activeBlocksCount = \App\Models\BlockedClient::where('contact_id', $record->id)
                                ->active()
                                ->count();
                            
                            $tooltip = 'Blocked';
                            if ($record->blocked_reason) {
                                $tooltip .= ': ' . $record->blocked_reason;
                            }
                            if ($activeBlocksCount > 0) {
                                $tooltip .= " ({$activeBlocksCount} active violation" . ($activeBlocksCount > 1 ? 's' : '') . ")";
                            }
                            return $tooltip;
                        }
                        return 'Active - Can book appointments';
                    }),

                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->description(fn (Contact $record): ?string =>
                        $record->is_blocked 
                            ? '⚠️ Blocked: ' . ($record->blocked_reason ?? 'No reason specified')
                            : null
                    )
                    ->color(fn (Contact $record): ?string =>
                        $record->is_blocked ? 'danger' : null
                    ),

                Tables\Columns\TextColumn::make('company')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('job_title')
                    ->label('Title')
                    ->toggleable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\BadgeColumn::make('type')
                    ->colors([
                        'success'   => 'client',
                        'primary'   => 'lead',
                        'secondary' => 'other',
                    ]),

                Tables\Columns\BadgeColumn::make('priority')
                    ->colors([
                        'danger' => 'vip',
                        'warning' => 'high',
                        'primary' => 'medium',
                        'secondary' => 'low',
                    ]),

                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->copyable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('phone')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('country_name')
                    ->label('Country')
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('last_contact_date')
                    ->label('Last Contact')
                    ->date()
                    ->sortable()
                    ->color(fn ($state) => $state && $state->diffInDays(now()) > 30 ? 'danger' : null),

                Tables\Columns\TextColumn::make('next_follow_up_date')
                    ->label('Next Follow-Up')
                    ->date()
                    ->sortable()
                    ->color(fn ($state) => $state && $state->isPast() ? 'danger' : 'success'),

                Tables\Columns\BadgeColumn::make('relationship_strength')
                    ->label('Relationship')
                    ->colors([
                        'danger' => 'cold',
                        'warning' => 'warm',
                        'success' => 'hot',
                    ])
                    ->toggleable(),

                Tables\Columns\TextColumn::make('lifetime_value')
                    ->label('LTV')
                    ->money('USD')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('deals_count')
                    ->label('Deals')
                    ->counts('deals')
                    ->sortable()
                    ->toggleable(),
            ])
            ->defaultSort('last_contact_date', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'client' => 'Client',
                        'lead'   => 'Lead',
                        'other'  => 'Other',
                    ])
                    ->multiple(),

                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                        'archived' => 'Archived',
                    ]),

                Tables\Filters\SelectFilter::make('priority')
                    ->options([
                        'vip' => 'VIP',
                        'high' => 'High',
                        'medium' => 'Medium',
                        'low' => 'Low',
                    ]),

                Tables\Filters\Filter::make('needs_follow_up')
                    ->label('Needs Follow-Up')
                    ->query(fn (Builder $query) => $query->needsFollowUp()),

                Tables\Filters\Filter::make('overdue')
                    ->label('Overdue Follow-Up')
                    ->query(fn (Builder $query) => $query->where('next_follow_up_date', '<', now())->where('status', 'active')),
            ])
            ->actions([
                EditAction::make(),

                Action::make('log_interaction')
                    ->label('Log Interaction')
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->color('primary')
                    ->form([
                        Forms\Components\Select::make('type')
                            ->options([
                                'call' => 'Call',
                                'email' => 'Email',
                                'meeting' => 'Meeting',
                                'note' => 'Note',
                                'demo' => 'Demo',
                            ])
                            ->default('note')
                            ->native(false)
                            ->required(),

                        Forms\Components\TextInput::make('subject')
                            ->maxLength(255),

                        Forms\Components\Textarea::make('description')
                            ->required()
                            ->rows(3),

                        Forms\Components\DateTimePicker::make('interaction_date')
                            ->label('Date & Time')
                            ->default(now())
                            ->required(),

                        Forms\Components\TextInput::make('duration_minutes')
                            ->label('Duration (minutes)')
                            ->numeric()
                            ->suffix('min'),

                        Forms\Components\Select::make('outcome')
                            ->options([
                                'positive' => 'Positive',
                                'neutral' => 'Neutral',
                                'negative' => 'Negative',
                                'follow_up_needed' => 'Follow-Up Needed',
                            ])
                            ->native(false),
                    ])
                    ->action(function (Contact $record, array $data) {
                        try {
                            // Validate required fields
                            if (empty($data['type'])) {
                                throw new \InvalidArgumentException('Interaction type is required.');
                            }
                            
                            if (empty($data['description'])) {
                                throw new \InvalidArgumentException('Description is required.');
                            }

                            // Parse and validate date
                            $interactionDate = null;
                            if (!empty($data['interaction_date'])) {
                                try {
                                    $interactionDate = Carbon::parse($data['interaction_date']);
                                } catch (\Exception $e) {
                                    throw new \InvalidArgumentException('Invalid date format provided.');
                                }
                            }

                            $record->logInteraction(
                                type: $data['type'],
                                description: $data['description'],
                                interactionDate: $interactionDate,
                                subject: $data['subject'] ?? null,
                                durationMinutes: $data['duration_minutes'] ?? null,
                                outcome: $data['outcome'] ?? null
                            );

                            Notification::make()
                                ->title('Interaction Logged')
                                ->body('The interaction has been successfully logged for this contact.')
                                ->success()
                                ->send();
                        } catch (\InvalidArgumentException $e) {
                            Notification::make()
                                ->title('Validation Error')
                                ->body($e->getMessage())
                                ->warning()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Error Logging Interaction')
                                ->body('An unexpected error occurred: ' . $e->getMessage())
                                ->danger()
                                ->send();
                            
                            // Log the full error for debugging
                            \Log::error('Failed to log contact interaction', [
                                'contact_id' => $record->id,
                                'data' => $data,
                                'error' => $e->getMessage(),
                                'trace' => $e->getTraceAsString()
                            ]);
                        }
                    }),

                Action::make('send_email')
                    ->label('Send Email')
                    ->icon('heroicon-o-envelope')
                    ->color('info')
                    ->visible(fn (Contact $record) => !empty($record->email))
                    ->form([
                        Forms\Components\Select::make('template_id')
                            ->label('Email Template (Optional)')
                            ->options(fn () => \App\Models\EmailTemplate::where('user_id', auth()->id())->pluck('name', 'id'))
                            ->searchable()
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state) {
                                    $template = \App\Models\EmailTemplate::find($state);
                                    if ($template) {
                                        $set('subject', $template->subject);
                                        $set('body', $template->body);
                                    }
                                }
                            }),

                        Forms\Components\TextInput::make('subject')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Email subject'),

                        Forms\Components\RichEditor::make('body')
                            ->label('Message')
                            ->required()
                            ->columnSpanFull()
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                                'link',
                            ]),

                        Forms\Components\Toggle::make('log_interaction')
                            ->label('Log as interaction')
                            ->default(true)
                            ->helperText('Create an interaction record for this email'),
                    ])
                    ->action(function (Contact $record, array $data) {
                        try {
                            // Send email
                            \Mail::to($record->email)->send(new \App\Mail\ContactEmail(
                                emailSubject: $data['subject'],
                                body: $data['body'],
                                contactName: $record->name
                            ));

                            // Log email
                            \App\Models\EmailLog::create([
                                'user_id' => auth()->id(),
                                'contact_id' => $record->id,
                                'recipient_email' => $record->email,
                                'subject' => $data['subject'],
                                'body' => $data['body'],
                                'status' => 'sent',
                                'sent_at' => now(),
                            ]);

                            // Log as interaction
                            if ($data['log_interaction'] ?? true) {
                                $record->logInteraction(
                                    type: 'email',
                                    description: $data['body'],
                                    subject: $data['subject'],
                                    outcome: 'positive'
                                );
                            }

                            Notification::make()
                                ->title('Email Sent')
                                ->success()
                                ->body("Email sent to {$record->name}")
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Email Failed')
                                ->danger()
                                ->body('Failed to send email: ' . $e->getMessage())
                                ->send();
                        }
                    }),

                Action::make('schedule_follow_up')
                    ->label('Schedule Follow-Up')
                    ->icon('heroicon-o-calendar')
                    ->color('warning')
                    ->form([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('e.g., Send proposal'),

                        Forms\Components\Textarea::make('description')
                            ->rows(3),

                        Forms\Components\DateTimePicker::make('remind_at')
                            ->label('Remind Me On')
                            ->default(now()->addDays(7))
                            ->required(),

                        Forms\Components\Select::make('priority')
                            ->options([
                                'low' => 'Low',
                                'medium' => 'Medium',
                                'high' => 'High',
                            ])
                            ->default('medium')
                            ->native(false)
                            ->required(),
                    ])
                    ->action(function (Contact $record, array $data) {
                        try {
                            // Validate required fields
                            if (empty($data['title'])) {
                                throw new \InvalidArgumentException('Follow-up title is required.');
                            }
                            
                            if (empty($data['remind_at'])) {
                                throw new \InvalidArgumentException('Reminder date is required.');
                            }

                            // Parse and validate date
                            try {
                                $remindAt = Carbon::parse($data['remind_at']);
                            } catch (\Exception $e) {
                                throw new \InvalidArgumentException('Invalid date format provided.');
                            }

                            $record->scheduleFollowUp(
                                date: $remindAt,
                                title: $data['title'],
                                description: $data['description'] ?? null,
                                priority: $data['priority']
                            );

                            Notification::make()
                                ->title('Follow-Up Scheduled')
                                ->success()
                                ->body("Reminder set for {$remindAt->format('M d, Y g:i A')}")
                                ->send();
                        } catch (\InvalidArgumentException $e) {
                            Notification::make()
                                ->title('Validation Error')
                                ->body($e->getMessage())
                                ->warning()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Error Scheduling Follow-Up')
                                ->body('An unexpected error occurred: ' . $e->getMessage())
                                ->danger()
                                ->send();
                            
                            // Log the full error for debugging
                            \Log::error('Failed to schedule follow-up', [
                                'contact_id' => $record->id,
                                'data' => $data,
                                'error' => $e->getMessage(),
                                'trace' => $e->getTraceAsString()
                            ]);
                        }
                    }),

                Action::make('block_client')
                    ->label('Block Client')
                    ->icon('heroicon-o-shield-exclamation')
                    ->color('danger')
                    ->visible(function (Contact $record) {
                        // Check if contact is blocked OR has active BlockedClient records
                        $hasActiveBlocks = \App\Models\BlockedClient::where('contact_id', $record->id)
                            ->active()
                            ->exists();
                        return !$record->is_blocked && !$hasActiveBlocks;
                    })
                    ->form([
                        Forms\Components\Select::make('violation_type')
                            ->label('Violation Type')
                            ->options([
                                'no_show' => 'No Show',
                                'late_cancellation' => 'Late Cancellation',
                                'repeated_reschedule' => 'Repeated Reschedule',
                                'inappropriate_behavior' => 'Inappropriate Behavior',
                                'payment_issue' => 'Payment Issue',
                                'other' => 'Other',
                            ])
                            ->required()
                            ->native(false),

                        Forms\Components\Textarea::make('reason')
                            ->label('Reason for Blocking')
                            ->required()
                            ->rows(3)
                            ->helperText('Provide detailed reason for blocking this client'),

                        Forms\Components\Toggle::make('auto_block')
                            ->label('Block immediately')
                            ->default(true)
                            ->helperText('If checked, client will be blocked from booking appointments'),
                    ])
                    ->action(function (Contact $record, array $data) {
                        $record->logViolation(
                            $data['violation_type'],
                            $data['reason'],
                            $data['auto_block'] ?? true
                        );

                        // Refresh the record to get updated is_blocked status
                        $record->refresh();

                        Notification::make()
                            ->title('Client Blocked')
                            ->body("$record->name has been blocked from booking appointments.")
                            ->warning()
                            ->send();
                    })
                    ->successNotificationTitle('Client blocked successfully')
                    ->after(function () {
                        // Filament will automatically refresh, but we ensure it happens
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Block Client from Bookings')
                    ->modalDescription('This will prevent the client from booking new appointments.')
                    ->modalSubmitActionLabel('Block Client'),

                Action::make('resolve_block')
                    ->label('Resolve Block')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(function (Contact $record) {
                        // Show if contact is blocked OR has active BlockedClient records
                        $hasActiveBlocks = \App\Models\BlockedClient::where('contact_id', $record->id)
                            ->active()
                            ->exists();
                        return $record->is_blocked || $hasActiveBlocks;
                    })
                    ->form([
                        Forms\Components\Textarea::make('resolution_notes')
                            ->label('Resolution Notes')
                            ->required()
                            ->rows(3)
                            ->helperText('Explain how this issue was resolved. This will resolve all active blocks for this client.'),
                    ])
                    ->action(function (Contact $record, array $data) {
                        // Resolve all active BlockedClient records for this contact
                        $activeBlocks = \App\Models\BlockedClient::where('contact_id', $record->id)
                            ->active()
                            ->get();

                        foreach ($activeBlocks as $block) {
                            $block->resolve($data['resolution_notes']);
                        }

                        // Unblock the contact
                        $record->unblock($data['resolution_notes']);

                        // Refresh the record
                        $record->refresh();

                        $blockCount = $activeBlocks->count();
                        Notification::make()
                            ->title('Block Resolved')
                            ->body($blockCount > 0 
                                ? "Resolved {$blockCount} block(s) and unblocked {$record->name}. They can now book appointments."
                                : "Unblocked {$record->name}. They can now book appointments.")
                            ->success()
                            ->send();
                    })
                    ->successNotificationTitle('Block resolved successfully')
                    ->requiresConfirmation()
                    ->modalHeading('Resolve Block')
                    ->modalDescription('This will resolve all active blocks for this client and allow them to book appointments again.')
                    ->modalSubmitActionLabel('Resolve Block'),

                Action::make('unblock_client')
                    ->label('Unblock Client')
                    ->icon('heroicon-o-lock-open')
                    ->color('gray')
                    ->visible(function (Contact $record) {
                        // Show if contact is blocked OR has active BlockedClient records
                        $hasActiveBlocks = \App\Models\BlockedClient::where('contact_id', $record->id)
                            ->active()
                            ->exists();
                        return $record->is_blocked || $hasActiveBlocks;
                    })
                    ->form([
                        Forms\Components\Textarea::make('resolution_notes')
                            ->label('Resolution Notes')
                            ->rows(3)
                            ->helperText('Document why this client is being unblocked (without resolving violation records)'),
                    ])
                    ->action(function (Contact $record, array $data) {
                        $record->unblock($data['resolution_notes'] ?? null);

                        // Refresh the record to get updated is_blocked status
                        $record->refresh();

                        Notification::make()
                            ->title('Client Unblocked')
                            ->body("$record->name can now book appointments. Note: Violation records remain active.")
                            ->success()
                            ->send();
                    })
                    ->successNotificationTitle('Client unblocked successfully')
                    ->after(function () {
                        // Filament will automatically refresh, but we ensure it happens
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Unblock Client')
                    ->modalDescription('This will allow the client to book appointments again, but violation records will remain active.')
                    ->modalSubmitActionLabel('Unblock Client'),

                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelationManagers(): array
    {
        return [
            FollowUpRulesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListContacts::route('/'),
            'create' => Pages\CreateContact::route('/create'),
            'edit'   => Pages\EditContact::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('user_id', auth()->id());
    }

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::where('user_id', auth()->id())
            ->needsFollowUp()
            ->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }

    /**
     * Check if user can create more contacts based on their plan
     * Returns false if no subscription - user will get 403 when trying to create
     */
    public static function canCreate(): bool
    {
        return auth()->user()?->canCreate(Contact::class, 'crm_contacts_limit') ?? false;
    }
}
