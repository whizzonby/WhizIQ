<?php

namespace App\Filament\Dashboard\Pages;

use App\Filament\Dashboard\Resources\AftercareSequenceResource;
use App\Filament\Dashboard\Resources\AftercareTemplateResource;
use App\Filament\Dashboard\Widgets\AftercareActivityWidget;
use App\Models\NotificationPreference;
use App\Services\MessagingService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use UnitEnum;

class NotificationSettingsPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-bell';
    protected static ?string $navigationLabel = 'Notifications';
    protected static UnitEnum|string|null $navigationGroup = 'Settings';
    protected static ?int $navigationSort = 2;
    protected static ?string $title = 'Notification Settings';
    protected string $view = 'filament.dashboard.pages.notification-settings-page';

    public ?array $data = [];

    public function mount(): void
    {
        $prefs = NotificationPreference::forUser(auth()->id());

        $this->form->fill($prefs->only([
            'owner_phone', 'preferred_channel', 'digest_enabled', 'digest_time',
            'alert_payment_received', 'alert_overdue_invoice', 'alert_stale_deals',
            'alert_follow_up_due', 'alert_new_booking', 'alert_task_due',
            'alert_campaign_draft_prepared',
            'client_appointment_reminders', 'client_invoice_reminders',
            'client_aftercare_enabled', 'client_booking_confirmation',
        ]));
    }

    public function form(Schema $schema): Schema
    {
        $twilioOk = app(MessagingService::class)->isTwilioConfigured();

        return $schema
            ->schema([
                Section::make('Your Contact Details')
                    ->description('WhizIQ will send alerts and your daily digest to this number.')
                    ->icon('heroicon-o-device-phone-mobile')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('owner_phone')
                                ->label('Your Mobile Number')
                                ->placeholder('+1 555 000 0000')
                                ->helperText('Include country code, e.g. +44 7700 900000')
                                ->tel(),

                            Select::make('preferred_channel')
                                ->label('Send via')
                                ->options([
                                    'sms'      => 'SMS only',
                                    'whatsapp' => 'WhatsApp only',
                                    'both'     => 'Both SMS + WhatsApp',
                                ])
                                ->required()
                                ->helperText($twilioOk
                                    ? 'Twilio is configured and ready.'
                                    : '⚠️ Twilio is not configured. Contact your administrator.'),
                        ]),
                    ]),

                Section::make('Daily Business Digest')
                    ->description('Get a morning summary of your business health sent to your phone.')
                    ->icon('heroicon-o-sun')
                    ->schema([
                        Grid::make(2)->schema([
                            Toggle::make('digest_enabled')
                                ->label('Enable daily digest')
                                ->helperText('Receive a morning brief every day'),

                            TimePicker::make('digest_time')
                                ->label('Digest time')
                                ->seconds(false)
                                ->helperText('What time should we send your brief?'),
                        ]),
                    ]),

                Section::make('My Business Alerts')
                    ->description('Instant notifications about what is happening in your business.')
                    ->icon('heroicon-o-bell-alert')
                    ->schema([
                        Grid::make(2)->schema([
                            Toggle::make('alert_payment_received')
                                ->label('Payment received')
                                ->helperText('Notify me when a client pays an invoice'),

                            Toggle::make('alert_overdue_invoice')
                                ->label('Invoice overdue')
                                ->helperText('Notify me when an invoice becomes overdue'),

                            Toggle::make('alert_new_booking')
                                ->label('New booking')
                                ->helperText('Notify me when a client books an appointment'),

                            Toggle::make('alert_follow_up_due')
                                ->label('Follow-up reminders')
                                ->helperText('Remind me when a client follow-up is due'),

                            Toggle::make('alert_stale_deals')
                                ->label('Stale deals')
                                ->helperText('Alert me when deals have not moved in 14+ days'),

                            Toggle::make('alert_task_due')
                                ->label('Task reminders')
                                ->helperText('Notify me when a task with a reminder is due'),

                            Toggle::make('alert_campaign_draft_prepared')
                                ->label('Campaign drafts ready')
                                ->helperText('Notify me when WhizIQ prepares a campaign draft for review'),
                        ]),
                    ]),

                Section::make('Client Automation')
                    ->description('Messages sent automatically to your clients on your behalf.')
                    ->icon('heroicon-o-paper-airplane')
                    ->schema([
                        Grid::make(2)->schema([
                            Toggle::make('client_appointment_reminders')
                                ->label('Appointment reminders')
                                ->helperText('Send clients an SMS reminder 24h + 1h before their appointment'),

                            Toggle::make('client_booking_confirmation')
                                ->label('Booking confirmations')
                                ->helperText('Send clients an SMS when they book an appointment'),

                            Toggle::make('client_invoice_reminders')
                                ->label('Invoice payment reminders')
                                ->helperText('Remind clients by SMS when their invoice is overdue'),

                            Toggle::make('client_aftercare_enabled')
                                ->label('Post-appointment messages')
                                ->helperText('Send aftercare or follow-up messages after appointments complete'),
                        ]),
                    ]),
            ])
            ->statePath('data');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('send_test')
                ->label('Send Test Message')
                ->icon('heroicon-o-paper-airplane')
                ->color('gray')
                ->action(function () {
                    $phone = $this->data['owner_phone'] ?? null;

                    if (empty($phone)) {
                        Notification::make()
                            ->title('No phone number')
                            ->body('Enter your mobile number first, then save.')
                            ->warning()
                            ->send();
                        return;
                    }

                    $sent = app(MessagingService::class)->sendSms(
                        $phone,
                        "👋 Test message from WhizIQ — your alerts are working correctly!"
                    );

                    if ($sent) {
                        Notification::make()
                            ->title('Test message sent')
                            ->body('Check your phone for the test SMS.')
                            ->success()
                            ->send();
                    } else {
                        Notification::make()
                            ->title('Message failed')
                            ->body('Check that Twilio is configured in admin settings.')
                            ->danger()
                            ->send();
                    }
                }),

            Action::make('manage_templates')
                ->label('Aftercare Templates')
                ->icon('heroicon-o-heart')
                ->color('gray')
                ->url(AftercareTemplateResource::getUrl('index')),

            Action::make('manage_sequences')
                ->label('Follow-up Sequences')
                ->icon('heroicon-o-queue-list')
                ->color('gray')
                ->url(AftercareSequenceResource::getUrl('index')),

            Action::make('save')
                ->label('Save Settings')
                ->icon('heroicon-o-check')
                ->action('save'),
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            AftercareActivityWidget::class,
        ];
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $prefs = NotificationPreference::forUser(auth()->id());
        $prefs->update($data);

        Notification::make()
            ->title('Settings saved')
            ->success()
            ->send();
    }
}
