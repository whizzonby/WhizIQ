<?php

namespace App\Filament\Dashboard\Resources;

use App\Filament\Dashboard\Resources\QuoteResource\Pages;
use App\Models\Quote;
use App\Services\QuotePDFService;
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

class QuoteResource extends Resource
{
    protected static ?string $model = Quote::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-document-currency-dollar';

    protected static ?string $navigationLabel = 'Quotes';

    protected static UnitEnum|string|null $navigationGroup = 'Money';

    protected static ?int $navigationSort = 4;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('user_id', auth()->id())->with('client');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('quote_number')
                    ->label('Quote #')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('client.name')
                    ->label('Client')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('quote_date')
                    ->label('Date')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('valid_until')
                    ->label('Valid Until')
                    ->date()
                    ->sortable()
                    ->color(fn (Quote $record) => $record->isExpired() ? 'danger' : null),

                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Total')
                    ->money(fn (Quote $record) => strtolower($record->currency))
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'gray'    => 'draft',
                        'info'    => 'sent',
                        'success' => 'accepted',
                        'danger'  => ['rejected', 'expired'],
                    ])
                    ->formatStateUsing(fn (string $state) => ucfirst($state)),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft'    => 'Draft',
                        'sent'     => 'Sent',
                        'accepted' => 'Accepted',
                        'rejected' => 'Rejected',
                        'expired'  => 'Expired',
                    ]),
            ])
            ->actions([
                Action::make('edit')
                    ->label('Edit')
                    ->icon('heroicon-o-pencil')
                    ->url(fn (Quote $record) => route('filament.dashboard.pages.quote-builder-page') . '?quote=' . $record->id),

                Action::make('download')
                    ->label('PDF')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->action(function (Quote $record) {
                        return app(QuotePDFService::class)->download($record);
                    }),

                Action::make('convert')
                    ->label('→ Invoice')
                    ->icon('heroicon-o-document-plus')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Convert to Invoice')
                    ->modalDescription('This will create a new draft invoice pre-filled with all items from this quote. The quote will be marked as Accepted.')
                    ->action(function (Quote $record) {
                        try {
                            $record->load(['items', 'user']);

                            $invoice = \App\Models\ClientInvoice::create([
                                'user_id'           => auth()->id(),
                                'invoice_client_id' => $record->invoice_client_id,
                                'invoice_number'    => \App\Models\ClientInvoice::generateInvoiceNumber(),
                                'status'            => 'draft',
                                'invoice_date'      => now(),
                                'due_date'          => now()->addDays(30),
                                'currency'          => $record->currency,
                                'tax_rate'          => $record->tax_rate,
                                'discount_amount'   => $record->discount_amount,
                                'notes'             => $record->notes,
                                'terms'             => $record->terms,
                                'footer'            => $record->footer,
                                'template'          => $record->template,
                                'primary_color'     => $record->primary_color,
                                'accent_color'      => $record->accent_color,
                                'subtotal'          => $record->subtotal,
                                'tax_amount'        => $record->tax_amount,
                                'total_amount'      => $record->total_amount,
                                'balance_due'       => $record->total_amount,
                            ]);

                            foreach ($record->items as $idx => $item) {
                                $invoice->items()->create([
                                    'description' => $item->description,
                                    'details'     => $item->details,
                                    'quantity'    => $item->quantity,
                                    'unit_price'  => $item->unit_price,
                                    'amount'      => $item->amount,
                                    'sort_order'  => $idx + 1,
                                ]);
                            }

                            $record->update(['status' => 'accepted']);

                            Notification::make()
                                ->title('Converted!')
                                ->success()
                                ->body("Invoice {$invoice->invoice_number} created. Opening in builder...")
                                ->send();

                            return redirect(route('filament.dashboard.pages.invoice-builder-page') . '?invoice=' . $invoice->id);
                        } catch (\Exception $e) {
                            Notification::make()->title('Error')->danger()->body('Could not convert quote.')->send();
                            \Log::error('Quote list convert error', ['error' => $e->getMessage()]);
                        }
                    }),

                DeleteAction::make(),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListQuotes::route('/'),
        ];
    }
}
