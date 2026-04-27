<?php

namespace App\Filament\Dashboard\Widgets;

use App\Models\AftercareLog;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class AftercareActivityWidget extends BaseWidget
{
    protected static ?int $sort = 10;

    protected static bool $isLazy = true;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Aftercare Activity';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                AftercareLog::query()
                    ->where('user_id', auth()->id())
                    ->with(['appointment', 'sequenceStep.sequence'])
                    ->latest('scheduled_at')
                    ->limit(50)
            )
            ->columns([
                Tables\Columns\TextColumn::make('appointment.attendee_name')
                    ->label('Client')
                    ->default('—')
                    ->searchable(),

                Tables\Columns\TextColumn::make('channel')
                    ->label('Channel')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'whatsapp' => 'success',
                        'sms'      => 'info',
                        'email'    => 'warning',
                        default    => 'gray',
                    })
                    ->formatStateUsing(fn (string $state) => ucfirst($state)),

                Tables\Columns\TextColumn::make('sequenceStep.sequence.name')
                    ->label('Sequence / Template')
                    ->default(fn ($record) => $record->template?->name ?? '—')
                    ->limit(30),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'sent'    => 'success',
                        'pending' => 'warning',
                        'failed'  => 'danger',
                        'skipped' => 'gray',
                        default   => 'gray',
                    })
                    ->formatStateUsing(fn (string $state) => ucfirst($state)),

                Tables\Columns\TextColumn::make('scheduled_at')
                    ->label('Scheduled')
                    ->since()
                    ->sortable(),

                Tables\Columns\TextColumn::make('sent_at')
                    ->label('Sent')
                    ->since()
                    ->placeholder('—')
                    ->sortable(),

                Tables\Columns\TextColumn::make('error_message')
                    ->label('Note')
                    ->limit(40)
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('scheduled_at', 'desc')
            ->paginated([10, 25, 50])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'sent'    => 'Sent',
                        'failed'  => 'Failed',
                        'skipped' => 'Skipped',
                    ]),

                Tables\Filters\SelectFilter::make('channel')
                    ->options([
                        'whatsapp' => 'WhatsApp',
                        'sms'      => 'SMS',
                        'email'    => 'Email',
                    ]),
            ])
            ->emptyStateHeading('No aftercare activity yet')
            ->emptyStateDescription('Messages will appear here once appointments are completed and aftercare is triggered.')
            ->emptyStateIcon('heroicon-o-chat-bubble-left-right');
    }
}
