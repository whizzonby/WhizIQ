<?php

namespace App\Filament\Dashboard\Pages;

use App\Models\AvailabilitySchedule;
use App\Models\BookingSetting;
use App\Models\CalendarConnection;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Schemas\Schema;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Str;

use UnitEnum;
use BackedEnum;

class BookingSettingsPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationLabel = 'Booking Settings';

    protected static ?string $title = 'Configure Your Booking Page';

    protected static UnitEnum|string|null $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 3;


    protected string $view = 'filament.dashboard.pages.booking-settings-page';

    public ?array $data = [];

    public ?BookingSetting $settings = null;

    public function mount(): void
    {
        $this->settings = BookingSetting::firstOrCreate(
            ['user_id' => auth()->id()],
            [
                'booking_slug' => $this->generateUniqueSlug(),
                'is_booking_enabled' => true,
                'display_name' => auth()->user()->name,
                'timezone' => config('app.timezone', 'UTC'),
                'brand_color' => '#3B82F6',
                'min_booking_notice_hours' => 24,
                'max_booking_days_ahead' => 60,
                'require_approval' => false,
            ]
        );

        $schedules = AvailabilitySchedule::where('user_id', auth()->id())
            ->get()
            ->keyBy('day_of_week');

        $scheduleData = [];
        for ($day = 0; $day <= 6; $day++) {
            $schedule = $schedules->get($day);
            $rawStart = $schedule ? substr($schedule->getRawOriginal('start_time') ?? '09:00:00', 0, 5) : '09:00';
            $rawEnd   = $schedule ? substr($schedule->getRawOriginal('end_time')   ?? '17:00:00', 0, 5) : '17:00';
            $scheduleData["sched_{$day}_available"] = $schedule ? (bool) $schedule->is_available : in_array($day, [1, 2, 3, 4, 5]);
            $scheduleData["sched_{$day}_start"]     = $rawStart;
            $scheduleData["sched_{$day}_end"]       = $rawEnd;
        }

        $this->form->fill(array_merge([
            'is_booking_enabled' => $this->settings->is_booking_enabled,
            'booking_slug' => $this->settings->booking_slug,
            'display_name' => $this->settings->display_name,
            'welcome_message' => $this->settings->welcome_message,
            'business_address' => $this->settings->business_address,
            'business_city' => $this->settings->business_city,
            'business_country' => $this->settings->business_country,
            'brand_color' => $this->settings->brand_color,
            'timezone' => $this->settings->timezone,
            'currency' => $this->settings->currency ?? 'USD',
            'min_booking_notice_hours' => $this->settings->min_booking_notice_hours,
            'max_booking_days_ahead' => $this->settings->max_booking_days_ahead,
            'require_approval' => $this->settings->require_approval,
            'meeting_platform' => $this->settings->meeting_platform ?? 'none',
            'meeting_link' => $this->settings->meeting_link,
            'meeting_instructions' => $this->settings->meeting_instructions,
            'payment_instructions' => $this->settings->payment_instructions,
            'payment_link' => $this->settings->payment_link,
            'show_payment_in_email' => $this->settings->show_payment_in_email ?? false,
            'google_meet_enabled' => $this->settings->google_meet_enabled ?? false,
        ], $scheduleData));
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('General Settings')
                    ->schema([
                        Forms\Components\Toggle::make('is_booking_enabled')
                            ->label('Enable Public Booking')
                            ->helperText('Allow clients to book appointments through your public booking page')
                            ->default(true)
                            ->live(),

                        Forms\Components\TextInput::make('booking_slug')
                            ->label('Booking Page URL')
                            ->required()
                            ->unique(BookingSetting::class, 'booking_slug', ignoreRecord: true)
                            ->alphaDash()
                            ->maxLength(100)
                            ->prefix(url('/book/'))
                            ->helperText('This is your unique booking page URL that you can share with clients')
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, callable $set) {
                                $set('booking_slug', Str::slug($state));
                            }),

                        Forms\Components\TextInput::make('display_name')
                            ->label('Display Name')
                            ->required()
                            ->maxLength(255)
                            ->helperText('Your name or business name shown to clients'),

                        Forms\Components\Textarea::make('welcome_message')
                            ->label('Welcome Message')
                            ->rows(3)
                            ->maxLength(1000)
                            ->helperText('A friendly message shown on your booking page'),

                        Forms\Components\TextInput::make('business_address')
                            ->label('Business Address')
                            ->maxLength(255)
                            ->helperText('Street address for your business'),

                        Forms\Components\TextInput::make('business_city')
                            ->label('City')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('business_country')
                            ->label('Country')
                            ->maxLength(255),

                        Forms\Components\ColorPicker::make('brand_color')
                            ->label('Brand Color')
                            ->helperText('Primary color for your booking page')
                            ->default('#3B82F6'),
                    ])
                    ->columns(2),

                Section::make('Availability Settings')
                    ->schema([
                        Forms\Components\Select::make('timezone')
                            ->label('Timezone')
                            ->required()
                            ->searchable()
                            ->options(function () {
                                $timezones = [];
                                foreach (timezone_identifiers_list() as $timezone) {
                                    $timezones[$timezone] = $timezone;
                                }
                                return $timezones;
                            })
                            ->default(config('app.timezone', 'UTC'))
                            ->helperText('All appointment times will be shown in this timezone'),

                        Forms\Components\Select::make('currency')
                            ->label('Currency')
                            ->required()
                            ->searchable()
                            ->default('USD')
                            ->options([
                                'USD' => 'USD — US Dollar',
                                'GBP' => 'GBP — British Pound',
                                'EUR' => 'EUR — Euro',
                                'CAD' => 'CAD — Canadian Dollar',
                                'AUD' => 'AUD — Australian Dollar',
                                'NZD' => 'NZD — New Zealand Dollar',
                                'ZAR' => 'ZAR — South African Rand',
                                'NGN' => 'NGN — Nigerian Naira',
                                'GHS' => 'GHS — Ghanaian Cedi',
                                'KES' => 'KES — Kenyan Shilling',
                                'INR' => 'INR — Indian Rupee',
                                'SGD' => 'SGD — Singapore Dollar',
                                'AED' => 'AED — UAE Dirham',
                                'JPY' => 'JPY — Japanese Yen',
                                'CHF' => 'CHF — Swiss Franc',
                            ])
                            ->helperText('Used on invoices and receipts generated from bookings'),

                        Forms\Components\TextInput::make('min_booking_notice_hours')
                            ->label('Minimum Booking Notice (hours)')
                            ->required()
                            ->numeric()
                            ->default(24)
                            ->minValue(0)
                            ->maxValue(720)
                            ->helperText('How far in advance clients must book (e.g., 24 hours)'),

                        Forms\Components\TextInput::make('max_booking_days_ahead')
                            ->label('Maximum Booking Window (days)')
                            ->required()
                            ->numeric()
                            ->default(60)
                            ->minValue(1)
                            ->maxValue(365)
                            ->helperText('How far in advance clients can book (e.g., 60 days)'),

                        Forms\Components\Toggle::make('require_approval')
                            ->label('Require Approval')
                            ->helperText('All bookings require your manual approval before confirmation')
                            ->default(false),
                    ])
                    ->columns(2),

                Section::make('Working Hours')
                    ->description('Set which days you are available and your hours for each day')
                    ->schema(function () {
                        $dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
                        $rows = [];
                        foreach (range(0, 6) as $day) {
                            $rows[] = Grid::make(7)->schema([
                                Forms\Components\Placeholder::make("day_{$day}_label")
                                    ->label('')
                                    ->content($dayNames[$day])
                                    ->columnSpan(2),
                                Forms\Components\Toggle::make("sched_{$day}_available")
                                    ->label('Open')
                                    ->inline(true)
                                    ->live()
                                    ->columnSpan(1),
                                Forms\Components\TextInput::make("sched_{$day}_start")
                                    ->label('Opens at')
                                    ->type('time')
                                    ->visible(fn ($get) => $get("sched_{$day}_available"))
                                    ->columnSpan(2),
                                Forms\Components\TextInput::make("sched_{$day}_end")
                                    ->label('Closes at')
                                    ->type('time')
                                    ->visible(fn ($get) => $get("sched_{$day}_available"))
                                    ->columnSpan(2),
                            ]);
                        }
                        return $rows;
                    })
                    ->columns(1),

                Section::make('Meeting Platform')
                    ->description('Configure online meeting settings for appointments')
                    ->schema([
                        Forms\Components\Select::make('meeting_platform')
                            ->label('Meeting Platform')
                            ->options([
                                'none' => 'No online meeting',
                                'custom' => 'Custom Meeting Link (Zoom, Google Meet, Teams, etc.)',
                            ])
                            ->default('none')
                            ->live()
                            ->helperText('Choose how to handle meeting links for appointments'),

                        Forms\Components\TextInput::make('meeting_link')
                            ->label('Meeting Link')
                            ->url()
                            ->placeholder('https://zoom.us/j/123456789 or https://meet.google.com/abc-defg-hij')
                            ->helperText('Enter your personal meeting room link (Zoom, Google Meet, Teams, etc.)')
                            ->visible(fn ($get) => $get('meeting_platform') === 'custom')
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('meeting_instructions')
                            ->label('Meeting Instructions (Optional)')
                            ->rows(3)
                            ->placeholder('e.g., Please join 5 minutes early. Meeting password: 12345')
                            ->helperText('Additional instructions for joining the meeting (password, dial-in number, etc.)')
                            ->visible(fn ($get) => $get('meeting_platform') === 'custom')
                            ->columnSpanFull(),

                        Forms\Components\Placeholder::make('meeting_info')
                            ->label('')
                            ->content('This meeting link will be included in all appointment confirmation emails and reminders.')
                            ->visible(fn ($get) => $get('meeting_platform') === 'custom'),
                    ])
                    ->columns(1),

                Section::make('Payment Information')
                    ->description('Add payment instructions and links to confirmation emails')
                    ->schema([
                        Forms\Components\Toggle::make('show_payment_in_email')
                            ->label('Include Payment Info in Confirmation Emails')
                            ->helperText('When enabled, payment details will be included in appointment confirmation emails')
                            ->default(false)
                            ->live()
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('payment_instructions')
                            ->label('Payment Instructions')
                            ->rows(3)
                            ->placeholder('e.g., Payment is due before your appointment. We accept Venmo, PayPal, Zelle, or credit card.')
                            ->helperText('Instructions shown to clients about how to pay (accepted methods, due date, etc.)')
                            ->visible(fn ($get) => $get('show_payment_in_email'))
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('payment_link')
                            ->label('Payment Link (Optional)')
                            ->url()
                            ->placeholder('https://venmo.com/YourUsername or https://paypal.me/YourUsername')
                            ->helperText('Link to your payment page (Venmo, PayPal, Stripe, Square, etc.). Clients can click this to pay.')
                            ->visible(fn ($get) => $get('show_payment_in_email'))
                            ->columnSpanFull(),

                        Forms\Components\Placeholder::make('payment_info')
                            ->label('')
                            ->content('💡 Tip: You can use Venmo (venmo.com/username), PayPal (paypal.me/username), Cash App (cash.app/$username), or any payment platform link.')
                            ->visible(fn ($get) => $get('show_payment_in_email')),
                    ])
                    ->columns(1),

                // Calendar Sync and Zoom Connection sections commented out
                // Users now enter meeting links manually in the Meeting Platform section above

                // Section::make('Calendar Sync')
                //     ->description('Connect your calendar to automatically block busy times')
                //     ->schema([
                //         Forms\Components\Placeholder::make('calendar_status')
                //             ->label('Google Calendar Status')
                //             ->content(function () {
                //                 $connection = CalendarConnection::where('user_id', auth()->id())
                //                     ->where('provider', 'google_calendar')
                //                     ->where('is_primary', true)
                //                     ->first();
                //
                //                 if ($connection) {
                //                     $statusText = '✅ Connected';
                //                     if ($connection->provider_email) {
                //                         $statusText .= ' (' . $connection->provider_email . ')';
                //                     }
                //                     if ($connection->last_synced_at) {
                //                         $statusText .= ' • Last synced: ' . $connection->last_synced_at->diffForHumans();
                //                     }
                //                     return $statusText;
                //                 }
                //
                //                 return '❌ Not connected';
                //             }),
                //
                //         Forms\Components\Placeholder::make('calendar_info')
                //             ->label('')
                //             ->content('Connect your Google Calendar to automatically block time slots when you have other events scheduled. This prevents double-bookings.')
                //             ->visible(fn () => !CalendarConnection::where('user_id', auth()->id())
                //                 ->where('provider', 'google_calendar')
                //                 ->exists()),
                //
                //         Forms\Components\ViewField::make('calendar_actions')
                //             ->view('filament.dashboard.components.calendar-actions')
                //             ->viewData([
                //                 'provider' => 'google_calendar',
                //                 'hasConnection' => CalendarConnection::where('user_id', auth()->id())
                //                     ->where('provider', 'google_calendar')
                //                     ->exists(),
                //                 'connectUrl' => route('calendar.google.connect'),
                //                 'testUrl' => route('calendar.google.test'),
                //                 'disconnectUrl' => route('calendar.google.disconnect'),
                //             ]),
                //     ])
                //     ->columns(1),
                //
                // Section::make('Zoom Connection')
                //     ->description('Connect your Zoom account to automatically create meeting links')
                //     ->schema([
                //         Forms\Components\Placeholder::make('zoom_status')
                //             ->label('Zoom Status')
                //             ->content(function () {
                //                 $connection = CalendarConnection::where('user_id', auth()->id())
                //                     ->where('provider', 'zoom')
                //                     ->first();
                //
                //                 if ($connection) {
                //                     $statusText = '✅ Connected';
                //                     if ($connection->provider_email) {
                //                         $statusText .= ' (' . $connection->provider_email . ')';
                //                     }
                //                     return $statusText;
                //                 }
                //
                //                 return '❌ Not connected';
                //             }),
                //
                //         Forms\Components\Placeholder::make('zoom_info')
                //             ->label('')
                //             ->content('Connect your Zoom account to automatically generate Zoom meeting links for all appointments. Each user needs to connect their own Zoom account.')
                //             ->visible(fn () => !CalendarConnection::where('user_id', auth()->id())
                //                 ->where('provider', 'zoom')
                //                 ->exists()),
                //
                //         Forms\Components\ViewField::make('zoom_actions')
                //             ->view('filament.dashboard.components.calendar-actions')
                //             ->viewData([
                //                 'provider' => 'zoom',
                //                 'hasConnection' => CalendarConnection::where('user_id', auth()->id())
                //                     ->where('provider', 'zoom')
                //                     ->exists(),
                //                 'connectUrl' => route('zoom.connect'),
                //                 'testUrl' => route('zoom.test'),
                //                 'disconnectUrl' => route('zoom.disconnect'),
                //             ]),
                //     ])
                //     ->columns(1),
            ])
            ->statePath('data');
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Save Settings')
                ->submit('save'),
        ];
    }

    public function save(): void
    {
        $data = $this->form->getState();

        // Extract and save working hours schedule
        for ($day = 0; $day <= 6; $day++) {
            $isOpen    = $data["sched_{$day}_available"] ?? false;
            $startTime = $data["sched_{$day}_start"] ?? '09:00';
            $endTime   = $data["sched_{$day}_end"]   ?? '17:00';

            AvailabilitySchedule::updateOrCreate(
                ['user_id' => auth()->id(), 'day_of_week' => $day],
                ['is_available' => $isOpen, 'start_time' => $startTime, 'end_time' => $endTime]
            );

            unset($data["sched_{$day}_available"], $data["sched_{$day}_start"], $data["sched_{$day}_end"]);
        }

        $this->settings->update($data);

        Notification::make()
            ->title('Settings Saved')
            ->body('Your booking settings have been updated successfully.')
            ->success()
            ->send();
    }

    public function copyBookingUrl(): void
    {
        Notification::make()
            ->title('URL Copied')
            ->body('Your booking page URL has been copied to clipboard.')
            ->success()
            ->send();
    }

    public function getBookingUrl(): string
    {
        return $this->settings ? $this->settings->booking_url : '';
    }

    protected function generateUniqueSlug(): string
    {
        $slug = Str::slug(auth()->user()->name);
        $originalSlug = $slug;
        $counter = 1;

        while (BookingSetting::where('booking_slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('viewBookingPage')
                ->label('View Booking Page')
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->color('primary')
                ->url(fn () => $this->getBookingUrl(), shouldOpenInNewTab: true)
                ->visible(fn () => $this->settings && $this->settings->is_booking_enabled),
            Action::make('manageTimeOff')
                ->label('Manage Time Off')
                ->icon('heroicon-o-no-symbol')
                ->color('gray')
                ->url(fn () => route('filament.dashboard.resources.availability-exceptions.index')),
        ];
    }
}
