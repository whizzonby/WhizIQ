<?php

namespace App\Filament\Dashboard\Pages;

use App\Models\ClientInvoice;
use App\Models\ClientInvoiceItem;
use App\Models\Currency;
use App\Models\InvoiceClient;
use App\Models\Quote;
use App\Models\QuoteItem;
use App\Services\QuotePDFService;
use BackedEnum;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\DB;
use UnitEnum;

class QuoteBuilderPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-document-currency-dollar';

    protected string $view = 'filament.dashboard.pages.quote-builder-page';

    protected static ?string $navigationLabel = 'Quote Builder';

    protected static UnitEnum|string|null $navigationGroup = 'Money';

    protected static ?int $navigationSort = 3;

    public ?array $data = [];

    public string $template     = 'modern';
    public string $primaryColor = '#3b82f6';
    public string $accentColor  = '#10b981';
    public ?int   $quoteId      = null;

    public function getTitle(): string
    {
        return $this->quoteId ? 'Edit Quote' : 'Quote Builder';
    }

    public function mount(): void
    {
        $quoteId = request()->query('quote');

        if ($quoteId) {
            $quote = Quote::with(['client', 'items'])
                ->where('user_id', auth()->id())
                ->findOrFail($quoteId);

            $this->quoteId      = $quote->id;
            $this->template     = $quote->template ?? 'modern';
            $this->primaryColor = $quote->primary_color ?? '#3b82f6';
            $this->accentColor  = $quote->accent_color ?? '#10b981';

            $this->form->fill([
                'invoice_client_id' => $quote->invoice_client_id,
                'quote_number'      => $quote->quote_number,
                'quote_date'        => $quote->quote_date,
                'valid_until'       => $quote->valid_until,
                'currency'          => $quote->currency,
                'tax_rate'          => $quote->tax_rate,
                'discount_amount'   => $quote->discount_amount,
                'notes'             => $quote->notes,
                'terms'             => $quote->terms,
                'footer'            => $quote->footer,
                'items'             => $quote->items->map(fn ($i) => [
                    'description' => $i->description,
                    'details'     => $i->details,
                    'quantity'    => $i->quantity,
                    'unit_price'  => $i->unit_price,
                    'amount'      => $i->amount,
                ])->toArray(),
            ]);
        } else {
            $this->form->fill([
                'quote_date'      => now(),
                'valid_until'     => now()->addDays(30),
                'currency'        => 'USD',
                'tax_rate'        => 0,
                'discount_amount' => 0,
                'items'           => [['description' => '', 'quantity' => 1, 'unit_price' => 0, 'amount' => 0]],
            ]);
        }
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Quote Builder')
                    ->tabs([
                        Tabs\Tab::make('Quote Details')
                            ->icon('heroicon-o-document-text')
                            ->schema([
                                Grid::make(2)->schema([
                                    Forms\Components\Select::make('invoice_client_id')
                                        ->label('Select Client')
                                        ->options(fn () => InvoiceClient::where('user_id', auth()->id())
                                            ->where('is_active', true)
                                            ->pluck('name', 'id'))
                                        ->searchable()
                                        ->preload()
                                        ->required()
                                        ->live()
                                        ->createOptionForm([
                                            Forms\Components\TextInput::make('name')->required()->maxLength(255),
                                            Forms\Components\TextInput::make('email')->email()->maxLength(255),
                                            Forms\Components\TextInput::make('company')->maxLength(255),
                                            Forms\Components\TextInput::make('phone')->tel()->maxLength(255),
                                        ])
                                        ->createOptionUsing(function (array $data) {
                                            $data['user_id'] = auth()->id();
                                            $client = InvoiceClient::create($data);
                                            Notification::make()->title('Client created')->success()->send();
                                            return $client->id;
                                        }),

                                    Forms\Components\TextInput::make('quote_number')
                                        ->label('Quote Number')
                                        ->default(fn () => Quote::generateQuoteNumber())
                                        ->required()
                                        ->maxLength(255)
                                        ->live()
                                        ->rule(function () {
                                            return function (string $attribute, $value, \Closure $fail) {
                                                if ($value && Quote::where('quote_number', $value)
                                                    ->where('user_id', auth()->id())
                                                    ->when($this->quoteId, fn ($q) => $q->where('id', '!=', $this->quoteId))
                                                    ->exists()) {
                                                    $fail('This quote number is already in use.');
                                                }
                                            };
                                        }),
                                ]),

                                Grid::make(3)->schema([
                                    Forms\Components\DatePicker::make('quote_date')
                                        ->label('Quote Date')
                                        ->default(now())
                                        ->required()
                                        ->live(),

                                    Forms\Components\DatePicker::make('valid_until')
                                        ->label('Valid Until')
                                        ->default(now()->addDays(30))
                                        ->live(),

                                    Forms\Components\Select::make('currency')
                                        ->label('Currency')
                                        ->options(Currency::getSelectOptions())
                                        ->searchable()
                                        ->default('USD')
                                        ->native(false)
                                        ->required()
                                        ->live(),
                                ]),
                            ]),

                        Tabs\Tab::make('Line Items')
                            ->icon('heroicon-o-list-bullet')
                            ->schema([
                                Forms\Components\Repeater::make('items')
                                    ->label('Quote Items')
                                    ->schema([
                                        Forms\Components\TextInput::make('description')
                                            ->label('Item / Service Description')
                                            ->required()
                                            ->placeholder('e.g., Website Redesign')
                                            ->columnSpanFull()
                                            ->live(),

                                        Forms\Components\Textarea::make('details')
                                            ->label('Additional Details (Optional)')
                                            ->rows(2)
                                            ->columnSpanFull()
                                            ->live(),

                                        Grid::make(4)->schema([
                                            Forms\Components\TextInput::make('quantity')
                                                ->label('Qty')
                                                ->numeric()
                                                ->default(1)
                                                ->minValue(0.01)
                                                ->required()
                                                ->live(onBlur: true)
                                                ->afterStateUpdated(fn ($state, callable $set, Get $get) =>
                                                    $set('amount', $state * ($get('unit_price') ?? 0))),

                                            Forms\Components\TextInput::make('unit_price')
                                                ->label('Unit Price')
                                                ->numeric()
                                                ->prefix('$')
                                                ->live(onBlur: true)
                                                ->afterStateUpdated(fn ($state, callable $set, Get $get) =>
                                                    $set('amount', $state * ($get('quantity') ?? 1))),

                                            Forms\Components\TextInput::make('amount')
                                                ->label('Line Total')
                                                ->numeric()
                                                ->prefix('$')
                                                ->disabled()
                                                ->dehydrated()
                                                ->required()
                                                ->columnSpan(2),
                                        ]),
                                    ])
                                    ->reorderable()
                                    ->collapsible()
                                    ->itemLabel(fn (array $state): ?string => $state['description'] ?? 'New Item')
                                    ->defaultItems(1)
                                    ->addActionLabel('+ Add Item')
                                    ->cloneable()
                                    ->columnSpanFull()
                                    ->live(),
                            ]),

                        Tabs\Tab::make('Pricing & Design')
                            ->icon('heroicon-o-paint-brush')
                            ->schema([
                                Section::make('Pricing')->schema([
                                    Grid::make(2)->schema([
                                        Forms\Components\TextInput::make('tax_rate')
                                            ->label('Tax Rate (%)')
                                            ->numeric()->suffix('%')->default(0)
                                            ->minValue(0)->maxValue(100)->live(),

                                        Forms\Components\TextInput::make('discount_amount')
                                            ->label('Discount Amount ($)')
                                            ->numeric()->prefix('$')->default(0)->minValue(0)->live(),
                                    ]),
                                ]),

                                Section::make('Quote Design')->schema([
                                    Forms\Components\Radio::make('template')
                                        ->label('Choose Template')
                                        ->options([
                                            'modern'  => 'Modern Blue',
                                            'elegant' => 'Elegant Purple',
                                            'minimal' => 'Minimal Gray',
                                            'vibrant' => 'Vibrant Green',
                                        ])
                                        ->default('modern')
                                        ->inline()
                                        ->live()
                                        ->afterStateUpdated(fn ($state) => $this->template = $state),

                                    Grid::make(2)->schema([
                                        Forms\Components\ColorPicker::make('primary_color')
                                            ->label('Primary Color')
                                            ->default('#3b82f6')
                                            ->live()
                                            ->afterStateUpdated(fn ($state) => $this->primaryColor = $state),

                                        Forms\Components\ColorPicker::make('accent_color')
                                            ->label('Accent Color')
                                            ->default('#10b981')
                                            ->live()
                                            ->afterStateUpdated(fn ($state) => $this->accentColor = $state),
                                    ]),
                                ]),
                            ]),

                        Tabs\Tab::make('Notes & Terms')
                            ->icon('heroicon-o-information-circle')
                            ->schema([
                                Forms\Components\Textarea::make('notes')
                                    ->label('Quote Notes')
                                    ->rows(3)
                                    ->placeholder('Add special notes or clarifications...')
                                    ->columnSpanFull()->live(),

                                Forms\Components\Textarea::make('terms')
                                    ->label('Terms & Conditions')
                                    ->rows(3)
                                    ->placeholder('e.g., Quote valid for 30 days. 50% deposit required.')
                                    ->columnSpanFull()->live(),

                                Forms\Components\Textarea::make('footer')
                                    ->label('Quote Footer')
                                    ->rows(2)
                                    ->placeholder('Thank you for your business!')
                                    ->columnSpanFull()->live(),
                            ]),
                    ])
                    ->columnSpanFull()
                    ->contained(false),
            ])
            ->statePath('data');
    }

    public function saveDraft(): void
    {
        try {
            $data = $this->form->getState();
            $this->validateQuoteData($data);

            if ($this->quoteId) {
                $quote = $this->updateQuote($this->quoteId, $data, 'draft');
                $msg = "Quote {$quote->quote_number} updated.";
            } else {
                $quote = $this->createQuote($data, 'draft');
                $msg = "Quote {$quote->quote_number} saved as draft.";
            }

            Notification::make()->title('Quote Saved')->success()->body($msg)->send();
            $this->redirect(route('filament.dashboard.resources.quotes.index'));
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            Notification::make()->title('Error')->danger()->body('Could not save quote.')->send();
            \Log::error('Quote save error', ['error' => $e->getMessage()]);
        }
    }

    public function saveAndSend(): void
    {
        try {
            $data = $this->form->getState();
            $this->validateQuoteData($data);

            if ($this->quoteId) {
                $quote = $this->updateQuote($this->quoteId, $data, 'draft');
            } else {
                $quote = $this->createQuote($data, 'draft');
            }

            $pdfService = app(QuotePDFService::class);
            $sent = $pdfService->emailToClient($quote);

            if ($sent) {
                $quote->status = 'sent';
                $quote->save();
                Notification::make()->title('Quote Sent!')->success()
                    ->body("Quote {$quote->quote_number} sent to the client.")->send();
            } else {
                Notification::make()->title('Quote Saved – Email Failed')->warning()
                    ->body("Quote {$quote->quote_number} was saved but email could not be sent.")->send();
            }

            $this->redirect(route('filament.dashboard.resources.quotes.index'));
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            Notification::make()->title('Error')->danger()->body('Could not process quote.')->send();
            \Log::error('Quote send error', ['error' => $e->getMessage()]);
        }
    }

    public function convertToInvoice(): void
    {
        if (!$this->quoteId) {
            Notification::make()->title('Save First')->warning()->body('Save the quote before converting.')->send();
            return;
        }

        try {
            $data = $this->form->getState();
            $this->validateQuoteData($data);

            $quote = $this->updateQuote($this->quoteId, $data, 'accepted');

            DB::transaction(function () use ($quote) {
                $invoice = ClientInvoice::create([
                    'user_id'           => auth()->id(),
                    'invoice_client_id' => $quote->invoice_client_id,
                    'invoice_number'    => ClientInvoice::generateInvoiceNumber(),
                    'status'            => 'draft',
                    'invoice_date'      => now(),
                    'due_date'          => now()->addDays(30),
                    'currency'          => $quote->currency,
                    'tax_rate'          => $quote->tax_rate,
                    'discount_amount'   => $quote->discount_amount,
                    'notes'             => $quote->notes,
                    'terms'             => $quote->terms,
                    'footer'            => $quote->footer,
                    'template'          => $quote->template,
                    'primary_color'     => $quote->primary_color,
                    'accent_color'      => $quote->accent_color,
                    'subtotal'          => $quote->subtotal,
                    'tax_amount'        => $quote->tax_amount,
                    'total_amount'      => $quote->total_amount,
                    'balance_due'       => $quote->total_amount,
                ]);

                foreach ($quote->items as $index => $item) {
                    $invoice->items()->create([
                        'description' => $item->description,
                        'details'     => $item->details,
                        'quantity'    => $item->quantity,
                        'unit_price'  => $item->unit_price,
                        'amount'      => $item->amount,
                        'sort_order'  => $index + 1,
                    ]);
                }

                Notification::make()
                    ->title('Converted to Invoice!')
                    ->success()
                    ->body("Invoice {$invoice->invoice_number} created from quote {$quote->quote_number}.")
                    ->send();

                $this->redirect(route('filament.dashboard.pages.invoice-builder-page', ['invoice' => $invoice->id]));
            });
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            Notification::make()->title('Error')->danger()->body('Could not convert quote.')->send();
            \Log::error('Quote convert error', ['error' => $e->getMessage()]);
        }
    }

    protected function createQuote(array $data, string $status): Quote
    {
        return DB::transaction(function () use ($data, $status) {
            $quote = Quote::create([
                'user_id'           => auth()->id(),
                'invoice_client_id' => $data['invoice_client_id'],
                'quote_number'      => $data['quote_number'],
                'status'            => $status,
                'quote_date'        => $data['quote_date'],
                'valid_until'       => $data['valid_until'] ?? null,
                'currency'          => $data['currency'],
                'tax_rate'          => $data['tax_rate'] ?? 0,
                'discount_amount'   => $data['discount_amount'] ?? 0,
                'notes'             => $data['notes'] ?? null,
                'terms'             => $data['terms'] ?? null,
                'footer'            => $data['footer'] ?? null,
                'template'          => $this->template,
                'primary_color'     => $this->primaryColor,
                'accent_color'      => $this->accentColor,
                'subtotal'          => 0,
                'tax_amount'        => 0,
                'total_amount'      => 0,
            ]);

            $subtotal = 0;
            foreach ($data['items'] as $index => $item) {
                $quote->items()->create([
                    'description' => $item['description'],
                    'details'     => $item['details'] ?? null,
                    'quantity'    => $item['quantity'],
                    'unit_price'  => $item['unit_price'],
                    'amount'      => $item['amount'],
                    'sort_order'  => $index + 1,
                ]);
                $subtotal += $item['amount'];
            }

            $discounted = $subtotal - ($data['discount_amount'] ?? 0);
            $taxAmount  = ($discounted * ($data['tax_rate'] ?? 0)) / 100;

            $quote->update([
                'subtotal'     => $subtotal,
                'tax_amount'   => $taxAmount,
                'total_amount' => $discounted + $taxAmount,
            ]);

            return $quote->load(['client', 'items', 'user']);
        });
    }

    protected function updateQuote(int $quoteId, array $data, string $status): Quote
    {
        return DB::transaction(function () use ($quoteId, $data, $status) {
            $quote = Quote::findOrFail($quoteId);

            $quote->update([
                'invoice_client_id' => $data['invoice_client_id'],
                'quote_number'      => $data['quote_number'],
                'status'            => $status,
                'quote_date'        => $data['quote_date'],
                'valid_until'       => $data['valid_until'] ?? null,
                'currency'          => $data['currency'],
                'tax_rate'          => $data['tax_rate'] ?? 0,
                'discount_amount'   => $data['discount_amount'] ?? 0,
                'notes'             => $data['notes'] ?? null,
                'terms'             => $data['terms'] ?? null,
                'footer'            => $data['footer'] ?? null,
                'template'          => $this->template,
                'primary_color'     => $this->primaryColor,
                'accent_color'      => $this->accentColor,
            ]);

            $quote->items()->delete();

            $subtotal = 0;
            foreach ($data['items'] as $index => $item) {
                $quote->items()->create([
                    'description' => $item['description'],
                    'details'     => $item['details'] ?? null,
                    'quantity'    => $item['quantity'],
                    'unit_price'  => $item['unit_price'],
                    'amount'      => $item['amount'],
                    'sort_order'  => $index + 1,
                ]);
                $subtotal += $item['amount'];
            }

            $discounted = $subtotal - $quote->discount_amount;
            $taxAmount  = ($discounted * $quote->tax_rate) / 100;

            $quote->update([
                'subtotal'     => $subtotal,
                'tax_amount'   => $taxAmount,
                'total_amount' => $discounted + $taxAmount,
            ]);

            return $quote->load(['client', 'items', 'user']);
        });
    }

    protected function validateQuoteData(array $data): void
    {
        if (empty($data['invoice_client_id'])) {
            throw \Illuminate\Validation\ValidationException::withMessages(['invoice_client_id' => 'Please select a client.']);
        }
        if (empty($data['quote_number'])) {
            throw \Illuminate\Validation\ValidationException::withMessages(['quote_number' => 'Quote number is required.']);
        }
        if (empty($data['items']) || !is_array($data['items'])) {
            throw \Illuminate\Validation\ValidationException::withMessages(['items' => 'At least one item is required.']);
        }
        foreach ($data['items'] as $i => $item) {
            if (empty($item['description'])) {
                throw \Illuminate\Validation\ValidationException::withMessages(["items.{$i}.description" => 'Item description is required.']);
            }
        }
    }
}
