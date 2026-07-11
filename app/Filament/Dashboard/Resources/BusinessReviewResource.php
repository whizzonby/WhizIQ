<?php

namespace App\Filament\Dashboard\Resources;

use App\Filament\Dashboard\Resources\BusinessReviewResource\Pages;
use App\Models\BusinessReview;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;
use BackedEnum;

class BusinessReviewResource extends Resource
{
    protected static ?string $model = BusinessReview::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-star';

    protected static ?string $navigationLabel = 'Reviews';

    protected static UnitEnum|string|null $navigationGroup = 'Clients';

    protected static ?int $navigationSort = 5;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\Placeholder::make('rating')
                    ->label('Rating')
                    ->content(fn (BusinessReview $record): string => str_repeat('*', $record->rating) . " ({$record->rating}/5)"),

                Forms\Components\Placeholder::make('comment')
                    ->label('Comment')
                    ->content(fn (BusinessReview $record): string => $record->comment ?: '—'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\IconColumn::make('is_approved')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-s-check-circle')
                    ->falseIcon('heroicon-s-clock')
                    ->trueColor('success')
                    ->falseColor('warning')
                    ->tooltip(fn (BusinessReview $record): string => $record->is_approved ? 'Live on booking page' : 'Awaiting your approval'),

                Tables\Columns\TextColumn::make('display_name')
                    ->label('From')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('rating')
                    ->label('Rating')
                    ->formatStateUsing(fn (int $state): string => str_repeat('*', $state))
                    ->badge()
                    ->color(fn (int $state): string => $state >= 4 ? 'success' : ($state === 3 ? 'warning' : 'danger'))
                    ->sortable(),

                Tables\Columns\TextColumn::make('comment')
                    ->label('Comment')
                    ->limit(60)
                    ->placeholder('—')
                    ->wrap(),

                Tables\Columns\TextColumn::make('appointmentType.name')
                    ->label('Service')
                    ->placeholder('—')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Submitted')
                    ->dateTime('M d, Y g:i A')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_approved')
                    ->label('Status')
                    ->placeholder('All reviews')
                    ->trueLabel('Approved only')
                    ->falseLabel('Pending approval only'),
            ])
            ->actions([
                Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->action(function (BusinessReview $record) {
                        $record->update([
                            'is_approved' => true,
                            'approved_at' => now(),
                        ]);

                        \Filament\Notifications\Notification::make()
                            ->title('Review approved')
                            ->body('This review is now visible on your booking page.')
                            ->success()
                            ->send();
                    })
                    ->visible(fn (BusinessReview $record) => ! $record->is_approved)
                    ->requiresConfirmation(),

                Action::make('unapprove')
                    ->label('Hide')
                    ->icon('heroicon-o-eye-slash')
                    ->color('warning')
                    ->action(function (BusinessReview $record) {
                        $record->update([
                            'is_approved' => false,
                            'approved_at' => null,
                        ]);

                        \Filament\Notifications\Notification::make()
                            ->title('Review hidden')
                            ->body('This review is no longer visible on your booking page.')
                            ->warning()
                            ->send();
                    })
                    ->visible(fn (BusinessReview $record) => $record->is_approved)
                    ->requiresConfirmation(),

                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('No reviews yet')
            ->emptyStateDescription('Reviews collected after completed appointments will appear here for your approval before they go live.')
            ->emptyStateIcon('heroicon-o-star');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBusinessReviews::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('user_id', auth()->id());
    }

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::where('user_id', auth()->id())
            ->where('is_approved', false)
            ->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }
}
