<?php

namespace App\Filament\Dashboard\Resources;

use App\Filament\Dashboard\Resources\AftercareSequenceResource\Pages;
use App\Models\AftercareSequence;
use BackedEnum;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class AftercareSequenceResource extends Resource
{
    protected static ?string $model = AftercareSequence::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-queue-list';

    protected static ?string $navigationLabel = 'Follow-up Sequences';

    protected static UnitEnum|string|null $navigationGroup = 'Settings';

    protected static bool $shouldRegisterNavigation = true;

    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Sequence Details')
                ->icon('heroicon-o-queue-list')
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->required()
                        ->maxLength(255)
                        ->placeholder('e.g., New Client Welcome Series')
                        ->columnSpan(2),

                    Forms\Components\Textarea::make('description')
                        ->rows(2)
                        ->maxLength(500)
                        ->placeholder('When is this sequence used?')
                        ->columnSpanFull(),

                    Forms\Components\Toggle::make('is_active')
                        ->label('Active')
                        ->default(true)
                        ->inline(false),
                ])
                ->columns(2),

            Section::make('Steps')
                ->description('Add messages that go out at different intervals after the appointment completes.')
                ->icon('heroicon-o-list-bullet')
                ->schema([
                    Forms\Components\Repeater::make('steps')
                        ->relationship()
                        ->schema([
                            Grid::make(4)->schema([
                                Forms\Components\TextInput::make('step_order')
                                    ->label('Order')
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0),

                                Forms\Components\TextInput::make('delay_days')
                                    ->label('Days')
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0),

                                Forms\Components\TextInput::make('delay_hours')
                                    ->label('Hours')
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0)
                                    ->maxValue(23),

                                Forms\Components\TextInput::make('delay_minutes')
                                    ->label('Minutes')
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0)
                                    ->maxValue(59),
                            ]),

                            Grid::make(3)->schema([
                                Forms\Components\Toggle::make('send_whatsapp')
                                    ->label('WhatsApp')
                                    ->inline(false)
                                    ->helperText(function () {
                                        $adminConfig = \App\Models\WhatsAppAdminConfig::getConfig();
                                        $userConfig  = \App\Models\WhatsAppConfiguration::forUser(auth()->id());
                                        $ready = $adminConfig?->isConfigured() && $adminConfig?->is_active
                                                 && !empty($userConfig?->phone_number_id) && $userConfig?->is_active;
                                        return $ready ? 'Connected' : '⚠️ Not configured';
                                    }),

                                Forms\Components\Toggle::make('send_sms')
                                    ->label('SMS (Twilio)')
                                    ->inline(false)
                                    ->helperText(function () {
                                        $ok = !empty(config('services.twilio.sid')) && !empty(config('services.twilio.token'));
                                        return $ok ? 'Configured' : '⚠️ Twilio not configured';
                                    }),

                                Forms\Components\Toggle::make('include_rebooking_link')
                                    ->label('Add rebooking link')
                                    ->inline(false),
                            ]),

                            Forms\Components\Textarea::make('message_body')
                                ->label('Message')
                                ->required()
                                ->rows(4)
                                ->placeholder('Hi {{client_first_name}}, just checking in after your {{appointment_type}} on {{appointment_date}}. Hope all is well! 😊')
                                ->helperText('Variables: {{client_name}} {{client_first_name}} {{appointment_type}} {{appointment_date}} {{appointment_time}} {{business_name}} {{business_phone}} {{rebooking_link}}')
                                ->columnSpanFull(),

                            Forms\Components\Toggle::make('is_active')
                                ->label('Step active')
                                ->default(true)
                                ->inline(false),
                        ])
                        ->orderColumn('step_order')
                        ->defaultItems(0)
                        ->addActionLabel('Add step')
                        ->collapsible()
                        ->itemLabel(fn (array $state): string =>
                            ($state['step_order'] !== null ? 'Step ' . ($state['step_order'] + 1) . ' — ' : '')
                            . self::buildDelayLabel($state)
                        )
                        ->columnSpanFull(),
                ]),
        ]);
    }

    private static function buildDelayLabel(array $state): string
    {
        $parts = [];
        if (!empty($state['delay_days']))    $parts[] = $state['delay_days']    . 'd';
        if (!empty($state['delay_hours']))   $parts[] = $state['delay_hours']   . 'h';
        if (!empty($state['delay_minutes'])) $parts[] = $state['delay_minutes'] . 'm';

        $channels = [];
        if (!empty($state['send_whatsapp'])) $channels[] = 'WhatsApp';
        if (!empty($state['send_sms']))      $channels[] = 'SMS';

        $delay    = $parts ? implode(' ', $parts) . ' after appointment' : 'Immediately';
        $channel  = $channels ? ' via ' . implode(' + ', $channels) : '';

        return $delay . $channel;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->where('user_id', auth()->id()))
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('steps_count')
                    ->label('Steps')
                    ->counts('steps')
                    ->badge(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Last updated')
                    ->since()
                    ->sortable(),
            ])
            ->filters([])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ])
            ->defaultSort('name');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListAftercareSequences::route('/'),
            'create' => Pages\CreateAftercareSequence::route('/create'),
            'edit'   => Pages\EditAftercareSequence::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('user_id', auth()->id());
    }

    protected static function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id();
        return $data;
    }
}
