<?php

namespace App\Filament\Dashboard\Resources;

use App\Filament\Dashboard\Resources\AppointmentTypeResource\Pages;
use App\Models\AppointmentType;
use App\Models\Venue;
use Filament\Forms;
use Filament\Resources\Resource;
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

class AppointmentTypeResource extends Resource
{
    protected static ?string $model = AppointmentType::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationLabel = 'Appointment Types';

    protected static UnitEnum|string|null $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Basic Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('30-min Consultation'),

                        Forms\Components\Textarea::make('description')
                            ->rows(3)
                            ->maxLength(1000)
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('duration_minutes')
                            ->label('Duration (minutes)')
                            ->required()
                            ->numeric()
                            ->default(30)
                            ->minValue(15)
                            ->maxValue(480),

                        Forms\Components\TextInput::make('price')
                            ->numeric()
                            ->prefix('$')
                            ->default(0)
                            ->minValue(0),

                        Forms\Components\ColorPicker::make('color')
                            ->default('#3B82F6'),

                        Forms\Components\FileUpload::make('image_url')
                            ->label('Cover Image')
                            ->image()
                            ->imageEditor()
                            ->directory('appointment-types')
                            ->visibility('public')
                            ->maxSize(2048)
                            ->helperText('Main cover image for this service')
                            ->columnSpanFull(),

                        Forms\Components\FileUpload::make('gallery_images')
                            ->label('Gallery Images')
                            ->image()
                            ->imageEditor()
                            ->directory('appointment-types/gallery')
                            ->visibility('public')
                            ->multiple()
                            ->reorderable()
                            ->maxFiles(10)
                            ->maxSize(2048)
                            ->helperText('Upload multiple images to showcase your work (max 10 images)')
                            ->columnSpanFull(),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->helperText('Only active types are shown on your booking page'),

