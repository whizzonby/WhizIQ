<?php

namespace App\Filament\Dashboard\Pages;

use App\Models\WhatsAppConfiguration;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Schemas\Components\Section;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Schema;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use UnitEnum;
use BackedEnum;

class WhatsAppSettingsPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-device-phone-mobile';

    protected static ?string $navigationLabel = 'WhatsApp Settings';

    protected static ?string $title = 'WhatsApp Configuration';

    protected static UnitEnum|string|null $navigationGroup = 'Settings';

    protected static bool $shouldRegisterNavigation = false;

    protected static ?int $navigationSort = 10;

    protected string $view = 'filament.dashboard.pages.whatsapp-settings-page';

    public ?array $data = [];

    public ?WhatsAppConfiguration $config = null;

    protected ?\App\Models\WhatsAppAdminConfig $adminConfig = null;

    public function mount(): void
    {
        $this->adminConfig = \App\Models\WhatsAppAdminConfig::getConfig();
        $this->config = WhatsAppConfiguration::forUser(auth()->id());

        $this->form->fill([
            'phone_number_id' => $this->config->phone_number_id,
            'phone_number' => $this->config->phone_number,
            'phone_number_display' => $this->config->phone_number_display,
            'is_active' => $this->config->is_active ?? false,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        $isAdminConfigured = $this->adminConfig 
            && $this->adminConfig->isConfigured() 
            && $this->adminConfig->is_active;

        $hasPhoneNumber = !empty($this->config->phone_number_id);
        $isFullyConfigured = $isAdminConfigured && $hasPhoneNumber;

        return $schema
            ->components([
                // Step 1: Admin Status Check
                Section::make('Step 1: System Status')
                    ->description('Check if WhatsApp is available for your account')
                    ->icon('heroicon-o-information-circle')
                    ->schema([
                        Forms\Components\Placeholder::make('system_status')
                            ->label('')
                            ->content(fn () => $this->getSystemStatusHtml($isAdminConfigured))
                            ->columnSpanFull(),
                    ])
                    ->visible(fn () => true)
                    ->collapsible()
                    ->collapsed(false),

                // Step 2: Phone Number Configuration (only if admin is configured)
                Section::make('Step 2: Connect Your Phone Number')
                    ->description('Enter your WhatsApp Business phone number details')
                    ->icon('heroicon-o-phone')
                    ->schema([
                        Forms\Components\TextInput::make('phone_number_id')
                            ->label('Phone Number ID')
                            ->required(fn ($get) => $get('is_active') || !empty($get('phone_number_id')))
                            ->maxLength(255)
                            ->placeholder('123456789012345')
                            ->helperText('Your WhatsApp Business Phone Number ID from Meta for Developers. This is required to send messages.')
                            ->disabled(fn () => !$isAdminConfigured)
                            ->visible(fn () => $isAdminConfigured)
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('phone_number')
                            ->label('Display Phone Number')
                            ->tel()
                            ->maxLength(20)
                            ->placeholder('+1 234 567 8900')
                            ->helperText('Your phone number in a readable format (for display only)')
                            ->disabled(fn () => !$isAdminConfigured)
                            ->visible(fn () => $isAdminConfigured)
                            ->columnSpan(2),

                        Forms\Components\TextInput::make('phone_number_display')
                            ->label('Display Name')
                            ->maxLength(100)
                            ->placeholder('My Business')
                            ->helperText('A friendly name for this phone number (optional)')
                            ->disabled(fn () => !$isAdminConfigured)
                            ->visible(fn () => $isAdminConfigured)
                            ->columnSpan(2),
                    ])
                    ->columns(2)
                    ->visible(fn () => $isAdminConfigured)
                    ->disabled(fn () => !$isAdminConfigured),

                // Step 3: Enable WhatsApp Messages
                Section::make('Step 3: Enable WhatsApp Messages')
                    ->description('Turn on WhatsApp messaging for aftercare templates')
                    ->icon('heroicon-o-paper-airplane')
                    ->schema([
                        Forms\Components\Placeholder::make('enable_info')
                            ->label('')
                            ->content(fn () => $this->getEnableInfoHtml($isFullyConfigured, $hasPhoneNumber))
                            ->columnSpanFull(),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Send WhatsApp Messages')
                            ->helperText('When enabled, WhatsApp messages will be sent automatically for aftercare templates that have WhatsApp enabled.')
                            ->default(false)
                            ->live()
                            ->disabled(fn () => !$isFullyConfigured)
                            ->afterStateUpdated(function ($state) {
                                $this->handleToggleChange($state);
                            })
                            ->columnSpanFull()
                            ->visible(fn () => $isAdminConfigured),
                    ])
                    ->visible(fn () => $isAdminConfigured),

                // Current Status Summary
                Section::make('Current Status')
                    ->description('Overview of your WhatsApp configuration')
                    ->icon('heroicon-o-check-circle')
                    ->schema([
                        Forms\Components\Placeholder::make('status_summary')
                            ->label('')
                            ->content(fn () => $this->getStatusSummaryHtml($isAdminConfigured, $hasPhoneNumber))
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->collapsed(true),
            ])
            ->statePath('data');
    }

    protected function getFormActions(): array
    {
        $isAdminConfigured = $this->adminConfig 
            && $this->adminConfig->isConfigured() 
            && $this->adminConfig->is_active;

        return [
            Action::make('save')
                ->label('Save Phone Number Details')
                ->submit('save')
                ->visible(fn () => $isAdminConfigured)
                ->disabled(fn () => !$isAdminConfigured),
        ];
    }

    /**
     * Handle toggle state change with immediate save
     */
    protected function handleToggleChange(bool $state): void
    {
        if (!$this->config) {
            return;
        }

        $isAdminConfigured = $this->adminConfig 
            && $this->adminConfig->isConfigured() 
            && $this->adminConfig->is_active;

        if (!$isAdminConfigured) {
            Notification::make()
                ->title('WhatsApp Not Available')
                ->body('WhatsApp Business API must be configured by an administrator first.')
                ->warning()
                ->send();
            return;
        }

        if ($state && empty($this->config->phone_number_id)) {
            Notification::make()
                ->title('Phone Number Required')
                ->body('Please enter your Phone Number ID before enabling WhatsApp messages.')
                ->warning()
                ->send();
            
            // Reset toggle to false
            $this->form->fill([
                ...$this->form->getState(),
                'is_active' => false,
            ]);
            return;
        }

        // Save immediately
        $updateData = ['is_active' => $state];
        
        if ($state && !$this->config->is_active && !empty($this->config->phone_number_id)) {
            $updateData['connected_at'] = now();
            $updateData['last_verified_at'] = now();
        }
        
        $this->config->update($updateData);
        $this->config->refresh();

        // Update form state
        $this->form->fill([
            ...$this->form->getState(),
            'is_active' => $state,
        ]);

        Notification::make()
            ->title($state ? 'WhatsApp Messages Enabled ✓' : 'WhatsApp Messages Disabled')
            ->body($state 
                ? 'WhatsApp messages will now be sent for aftercare templates that have WhatsApp enabled.' 
                : 'WhatsApp messages have been disabled.')
            ->success()
            ->send();
    }

    public function save(): void
    {
        $data = $this->form->getState();

        // Validate phone number ID if toggle is active
        if (!empty($data['is_active']) && empty($data['phone_number_id'])) {
            Notification::make()
                ->title('Phone Number ID Required')
                ->body('You must enter a Phone Number ID before enabling WhatsApp messages.')
                ->warning()
                ->send();
            return;
        }

        // If activating and phone number is set, mark as connected
        if (!empty($data['is_active']) && !$this->config->is_active && !empty($data['phone_number_id'])) {
            $data['connected_at'] = now();
            $data['last_verified_at'] = now();
        }

        $this->config->update($data);
        $this->config->refresh();

        // Refresh form with updated data
        $this->form->fill([
            'phone_number_id' => $this->config->phone_number_id,
            'phone_number' => $this->config->phone_number,
            'phone_number_display' => $this->config->phone_number_display,
            'is_active' => $this->config->is_active,
        ]);

        Notification::make()
            ->title('Settings Saved')
            ->body('Your WhatsApp phone number details have been saved successfully.')
            ->success()
            ->send();
    }

    protected function getSystemStatusHtml(bool $isAdminConfigured): \Illuminate\Support\HtmlString
    {
        if (!$isAdminConfigured) {
            $html = '<div class="p-4 bg-warning-50 dark:bg-warning-900/20 rounded-lg border-2 border-warning-300 dark:border-warning-700">';
            $html .= '<div class="flex items-start gap-3">';
            $html .= '<svg class="w-6 h-6 text-warning-600 dark:text-warning-400 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>';
            $html .= '<div class="flex-1">';
            $html .= '<h3 class="font-semibold text-warning-900 dark:text-warning-100 mb-1">WhatsApp Not Available</h3>';
            $html .= '<p class="text-sm text-warning-700 dark:text-warning-300">WhatsApp Business API has not been configured by the administrator yet. Please contact your administrator to set up WhatsApp before you can connect your phone number.</p>';
            $html .= '</div>';
            $html .= '</div>';
            $html .= '</div>';
            return new \Illuminate\Support\HtmlString($html);
        }
        
        $html = '<div class="p-4 bg-success-50 dark:bg-success-900/20 rounded-lg border-2 border-success-300 dark:border-success-700">';
        $html .= '<div class="flex items-start gap-3">';
        $html .= '<svg class="w-6 h-6 text-success-600 dark:text-success-400 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>';
        $html .= '<div class="flex-1">';
        $html .= '<h3 class="font-semibold text-success-900 dark:text-success-100 mb-1">WhatsApp Available ✓</h3>';
        $html .= '<p class="text-sm text-success-700 dark:text-success-300">WhatsApp Business API is configured and ready. You can now connect your phone number and start sending messages.</p>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';
        return new \Illuminate\Support\HtmlString($html);
    }

    protected function getEnableInfoHtml(bool $isFullyConfigured, bool $hasPhoneNumber): \Illuminate\Support\HtmlString
    {
        if (!$hasPhoneNumber) {
            $html = '<div class="p-3 bg-info-50 dark:bg-info-900/20 rounded-lg border border-info-200 dark:border-info-800 mb-4">';
            $html .= '<p class="text-sm text-info-700 dark:text-info-300">⚠️ <strong>Please complete Step 2 first:</strong> Enter your Phone Number ID above before enabling WhatsApp messages.</p>';
            $html .= '</div>';
            return new \Illuminate\Support\HtmlString($html);
        }

        $html = '<div class="p-3 bg-success-50 dark:bg-success-900/20 rounded-lg border border-success-200 dark:border-success-800 mb-4">';
        $html .= '<p class="text-sm text-success-700 dark:text-success-300">✓ Your phone number is connected. You can now enable WhatsApp messages below.</p>';
        $html .= '</div>';
        return new \Illuminate\Support\HtmlString($html);
    }

    protected function getStatusSummaryHtml(bool $isAdminConfigured, bool $hasPhoneNumber): \Illuminate\Support\HtmlString
    {
        $isActive = $this->config->is_active ?? false;
        
        $html = '<div class="space-y-3">';
        
        // Admin Status
        $html .= '<div class="flex items-center gap-2">';
        $html .= $isAdminConfigured 
            ? '<svg class="w-5 h-5 text-success-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>'
            : '<svg class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path></svg>';
        $html .= '<span class="text-sm ' . ($isAdminConfigured ? 'text-success-700 dark:text-success-300' : 'text-gray-500') . '">';
        $html .= $isAdminConfigured ? 'System: WhatsApp API Configured' : 'System: WhatsApp API Not Configured';
        $html .= '</span>';
        $html .= '</div>';

        // Phone Number Status
        if ($isAdminConfigured) {
            $html .= '<div class="flex items-center gap-2">';
            $html .= $hasPhoneNumber
                ? '<svg class="w-5 h-5 text-success-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>'
                : '<svg class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path></svg>';
            $html .= '<span class="text-sm ' . ($hasPhoneNumber ? 'text-success-700 dark:text-success-300' : 'text-gray-500') . '">';
            $html .= $hasPhoneNumber ? 'Phone Number: Connected' : 'Phone Number: Not Connected';
            $html .= '</span>';
            $html .= '</div>';

            // Messages Status
            $html .= '<div class="flex items-center gap-2">';
            $html .= $isActive
                ? '<svg class="w-5 h-5 text-success-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>'
                : '<svg class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path></svg>';
            $html .= '<span class="text-sm ' . ($isActive ? 'text-success-700 dark:text-success-300' : 'text-gray-500') . '">';
            $html .= $isActive ? 'Messages: Enabled' : 'Messages: Disabled';
            $html .= '</span>';
            $html .= '</div>';
        }
        
        $html .= '</div>';
        return new \Illuminate\Support\HtmlString($html);
    }
}
