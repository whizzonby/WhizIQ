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

    protected static UnitEnum|string|null $navigationGroup = 'Settings';

    protected static bool $shouldRegisterNavigation = false;

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
                    ->icon('heroicon-o-envelope')
                    ->schema([
                        Forms\Components\TextInput::make('email_subject')
                            ->label('Subject')
                            ->maxLength(255)
                            ->placeholder('e.g. Your aftercare instructions from {{business_name}}')
                            ->required(fn ($get) => $get('send_via_email'))
                            ->columnSpanFull(),

                        Forms\Components\RichEditor::make('email_body')
                            ->label('Body')
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
                            ->extraAttributes([
                                'x-data' => '{}',
                                'x-init' => '
                                    $el.addEventListener("trix-initialize", function(e) {
                                        e.target.style.minHeight = "220px";
                                        e.target.style.resize    = "vertical";
                                        e.target.style.overflow  = "auto";
                                    });
                                ',
                            ])
                            ->columnSpanFull(),

                        Forms\Components\Toggle::make('include_rebooking_link')
                            ->label('Append rebooking button')
                            ->default(true)
                            ->live()
                            ->inline(false),

                        Forms\Components\TextInput::make('rebooking_cta_text')
                            ->label('Button text')
                            ->default('Book Your Next Appointment')
                            ->maxLength(100)
                            ->visible(fn ($get) => $get('include_rebooking_link'))
                            ->placeholder('Book Your Next Appointment'),
                    ])
                    ->visible(fn ($get) => $get('send_via_email')),

                Section::make('SMS Content')
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
                    ->visible(fn ($get) => $get('send_via_sms')),

                Section::make('WhatsApp Content')
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
                            ->columnSpanFull(),
                    ])
                    ->visible(fn ($get) => $get('send_via_whatsapp')),

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


                Section::make('Tips & Variables')
                    ->icon('heroicon-o-light-bulb')
                    ->schema([
                        Forms\Components\Placeholder::make('tips_and_vars')
                            ->label('')
                            ->content(function () {
                                $variables = (new AftercareTemplate())->getAvailableVariablesList();

                                // --- variables grid ---
                                $varHtml = '';
                                foreach ($variables as $var => $desc) {
                                    $varHtml .= '
                                        <div class="flex items-start gap-2 p-2 rounded-lg bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700">
                                            <code
                                                class="shrink-0 px-2 py-0.5 rounded bg-violet-100 dark:bg-violet-900/40 text-violet-700 dark:text-violet-300 text-xs font-mono cursor-pointer hover:bg-violet-200 dark:hover:bg-violet-800/60 transition"
                                                onclick="navigator.clipboard.writeText(\'{{' . $var . '}}\'); this.textContent=\'Copied!\'; setTimeout(()=>this.textContent=\'{{' . $var . '}}\',1200)"
                                                title="Click to copy"
                                            >{{' . $var . '}}</code>
                                            <span class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed pt-0.5">' . $desc . '</span>
                                        </div>';
                                }

                                $html = '
                                <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 overflow-hidden">

                                    <!-- Variables -->
                                    <div class="px-4 pt-4 pb-3">
                                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-400 dark:text-gray-500 mb-2">Available variables — click any to copy</p>
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-1.5">' . $varHtml . '</div>
                                    </div>

                                    <!-- Divider -->
                                    <div class="border-t border-gray-200 dark:border-gray-700"></div>

                                    <!-- Tips -->
                                    <div class="px-4 py-3 space-y-2">
                                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-400 dark:text-gray-500 mb-1">Tips</p>

                                        <div class="flex gap-2 text-xs text-gray-600 dark:text-gray-400">
                                            <span class="shrink-0 text-amber-500">✦</span>
                                            <span><strong>SMS:</strong> Keep messages under 160 characters to avoid being split into multiple SMS credits.</span>
                                        </div>

                                        <div class="flex gap-2 text-xs text-gray-600 dark:text-gray-400">
                                            <span class="shrink-0 text-amber-500">✦</span>
                                            <span><strong>WhatsApp:</strong> Supports emojis 🎉. Paste <code class="bg-gray-200 dark:bg-gray-700 px-1 rounded">{{rebooking_link}}</code> anywhere in the message to add the booking URL inline.</span>
                                        </div>

                                        <div class="flex gap-2 text-xs text-gray-600 dark:text-gray-400">
                                            <span class="shrink-0 text-amber-500">✦</span>
                                            <span><strong>Email:</strong> The rebooking button is appended automatically when the toggle above is on — no need to add the link manually.</span>
                                        </div>
                                    </div>

                                </div>';

                                return new \Illuminate\Support\HtmlString($html);
                            })
                            ->columnSpanFull(),
                    ]),
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
