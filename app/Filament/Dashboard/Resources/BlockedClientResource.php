<?php

namespace App\Filament\Dashboard\Resources;

use App\Filament\Dashboard\Resources\BlockedClientResource\Pages;
use App\Models\BlockedClient;
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
use Filament\Actions\Action;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;
use BackedEnum;

class BlockedClientResource extends Resource
{
    protected static ?string $model = BlockedClient::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-shield-exclamation';

    protected static ?string $navigationLabel = 'Blocked Clients';

    protected static UnitEnum|string|null $navigationGroup = 'Booking';

    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Client Information')
                    ->description('Identify the client to block')
                    ->icon('heroicon-o-user')
                    ->schema([
                        Forms\Components\Select::make('contact_id')
                            ->label('Existing Contact')
                            ->relationship('contact', 'name', fn ($query) =>
                                $query->where('user_id', auth()->id())
                            )
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state) {
                                    $contact = \App\Models\Contact::find($state);
                                    if ($contact) {
                                        $set('email', $contact->email);
                                        $set('phone', $contact->phone);
                                        $set('name', $contact->name);
                                    }
                                }
                            })
                            ->helperText('Select an existing contact, or enter guest details below'),

                        Forms\Components\TextInput::make('name')
                            ->label('Guest Name')
                            ->maxLength(255)
                            ->visible(fn ($get) => !$get('contact_id'))
                            ->helperText('For non-contact bookings'),

                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->maxLength(255)
                            ->helperText('Block all bookings from this email'),

                        Forms\Components\TextInput::make('phone')
                            ->label('Phone')
                            ->tel()
                            ->maxLength(255)
                            ->helperText('Block all bookings from this phone number'),
                    ])
                    ->columns(2),

                Section::make('Violation Details')
                    ->description('Reason for blocking this client')
                    ->icon('heroicon-o-exclamation-triangle')
                    ->schema([
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

                        Forms\Components\DateTimePicker::make('violation_date')
                            ->label('Violation Date')
                            ->required()
                            ->default(now())
                            ->maxDate(now()),

                        Forms\Components\Textarea::make('violation_details')
                            ->label('Details')
                            ->rows(3)
                            ->required()
                            ->maxLength(1000)
                            ->helperText('Provide specific details about the violation')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Block Settings')
                    ->description('Configure the block duration and status')
                    ->icon('heroicon-o-lock-closed')
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->label('Block is Active')
                            ->default(true)
                            ->helperText('Uncheck to temporarily lift the block'),

                        Forms\Components\DateTimePicker::make('blocked_until')
                            ->label('Block Until (Optional)')
                            ->native(false)
                            ->nullable()
                            ->helperText('Leave blank for permanent block, or set date for temporary block')
                            ->visible(fn ($get) => $get('is_active')),
                    ])
                    ->columns(2),

                Section::make('Resolution')
                    ->description('Track when and how the block was resolved')
                    ->icon('heroicon-o-check-circle')
                    ->schema([
                        Forms\Components\Textarea::make('resolution_notes')
                            ->label('Resolution Notes')
                            ->rows(3)
                            ->maxLength(1000)
                            ->helperText('Document how this issue was resolved'),

                        Forms\Components\DateTimePicker::make('resolved_at')
                            ->label('Resolved At')
                            ->disabled(),
                    ])
                    ->collapsed()
                    ->visible(fn ($get) => !$get('is_active') || $get('resolved_at')),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-s-lock-closed')
                    ->falseIcon('heroicon-s-lock-open')
                    ->trueColor('danger')
                    ->falseColor('success')
                    ->tooltip(fn (BlockedClient $record): string =>
                        $record->is_active ? 'Blocked' : 'Unblocked'
                    ),

                Tables\Columns\TextColumn::make('contact.name')
                    ->label('Contact')
                    ->searchable()
                    ->sortable()
                    ->default('—')
                    ->description(fn (BlockedClient $record): ?string =>
                        $record->contact_id ? null : ($record->email ?? $record->phone ?? 'Guest')
                    ),

                Tables\Columns\TextColumn::make('name')
                    ->label('Guest Name')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->default('—'),

                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->toggleable()
                    ->default('—'),

                Tables\Columns\TextColumn::make('phone')
                    ->label('Phone')
                    ->searchable()
                    ->toggleable()
                    ->default('—'),

                Tables\Columns\TextColumn::make('violation_type')
                    ->label('Violation')
                    ->badge()
                    ->color(fn (BlockedClient $record): string => $record->violation_type_color)
                    ->formatStateUsing(fn (BlockedClient $record): string => $record->violation_type_label)
                    ->sortable(),

                Tables\Columns\TextColumn::make('violation_date')
                    ->label('Date')
                    ->dateTime('M d, Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('blocked_until')
                    ->label('Until')
                    ->dateTime('M d, Y')
                    ->placeholder('Permanent')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Blocked On')
                    ->dateTime('M d, Y g:i A')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('violation_type')
                    ->label('Violation Type')
                    ->options([
                        'no_show' => 'No Show',
                        'late_cancellation' => 'Late Cancellation',
                        'repeated_reschedule' => 'Repeated Reschedule',
                        'inappropriate_behavior' => 'Inappropriate Behavior',
                        'payment_issue' => 'Payment Issue',
                        'other' => 'Other',
                    ])
                    ->multiple(),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status')
                    ->placeholder('All blocks')
                    ->trueLabel('Active blocks only')
                    ->falseLabel('Resolved blocks only'),

                Tables\Filters\Filter::make('temporary')
                    ->label('Temporary Blocks')
                    ->query(fn (Builder $query) => $query->whereNotNull('blocked_until')),
            ])
            ->actions([
                Action::make('resolve')
                    ->label('Resolve')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->form([
                        Forms\Components\Textarea::make('resolution_notes')
                            ->label('Resolution Notes')
                            ->required()
                            ->rows(3)
                            ->helperText('Explain how this issue was resolved'),
                    ])
                    ->action(function (BlockedClient $record, array $data) {
                        $record->resolve($data['resolution_notes']);

                        \Filament\Notifications\Notification::make()
                            ->title('Block Resolved')
                            ->success()
                            ->body('The client block has been resolved.')
                            ->send();
                    })
                    ->visible(fn (BlockedClient $record) => $record->is_active)
                    ->requiresConfirmation(),

                Action::make('reactivate')
                    ->label('Reactivate')
                    ->icon('heroicon-o-lock-closed')
                    ->color('danger')
                    ->action(function (BlockedClient $record) {
                        $record->update([
                            'is_active' => true,
                            'resolved_at' => null,
                            'resolution_notes' => null,
                        ]);

                        \Filament\Notifications\Notification::make()
                            ->title('Block Reactivated')
                            ->warning()
                            ->body('The client block has been reactivated.')
                            ->send();
                    })
                    ->visible(fn (BlockedClient $record) => !$record->is_active)
                    ->requiresConfirmation(),

                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('No blocked clients')
            ->emptyStateDescription('Block clients who violate booking policies')
            ->emptyStateIcon('heroicon-o-shield-exclamation');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBlockedClients::route('/'),
            'create' => Pages\CreateBlockedClient::route('/create'),
            'edit' => Pages\EditBlockedClient::route('/{record}/edit'),
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
        return 'danger';
    }
}