                        Forms\Components\Select::make('appointment_format')
                            ->label('Appointment Format')
                            ->options([
                                'online' => 'Online (Video/Meeting)',
                                'in_person' => 'In-Person',
                                'hybrid' => 'Hybrid (Both Online & In-Person)',
                                'phone' => 'Phone Call',
                            ])
                            ->default('online')
                            ->required()
                            ->native(false)
                            ->live()
                            ->helperText('Select how this appointment type will be conducted')
                            ->afterStateUpdated(function ($state, callable $set) {
                                // Reset venue-related fields if format changes
                                if ($state === 'online' || $state === 'phone') {
                                    $set('default_venue_id', null);
                                    $set('requires_location', false);
                                }
                            }),
                    ])
                    ->columns(2),

                Section::make('Payment Settings')
                    ->description('Choose whether clients can book for free or receive an invoice after booking')
                    ->icon('heroicon-o-credit-card')
                    ->schema([
                        Forms\Components\Select::make('payment_type')
                            ->label('Payment Type')
                            ->options([
                                'none' => 'No Payment Required',
                                'invoice' => 'Invoice Later (Book Now, Pay Later)',
                            ])
                            ->default('invoice')
                            ->required()
                            ->native(false)
                            ->live()
                            ->helperText('Use booking settings to include payment instructions or a payment link in confirmation emails.'),

                        Forms\Components\Placeholder::make('payment_summary')
                            ->label('Payment Summary')
                            ->content(function ($get) {
                                $price = $get('price') ?? 0;
                                $paymentType = $get('payment_type');
                                if ($paymentType === 'none') {
                                    return 'No payment required for this service.';
                                }

                                if ($paymentType === 'invoice') {
                                    return "Clients will book now and receive an invoice for **$" . number_format($price, 2) . "** to pay later.";
                                }

                                return '';
                            })
                            ->visible(fn ($get) => $get('payment_type') !== 'none'),

                        Forms\Components\Placeholder::make('payment_info')
                            ->label('How it works')
                            ->content(function ($get) {
                                $paymentType = $get('payment_type');

                                if ($paymentType === 'invoice') {
                                    return '📧 Clients can book immediately. An invoice with a payment link will be sent to them automatically. You can track payment status in the dashboard.';
                                }

                                return '';
                            })
                            ->visible(fn ($get) => $get('payment_type') !== 'none'),
                    ])
                    ->columns(2)
                    ->collapsed(),

                Section::make('Advanced Settings')
                    ->schema([
                        Forms\Components\TextInput::make('buffer_before_minutes')
                            ->label('Buffer Before (minutes)')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->helperText('Prep time before appointment'),

                        Forms\Components\TextInput::make('buffer_after_minutes')
                            ->label('Buffer After (minutes)')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->helperText('Cleanup time after appointment'),

                        Forms\Components\TextInput::make('max_per_day')
                            ->label('Max Per Day')
                            ->numeric()
                            ->minValue(1)
                            ->helperText('Leave empty for unlimited'),

                        Forms\Components\TextInput::make('sort_order')
                            ->label('Display Order')
                            ->numeric()
                            ->default(0)
                            ->helperText('Lower numbers appear first'),
                    ])
                    ->columns(2)
                    ->collapsed(),

                Section::make('Location Settings')
                    ->description('Configure venue/location for in-person appointments')
                    ->icon('heroicon-o-map-pin')
                    ->schema([
                        Forms\Components\Select::make('default_venue_id')
                            ->label('Default Venue')
                            ->relationship('defaultVenue', 'name', fn ($query) =>
                                $query->where('user_id', auth()->id())->active()
                            )
                            ->searchable()
                            ->preload()
                            ->helperText('Default location for this appointment type')
                            ->visible(fn ($get) => in_array($get('appointment_format'), ['in_person', 'hybrid'])),

                        Forms\Components\Toggle::make('requires_location')
                            ->label('Require Location Selection')
                            ->default(false)
                            ->helperText('Clients must select a venue when booking')
                            ->visible(fn ($get) => in_array($get('appointment_format'), ['in_person', 'hybrid'])),

                        Forms\Components\Select::make('allowed_venues')
                            ->label('Allowed Venues')
                            ->options(function () {
                                return Venue::where('user_id', auth()->id())
                                    ->active()
                                    ->pluck('name', 'id')
                                    ->toArray();
                            })
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->helperText('Leave empty to allow all venues. Select specific venues to restrict options.')
                            ->visible(fn ($get) => in_array($get('appointment_format'), ['in_person', 'hybrid'])),
                    ])
                    ->collapsed()
                    ->visible(fn ($get) => in_array($get('appointment_format'), ['in_person', 'hybrid'])),

                Section::make('Booking Form Requirements')
                    ->schema([
                        Forms\Components\Toggle::make('require_phone')
                            ->label('Require Phone Number')
                            ->default(false),

                        Forms\Components\Toggle::make('require_company')
                            ->label('Require Company Name')
                            ->default(false),
                    ])
                    ->columns(2)
                    ->collapsed(),

                Section::make('Aftercare Automation')
                    ->description('Automatically send aftercare instructions when appointments are completed')
                    ->icon('heroicon-o-heart')
                    ->schema([
                        Forms\Components\Toggle::make('enable_aftercare')
                            ->label('Enable Automatic Aftercare Messages')
                            ->default(false)
                            ->live()
                            ->helperText('Send automated aftercare instructions after appointment completion'),

                        Forms\Components\Radio::make('aftercare_mode')
                            ->label('Aftercare type')
                            ->options([
                                'template' => 'Single message (template)',
                                'sequence' => 'Multi-step sequence',
                            ])
                            ->default('template')
                            ->live()
                            ->visible(fn ($get) => $get('enable_aftercare'))
                            ->helperText('Use a sequence to send multiple follow-ups at different intervals.'),

                        Forms\Components\Select::make('aftercare_template_id')
                            ->label('Aftercare Template')
                            ->relationship('aftercareTemplate', 'name', fn ($query) =>
                                $query->where('user_id', auth()->id())->active()
                            )
                            ->searchable()
                            ->preload()
                            ->required(fn ($get) => $get('enable_aftercare') && $get('aftercare_mode') === 'template')
                            ->visible(fn ($get) => $get('enable_aftercare') && $get('aftercare_mode') !== 'sequence')
                            ->helperText('Select the aftercare template to use for this service')
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\Textarea::make('description')
                                    ->rows(2),
                            ])
                            ->createOptionUsing(function ($data) {
                                return \App\Models\AftercareTemplate::create([
                                    'user_id' => auth()->id(),
                                    'name' => $data['name'],
                                    'description' => $data['description'] ?? null,
                                    'send_via_email' => true,
                                    'is_active' => true,
                                ])->id;
                            }),

                        Forms\Components\Select::make('aftercare_sequence_id')
                            ->label('Follow-up Sequence')
                            ->relationship('aftercareSequence', 'name', fn ($query) =>
                                $query->where('user_id', auth()->id())->where('is_active', true)
                            )
                            ->searchable()
                            ->preload()
                            ->required(fn ($get) => $get('enable_aftercare') && $get('aftercare_mode') === 'sequence')
                            ->visible(fn ($get) => $get('enable_aftercare') && $get('aftercare_mode') === 'sequence')
                            ->helperText('All active steps in the sequence will be scheduled for each completed appointment.')
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->maxLength(255),
                            ])
                            ->createOptionUsing(function ($data) {
                                return \App\Models\AftercareSequence::create([
                                    'user_id'   => auth()->id(),
                                    'name'      => $data['name'],
                                    'is_active' => true,
                                ])->id;
                            }),

                        Forms\Components\Placeholder::make('aftercare_info')
                            ->label('How it works')
                            ->content('When an appointment is marked as "completed", the selected template or sequence is automatically scheduled for the client.')
                            ->visible(fn ($get) => $get('enable_aftercare')),
                    ])
                    ->columns(1)
                    ->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ColorColumn::make('color')
                    ->label('')
                    ->width(10),

                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('duration_minutes')
                    ->label('Duration')
                    ->formatStateUsing(fn ($state) => $state . ' min')
                    ->sortable(),

                Tables\Columns\TextColumn::make('price')
                    ->money('USD')
                    ->sortable(),

                Tables\Columns\TextColumn::make('appointment_format')
                    ->label('Format')
                    ->formatStateUsing(fn ($state) => match($state) {
                        'online' => 'Online',
                        'in_person' => 'In-Person',
                        'hybrid' => 'Hybrid',
                        'phone' => 'Phone',
                        default => 'Online',
                    })
                    ->badge()
                    ->color(fn ($state) => match($state) {
                        'online' => 'primary',
                        'in_person' => 'success',
                        'hybrid' => 'warning',
                        'phone' => 'info',
                        default => 'gray',
                    })
                    ->toggleable(),

                Tables\Columns\TextColumn::make('appointments_count')
                    ->label('Bookings')
                    ->counts('appointments')
                    ->badge()
                    ->color('success'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Order')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('sort_order')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active')
                    ->placeholder('All types')
                    ->trueLabel('Active only')
                    ->falseLabel('Inactive only'),
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
            ->emptyStateHeading('No appointment types yet')
            ->emptyStateDescription('Create your first service offering')
            ->emptyStateIcon('heroicon-o-clipboard-document-list');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageAppointmentTypes::route('/'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('user_id', auth()->id());
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->canCreate(AppointmentType::class, 'appointments_types_limit') ?? false;
    }

}
