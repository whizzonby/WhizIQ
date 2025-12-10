<?php

namespace App\Filament\Dashboard\Resources;

use App\Filament\Dashboard\Resources\AftercareTemplateResource\Pages;
use App\Models\AftercareTemplate;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;
use BackedEnum;

class AftercareTemplateResource extends Resource
{
    protected static ?string $model = AftercareTemplate::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-heart';

    protected static ?string $navigationLabel = 'Aftercare Templates';

    protected static UnitEnum|string|null $navigationGroup = 'Booking';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Template Details')
                    ->description('Basic information about your aftercare template')
                    ->icon('heroicon-o-document-text')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Template Name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('e.g., Lash Aftercare, Facial Care Instructions')
                            ->helperText('Give this template a descriptive name that helps you identify it')
                            ->columnSpan(2),

                        Forms\Components\Textarea::make('description')
                            ->label('Description')
                            ->rows(3)
                            ->maxLength(500)
                            ->placeholder('Brief description of when and why to use this template...')
                            ->helperText('Optional: Add notes about when this template should be used')
                            ->columnSpanFull(),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Active Template')
                            ->default(true)
                            ->helperText('Only active templates will be sent to clients')
                            ->inline(false),
                    ])
                    ->columns(2),

                Section::make('Delivery Channels')
                    ->description('Choose which communication channels to use for sending aftercare messages')
                    ->icon('heroicon-o-paper-airplane')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Forms\Components\Checkbox::make('send_via_email')
                                    ->label('Email')
                                    ->default(true)
                                    ->live()
                                    ->helperText('Send via email (always available)')
                                    ->afterStateUpdated(fn ($state, $set) => $set('send_via_email', $state)),

                                Forms\Components\Checkbox::make('send_via_sms')
                                    ->label('SMS')
                                    ->live()
                                    ->helperText(function () {
                                        $hasTwilio = !empty(config('services.twilio.sid')) && !empty(config('services.twilio.token'));
                                        if (!$hasTwilio) {
                                            return 'Send via SMS - ⚠️ Twilio not configured';
                                        }
                                        return 'Send via SMS (Twilio configured)';
                                    }),

                                Forms\Components\Checkbox::make('send_via_whatsapp')
                                    ->label('WhatsApp')
                                    ->live()
                                    ->helperText(function () {
                                        // Use cached admin config (cached for 1 hour)
                                        $adminConfig = \App\Models\WhatsAppAdminConfig::getConfig();
                                        $userConfig = \App\Models\WhatsAppConfiguration::forUser(auth()->id());
                                        
                                        $adminActive = $adminConfig && $adminConfig->isConfigured() && $adminConfig->is_active;
                                        $userConnected = $userConfig && !empty($userConfig->phone_number_id) && $userConfig->is_active;
                                        
                                        if (!$adminActive) {
                                            return 'Send via WhatsApp - ⚠️ Admin has not configured WhatsApp Business API yet.';
                                        }
                                        
                                        if (!$userConnected) {
                                            return 'Send via WhatsApp - ⚠️ Connect your phone number in Settings > WhatsApp Settings.';
                                        }
                                        
                                        return 'Send via WhatsApp Business API (your phone number is connected and active)';
                                    }),
                            ]),
                    ]),

                Section::make('Email Content')
                    ->description('Compose your email message with rich formatting')
                    ->icon('heroicon-o-envelope')
                    ->schema([
                        Forms\Components\TextInput::make('email_subject')
                            ->label('Email Subject')
                            ->maxLength(255)
                            ->placeholder('Aftercare Instructions for Your {{appointment_type}}')
                            ->required(fn ($get) => $get('send_via_email'))
                            ->helperText('Use variables like {{client_first_name}} or {{appointment_type}}')
                            ->columnSpanFull(),

                        Forms\Components\RichEditor::make('email_body')
                            ->label('Email Body')
                            ->required(fn ($get) => $get('send_via_email'))
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                                'link',
                                'h2',
                                'h3',
                            ])
                            ->placeholder('Hi {{client_first_name}},

Thank you for your {{appointment_type}} appointment on {{appointment_date}}!

Here are important aftercare instructions:
• Avoid water for 24 hours
• Do not use oil-based products
• Be gentle when cleansing

We look forward to seeing you again!')
                            ->helperText('You can use HTML formatting and variables. The rebooking link will be added automatically if enabled below.')
                            ->columnSpanFull(),
                    ])
                    ->visible(fn ($get) => $get('send_via_email'))
                    ->collapsible()
                    ->collapsed(fn ($get) => !$get('send_via_email')),

                Section::make('SMS Content')
                    ->description('Compose a concise SMS message (keep it under 160 characters)')
                    ->icon('heroicon-o-chat-bubble-left')
                    ->schema([
                        Forms\Components\Textarea::make('sms_message')
                            ->label('SMS Message')
                            ->rows(4)
                            ->maxLength(500)
                            ->required(fn ($get) => $get('send_via_sms'))
                            ->placeholder('Hi {{client_first_name}}! Quick reminder: Avoid water for 24hrs after your lash appointment. Rebook: {{rebooking_link}}')
                            ->helperText(function ($get) {
                                $length = strlen($get('sms_message') ?? '');
                                $color = $length > 160 ? 'text-warning-600' : 'text-gray-600';
                                return "<span class='{$color}'>Character count: {$length} / 500 (SMS is best under 160 characters)</span>";
                            })
                            ->live()
                            ->columnSpanFull(),
                    ])
                    ->visible(fn ($get) => $get('send_via_sms'))
                    ->collapsible()
                    ->collapsed(fn ($get) => !$get('send_via_sms')),

                Section::make('WhatsApp Content')
                    ->description('Compose your WhatsApp message (supports emojis and formatting)')
                    ->icon('heroicon-o-device-phone-mobile')
                    ->schema([
                        Forms\Components\Textarea::make('whatsapp_message')
                            ->label('WhatsApp Message')
                            ->rows(6)
                            ->required(fn ($get) => $get('send_via_whatsapp'))
                            ->placeholder('Hi {{client_first_name}}! 👋

Thank you for choosing us for your {{appointment_type}}!

Here are your aftercare instructions:
• Avoid water for 24 hours 💧
• No oil-based products 🚫
• Be gentle when cleansing ✨

Book your next appointment: {{rebooking_link}}')
                            ->helperText('WhatsApp messages support emojis and basic formatting. Keep messages clear and friendly.')
                            ->columnSpanFull(),
                    ])
                    ->visible(fn ($get) => $get('send_via_whatsapp'))
                    ->collapsible()
                    ->collapsed(fn ($get) => !$get('send_via_whatsapp')),

                Section::make('Delivery Timing')
                    ->description('Configure when the aftercare message should be sent after appointment completion')
                    ->icon('heroicon-o-clock')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('send_after_days')
                                    ->label('Days')
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0)
                                    ->maxValue(30)
                                    ->suffix('days')
                                    ->helperText('0 = same day')
                                    ->live(),

                                Forms\Components\TextInput::make('send_after_hours')
                                    ->label('Hours')
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0)
                                    ->maxValue(23)
                                    ->suffix('hours')
                                    ->helperText('0-23 hours')
                                    ->live(),

                                Forms\Components\TextInput::make('send_after_minutes')
                                    ->label('Minutes')
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0)
                                    ->maxValue(59)
                                    ->suffix('minutes')
                                    ->helperText('0-59 minutes')
                                    ->live(),
                            ]),

                        Forms\Components\Placeholder::make('timing_preview')
                            ->label('Timing Summary')
                            ->content(function ($get) {
                                $days = (int)($get('send_after_days') ?? 0);
                                $hours = (int)($get('send_after_hours') ?? 0);
                                $minutes = (int)($get('send_after_minutes') ?? 0);

                                if ($days == 0 && $hours == 0 && $minutes == 0) {
                                    return new \Illuminate\Support\HtmlString(
                                        '<div class="p-3 bg-primary-50 dark:bg-primary-900/20 rounded-lg border border-primary-200 dark:border-primary-800">
                                            <div class="flex items-center gap-2 text-primary-700 dark:text-primary-300">
                                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                                                </svg>
                                                <span class="font-medium">Message will be sent immediately after appointment completion</span>
                                            </div>
                                        </div>'
                                    );
                                }

                                $parts = [];
                                if ($days > 0) $parts[] = $days . ' ' . str('day')->plural($days);
                                if ($hours > 0) $parts[] = $hours . ' ' . str('hour')->plural($hours);
                                if ($minutes > 0) $parts[] = $minutes . ' ' . str('minute')->plural($minutes);

                                $totalMinutes = ($days * 24 * 60) + ($hours * 60) + $minutes;
                                $sendTime = now()->addMinutes($totalMinutes);

                                $html = '<div class="p-3 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">';
                                $html .= '<div class="space-y-1">';
                                $html .= '<div class="flex items-center gap-2 text-gray-700 dark:text-gray-300">';
                                $html .= '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">';
                                $html .= '<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>';
                                $html .= '</svg>';
                                $html .= '<span class="font-medium">Message will be sent ' . implode(', ', $parts) . ' after completion</span>';
                                $html .= '</div>';
                                $html .= '<div class="text-sm text-gray-600 dark:text-gray-400 ml-7">';
                                $html .= 'Example: If appointment completes at 2:00 PM, message will be sent at ' . $sendTime->format('g:i A') . ' on ' . $sendTime->format('M j, Y');
                                $html .= '</div>';
                                $html .= '</div>';
                                $html .= '</div>';

                                return new \Illuminate\Support\HtmlString($html);
                            })
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),

                Section::make('Rebooking Link')
                    ->description('Add a call-to-action button to encourage clients to book their next appointment')
                    ->icon('heroicon-o-link')
                    ->schema([
                        Forms\Components\Toggle::make('include_rebooking_link')
                            ->label('Include Rebooking Link')
                            ->default(true)
                            ->live()
                            ->helperText('Add a clickable button/link in emails for clients to book their next appointment')
                            ->inline(false),

                        Forms\Components\TextInput::make('rebooking_cta_text')
                            ->label('Button Text')
                            ->default('Book Your Next Appointment')
                            ->maxLength(100)
                            ->visible(fn ($get) => $get('include_rebooking_link'))
                            ->placeholder('Book Your Next Appointment')
                            ->helperText('The text displayed on the rebooking button (emails only)')
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->collapsed(),

                Section::make('Available Variables')
                    ->description('Use these placeholders in your messages - they will be automatically replaced with actual client and appointment data')
                    ->icon('heroicon-o-variable')
                    ->schema([
                        Forms\Components\Placeholder::make('variables_list')
                            ->label('')
                            ->content(function () {
                                $template = new AftercareTemplate();
                                $variables = $template->getAvailableVariablesList();

                                $html = '<div class="space-y-2">';
                                $html .= '<p class="text-sm text-gray-600 dark:text-gray-400 mb-3">Copy and paste these variables into your message templates:</p>';
                                $html .= '<div class="grid grid-cols-1 md:grid-cols-2 gap-2">';
                                
                                foreach ($variables as $var => $description) {
                                    $html .= '<div class="p-2 bg-gray-50 dark:bg-gray-800 rounded border border-gray-200 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-700 transition">';
                                    $html .= '<div class="flex items-start gap-2">';
                                    $html .= '<code class="px-2 py-1 bg-primary-100 dark:bg-primary-900 text-primary-700 dark:text-primary-300 rounded text-sm font-mono cursor-pointer hover:bg-primary-200 dark:hover:bg-primary-800" onclick="navigator.clipboard.writeText(\'{{' . $var . '}}\')" title="Click to copy">{{' . $var . '}}</code>';
                                    $html .= '<span class="text-sm text-gray-600 dark:text-gray-400 flex-1">' . $description . '</span>';
                                    $html .= '</div>';
                                    $html .= '</div>';
                                }
                                
                                $html .= '</div>';
                                $html .= '<p class="text-xs text-gray-500 dark:text-gray-500 mt-3">💡 Tip: Click on any variable code to copy it to your clipboard</p>';
                                $html .= '</div>';

                                return new \Illuminate\Support\HtmlString($html);
                            })
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-s-check-circle')
                    ->falseIcon('heroicon-s-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray'),

                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('description')
                    ->limit(50)
                    ->toggleable()
                    ->default('—'),

                Tables\Columns\TextColumn::make('channels')
                    ->label('Channels')
                    ->formatStateUsing(fn (AftercareTemplate $record): string =>
                        $record->getActiveChannelsString()
                    )
                    ->badge()
                    ->separator(','),

                Tables\Columns\TextColumn::make('delay')
                    ->label('Send After')
                    ->formatStateUsing(fn (AftercareTemplate $record): string =>
                        $record->getDelayDescription()
                    ),

                Tables\Columns\TextColumn::make('appointmentTypes_count')
                    ->label('Used By')
                    ->counts('appointmentTypes')
                    ->suffix(' service(s)')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('M d, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('name')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status')
                    ->placeholder('All templates')
                    ->trueLabel('Active only')
                    ->falseLabel('Inactive only'),

                Tables\Filters\Filter::make('has_email')
                    ->label('With Email')
                    ->query(fn (Builder $query) => $query->where('send_via_email', true)),

                Tables\Filters\Filter::make('has_sms')
                    ->label('With SMS')
                    ->query(fn (Builder $query) => $query->where('send_via_sms', true)),

                Tables\Filters\Filter::make('has_whatsapp')
                    ->label('With WhatsApp')
                    ->query(fn (Builder $query) => $query->where('send_via_whatsapp', true)),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('No aftercare templates')
            ->emptyStateDescription('Create templates for automated aftercare messages')
            ->emptyStateIcon('heroicon-o-heart');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAftercareTemplates::route('/'),
            'create' => Pages\CreateAftercareTemplate::route('/create'),
            'edit' => Pages\EditAftercareTemplate::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('user_id', auth()->id());
    }

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::where('user_id', auth()->id())
            ->active()
            ->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }
}
