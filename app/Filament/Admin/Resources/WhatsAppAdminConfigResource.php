<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\WhatsAppAdminConfigResource\Pages;
use App\Models\WhatsAppAdminConfig;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Illuminate\Database\Eloquent\Builder;

class WhatsAppAdminConfigResource extends Resource
{
    protected static ?string $model = WhatsAppAdminConfig::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-device-phone-mobile';

    protected static ?string $navigationLabel = 'WhatsApp Configuration';

    public static function getNavigationGroup(): ?string
    {
        return __('Settings');
    }

    protected static ?int $navigationSort = 5;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Setup Instructions
                Section::make('Setup Instructions')
                    ->description('Follow these steps to configure WhatsApp Business API')
                    ->icon('heroicon-o-book-open')
                    ->schema([
                        Forms\Components\Placeholder::make('instructions')
                            ->label('')
                            ->content(function () {
                                $html = '<div class="space-y-2 text-sm">';
                                $html .= '<p class="font-medium mb-2">To get your WhatsApp Business API credentials:</p>';
                                $html .= '<ol class="list-decimal list-inside space-y-1 ml-4">';
                                $html .= '<li>Go to <a href="https://developers.facebook.com" target="_blank" class="text-primary-600 dark:text-primary-400 hover:underline">Meta for Developers</a></li>';
                                $html .= '<li>Create a Meta App and add the WhatsApp product</li>';
                                $html .= '<li>Get your Business Account ID from the app dashboard</li>';
                                $html .= '<li>Generate a permanent Access Token (System User Token)</li>';
                                $html .= '<li>Copy the Webhook URL below and configure it in Meta for Developers</li>';
                                $html .= '<li>Enter your credentials in the form below</li>';
                                $html .= '</ol>';
                                $html .= '</div>';
                                return new \Illuminate\Support\HtmlString($html);
                            })
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->collapsed(false),

                // API Credentials
                Section::make('API Credentials')
                    ->description('Enter your WhatsApp Business API credentials from Meta for Developers')
                    ->icon('heroicon-o-key')
                    ->schema([
                        Forms\Components\TextInput::make('business_account_id')
                            ->label('Business Account ID')
                            ->required(fn ($get) => $get('is_active'))
                            ->maxLength(255)
                            ->placeholder('123456789012345')
                            ->helperText('Your WhatsApp Business Account ID from Meta for Developers dashboard')
                            ->columnSpan(2),

                        Forms\Components\TextInput::make('api_version')
                            ->label('API Version')
                            ->default('v21.0')
                            ->maxLength(10)
                            ->helperText('WhatsApp Business API version (usually v21.0)')
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('access_token')
                            ->label('Access Token')
                            ->required(fn ($get) => $get('is_active'))
                            ->helperText('Your permanent System User Access Token from Meta for Developers. This will be encrypted for security.')
                            ->password()
                            ->revealable()
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('verify_token')
                            ->label('Webhook Verify Token')
                            ->maxLength(255)
                            ->placeholder('your-secret-verify-token')
                            ->helperText('A secret token you create for webhook verification (optional but recommended)')
                            ->columnSpan(2),

                        Forms\Components\TextInput::make('app_secret')
                            ->label('App Secret')
                            ->helperText('Your app secret from Meta for Developers (optional, for enhanced webhook verification). This will be encrypted.')
                            ->password()
                            ->revealable()
                            ->columnSpan(2),
                    ])
                    ->columns(3),

                // Webhook Configuration
                Section::make('Webhook Configuration')
                    ->description('Configure webhook in Meta for Developers')
                    ->icon('heroicon-o-link')
                    ->schema([
                        Forms\Components\TextInput::make('webhook_url')
                            ->label('Webhook URL')
                            ->formatStateUsing(fn () => route('webhooks.whatsapp'))
                            ->helperText('Copy this URL and paste it in Meta for Developers > WhatsApp > Configuration > Webhook > Callback URL')
                            ->readOnly()
                            ->dehydrated(false)
                            ->extraInputAttributes(['onclick' => 'this.select()'])
                            ->columnSpanFull(),

                        Forms\Components\Placeholder::make('webhook_info')
                            ->label('')
                            ->content(function () {
                                $html = '<div class="p-3 bg-info-50 dark:bg-info-900/20 rounded-lg border border-info-200 dark:border-info-800">';
                                $html .= '<p class="text-sm text-info-700 dark:text-info-300">';
                                $html .= '<strong>Webhook Setup:</strong> After entering your credentials above, go to Meta for Developers, ';
                                $html .= 'navigate to your app > WhatsApp > Configuration, and set the Callback URL to the URL shown above. ';
                                $html .= 'Use the Verify Token you entered above when Meta asks for it.';
                                $html .= '</p>';
                                $html .= '</div>';
                                return new \Illuminate\Support\HtmlString($html);
                            })
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->collapsed(true),

                // Enable Integration
                Section::make('Enable Integration')
                    ->description('Activate WhatsApp integration for all users')
                    ->icon('heroicon-o-power')
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->label('Enable WhatsApp Integration')
                            ->helperText('When enabled, all users will be able to connect their phone numbers and send WhatsApp messages through aftercare templates.')
                            ->default(false)
                            ->live()
                            ->columnSpanFull(),

                        Forms\Components\Placeholder::make('enable_warning')
                            ->label('')
                            ->content(function ($get) {
                                $isActive = $get('is_active');
                                $hasCredentials = !empty($get('business_account_id')) && !empty($get('access_token'));
                                
                                if ($isActive && $hasCredentials) {
                                    $html = '<div class="p-3 bg-success-50 dark:bg-success-900/20 rounded-lg border border-success-200 dark:border-success-800">';
                                    $html .= '<p class="text-sm text-success-700 dark:text-success-300">✓ Integration will be enabled. Users can now connect their phone numbers.</p>';
                                    $html .= '</div>';
                                } else {
                                    $html = '<div class="p-3 bg-warning-50 dark:bg-warning-900/20 rounded-lg border border-warning-200 dark:border-warning-800">';
                                    $html .= '<p class="text-sm text-warning-700 dark:text-warning-300">⚠️ Please enter Business Account ID and Access Token before enabling.</p>';
                                    $html .= '</div>';
                                }
                                return new \Illuminate\Support\HtmlString($html);
                            })
                            ->columnSpanFull()
                            ->visible(fn ($get) => $get('is_active') !== null),
                    ]),

                // Configuration Status
                Section::make('Configuration Status')
                    ->description('Current status of your WhatsApp Business API configuration')
                    ->icon('heroicon-o-check-circle')
                    ->schema([
                        Forms\Components\Placeholder::make('status')
                            ->label('Status')
                            ->content(function ($record) {
                                return static::getStatusHtml($record);
                            })
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->collapsed(true),
            ]);
    }

    protected static function getStatusHtml($record): \Illuminate\Support\HtmlString
    {
        if (!$record || !$record->isConfigured()) {
            $html = '<div class="p-4 bg-gray-50 dark:bg-gray-800 rounded-lg border-2 border-gray-200 dark:border-gray-700">';
            $html .= '<div class="flex items-start gap-3">';
            $html .= '<svg class="w-6 h-6 text-gray-500 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path></svg>';
            $html .= '<div class="flex-1">';
            $html .= '<h3 class="font-semibold text-gray-900 dark:text-gray-100 mb-1">Not Configured</h3>';
            $html .= '<p class="text-sm text-gray-600 dark:text-gray-400">Please enter your WhatsApp Business API credentials above to enable WhatsApp for all users.</p>';
            $html .= '</div>';
            $html .= '</div>';
            $html .= '</div>';
            return new \Illuminate\Support\HtmlString($html);
        }
        
        if (!$record->is_active) {
            $html = '<div class="p-4 bg-warning-50 dark:bg-warning-900/20 rounded-lg border-2 border-warning-300 dark:border-warning-700">';
            $html .= '<div class="flex items-start gap-3">';
            $html .= '<svg class="w-6 h-6 text-warning-600 dark:text-warning-400 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>';
            $html .= '<div class="flex-1">';
            $html .= '<h3 class="font-semibold text-warning-900 dark:text-warning-100 mb-1">Configured but Inactive</h3>';
            $html .= '<p class="text-sm text-warning-700 dark:text-warning-300">Your credentials are saved but WhatsApp integration is disabled. Enable the toggle above to allow users to connect their phone numbers.</p>';
            $html .= '</div>';
            $html .= '</div>';
            $html .= '</div>';
            return new \Illuminate\Support\HtmlString($html);
        }
        
        $configuredAt = 'Not configured yet';
        if ($record->configured_at) {
            try {
                $configuredAt = $record->configured_at instanceof \Carbon\Carbon 
                    ? $record->configured_at->format('M j, Y g:i A')
                    : \Carbon\Carbon::parse($record->configured_at)->format('M j, Y g:i A');
            } catch (\Exception $e) {
                $configuredAt = 'Recently';
            }
        }
                
        $html = '<div class="p-4 bg-success-50 dark:bg-success-900/20 rounded-lg border-2 border-success-300 dark:border-success-700">';
        $html .= '<div class="flex items-start gap-3">';
        $html .= '<svg class="w-6 h-6 text-success-600 dark:text-success-400 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>';
        $html .= '<div class="flex-1">';
        $html .= '<h3 class="font-semibold text-success-900 dark:text-success-100 mb-1">Active and Configured ✓</h3>';
        $html .= '<p class="text-sm text-success-700 dark:text-success-300 mb-2">WhatsApp Business API is configured and active. Users can now connect their phone numbers and send WhatsApp messages.</p>';
        $html .= '<p class="text-xs text-success-600 dark:text-success-400">Configured: ' . $configuredAt . '</p>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';
        return new \Illuminate\Support\HtmlString($html);
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

                Tables\Columns\TextColumn::make('business_account_id')
                    ->label('Business Account ID')
                    ->searchable()
                    ->limit(20),

                Tables\Columns\TextColumn::make('api_version')
                    ->label('API Version')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('configured_at')
                    ->label('Configured At')
                    ->dateTime('M d, Y g:i A')
                    ->sortable()
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('last_verified_at')
                    ->label('Last Verified')
                    ->dateTime('M d, Y g:i A')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->placeholder('—'),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                EditAction::make()
                    ->label('Configure')
                    ->icon('heroicon-o-cog-6-tooth'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageWhatsAppAdminConfig::route('/'),
            'edit' => Pages\EditWhatsAppAdminConfig::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return false; // Only one config allowed
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery();
    }
}
