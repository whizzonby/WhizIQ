<?php

namespace App\Filament\Dashboard\Resources;

use App\Filament\Dashboard\Resources\ReceiptResource\Pages;
use App\Models\Receipt;
use App\Services\ReceiptPDFService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class ReceiptResource extends Resource
{
    protected static ?string $model = Receipt::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-receipt-percent';

    protected static ?string $navigationLabel = 'Receipts';

    protected static UnitEnum|string|null $navigationGroup = 'Money';

    protected static ?int $navigationSort = 2;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('user_id', auth()->id());
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('receipt_number')
                    ->label('Receipt #')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('customer_display')
                    ->label('Customer')
                    ->getStateUsing(fn (Receipt $record) =>
                        $record->client?->name ?? $record->customer_name ?? 'Walk-in')
                    ->searchable(query: fn (Builder $query, string $search) =>
                        $query->where('customer_name', 'like', "%{$search}%")
                              ->orWhereHas('client', fn ($q) => $q->where('name', 'like', "%{$search}%")))
                    ->sortable(query: fn (Builder $query, string $direction) =>
                        $query->orderBy('customer_name', $direction)),

                Tables\Columns\TextColumn::make('receipt_date')
                    ->label('Date')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('payment_method')
                    ->label('Payment')
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'cash'          => 'Cash',
                        'credit_card'   => 'Credit Card',
                        'debit_card'    => 'Debit Card',
                        'bank_transfer' => 'Bank Transfer',
                        'check'         => 'Check',
                        'paypal'        => 'PayPal',
                        'stripe'        => 'Stripe',
                        default         => ucfirst($state),
                    })
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'cash'          => 'success',
                        'credit_card',
                        'debit_card'    => 'info',
                        'bank_transfer' => 'warning',
                        default         => 'gray',
                    }),

                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Total')
                    ->money(fn (Receipt $record) => $record->currency ?? 'USD')
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('template')
                    ->label('Style')
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'thermal' => 'Thermal',
                        default   => 'Standard',
                    })
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([])
            ->actions([
                Action::make('edit')
                    ->label('Edit')
                    ->icon('heroicon-o-pencil')
                    ->color('gray')
                    ->url(fn (Receipt $record) => route('filament.dashboard.pages.receipt-builder-page') . '?receipt=' . $record->id),

                Action::make('download')
                    ->label('Download PDF')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->action(function (Receipt $record) {
                        return response()->streamDownload(
                            fn () => print(app(ReceiptPDFService::class)->generate($record)),
                            $record->receipt_number . '.pdf',
                            ['Content-Type' => 'application/pdf']
                        );
                    }),

                DeleteAction::make(),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReceipts::route('/'),
        ];
    }
}
