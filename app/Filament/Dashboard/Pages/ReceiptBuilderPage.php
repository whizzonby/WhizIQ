<?php

namespace App\Filament\Dashboard\Pages;

use App\Models\ClientInvoice;
use App\Models\Currency;
use App\Models\InvoiceClient;
use App\Models\Receipt;
use App\Services\ReceiptPDFService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\DB;
use UnitEnum;

class ReceiptBuilderPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-receipt-percent';
    protected static ?string $navigationLabel    = 'Receipt Builder';
    protected static ?string $title             = 'Receipt Builder';
    protected static UnitEnum|string|null $navigationGroup = 'Money';
    protected static ?int $navigationSort       = 1;
    protected string $view = 'filament.dashboard.pages.receipt-builder-page';

    public ?array $data = [];
    public string $template     = 'standard';
    public string $primaryColor = '#10b981';
    public ?int   $receiptId    = null;
    public bool   $showCashFields = false;

    public function getTitle(): string
    {
        return $this->receiptId ? 'Edit Receipt' : 'Receipt Builder';
    }

    public function mount(): void
    {
        $receiptId = request()->query('receipt');

        if ($receiptId) {
            $receipt = Receipt::where('user_id', auth()->id())->findOrFail($receiptId);
            $this->receiptId    = $receipt->id;
            $this->template     = $receipt->template;
            $this->primaryColor = $receipt->primary_color;

            $this->form->fill([
                'receipt_number'      => $receipt->receipt_number,
                'receipt_date'        => $receipt->receipt_date,
                'invoice_client_id'   => $receipt->invoice_client_id,
                'customer_name'       => $receipt->customer_name,
                'customer_email'      => $receipt->customer_email,
                'customer_phone'      => $receipt->customer_phone,
                'payment_method'      => $receipt->payment_method,
                'transaction_reference' => $receipt->transaction_reference,
                'amount_tendered'     => $receipt->amount_tendered,
                'currency'            => $receipt->currency,
                'tax_rate'            => $receipt->tax_rate,
                'discount_amount'     => $receipt->discount_amount,
                'notes'               => $receipt->notes,
                'items'               => $receipt->items ?? [],
            ]);

            $this->showCashFields = $receipt->payment_method === 'cash';
        } else {
            // Check if pre-filling from an invoice
            $invoiceId = request()->query('from_invoice');
            $defaults  = $this->getDefaults($invoiceId);
            $this->form->fill($defaults);
        }
    }

    // ── Form ────────────────────────────────────────────────────────────────────

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([

                Section::make('Receipt Details')
                    ->icon('heroicon-o-identification')
                    ->schema([
                        Grid::make(3)->schema([
                            TextInput::make('receipt_number')
                                ->label('Receipt Number')
                                ->required()
                                ->default(Receipt::generateReceiptNumber(auth()->id())),

                            DatePicker::make('receipt_date')
                                ->label('Date')
                                ->required()
                                ->default(now()),

                            Select::make('currency')
                                ->label('Currency')
                                ->options(Currency::getSelectOptions())
                                ->default('USD')
                                ->searchable()
                                ->required(),
                        ]),
                    ]),

                Section::make('Customer')
                    ->icon('heroicon-o-user')
                    ->description('Choose an existing client or enter a walk-in customer.')
                    ->schema([
                        Select::make('invoice_client_id')
                            ->label('Existing Client')
                            ->options(fn () => InvoiceClient::where('user_id', auth()->id())
                                ->orderBy('name')
                                ->pluck('name', 'id'))
                            ->searchable()
                            ->placeholder('Select a client (optional)')
                            ->live(),

                        Grid::make(3)->schema([
                            TextInput::make('customer_name')
                                ->label('Walk-in Name')
                                ->placeholder('e.g. John Smith'),

                            TextInput::make('customer_email')
                                ->label('Email')
                                ->email()
                                ->placeholder('optional'),

                            TextInput::make('customer_phone')
                                ->label('Phone')
                                ->tel()
                                ->placeholder('optional'),
                        ]),
                    ]),

                Section::make('Payment')
                    ->icon('heroicon-o-credit-card')
                    ->schema([
                        Grid::make(2)->schema([
                            Select::make('payment_method')
                                ->label('Payment Method')
                                ->options([
                                    'cash'          => 'Cash',
                                    'credit_card'   => 'Credit Card',
                                    'debit_card'    => 'Debit Card',
                                    'bank_transfer' => 'Bank Transfer',
                                    'check'         => 'Check',
                                    'paypal'        => 'PayPal',
                                    'stripe'        => 'Stripe',
                                    'other'         => 'Other',
                                ])
                                ->default('cash')
                                ->required()
                                ->live()
                                ->afterStateUpdated(fn ($state) => $this->showCashFields = ($state === 'cash')),

                            TextInput::make('transaction_reference')
                                ->label('Transaction / Reference No.')
                                ->placeholder('optional'),
                        ]),

                        Grid::make(2)->schema([
                            TextInput::make('amount_tendered')
                                ->label('Cash Tendered')
                                ->numeric()
                                ->minValue(0)
                                ->placeholder('0.00')
                                ->visible(fn ($get) => $get('payment_method') === 'cash')
                                ->live(onBlur: true)
                                ->helperText('Leave blank if exact amount'),

                            TextInput::make('change_due_display')
                                ->label('Change Due')
                                ->disabled()
                                ->visible(fn ($get) => $get('payment_method') === 'cash')
                                ->dehydrated(false),
                        ]),
                    ]),

                Section::make('Items')
                    ->icon('heroicon-o-list-bullet')
                    ->schema([
                        Repeater::make('items')
                            ->label('')
                            ->schema([
                                Grid::make(4)->schema([
                                    TextInput::make('description')
                                        ->label('Description')
                                        ->required()
                                        ->columnSpan(2),

                                    TextInput::make('quantity')
                                        ->label('Qty')
                                        ->numeric()
                                        ->default(1)
                                        ->minValue(0.001)
                                        ->live(onBlur: true)
                                        ->afterStateUpdated(fn ($state, $get, $set) =>
                                            $set('amount', round((float)$state * (float)$get('unit_price'), 2))),

                                    TextInput::make('unit_price')
                                        ->label('Price')
                                        ->numeric()
                                        ->default(0)
                                        ->minValue(0)
                                        ->prefix('$')
                                        ->live(onBlur: true)
                                        ->afterStateUpdated(fn ($state, $get, $set) =>
                                            $set('amount', round((float)$get('quantity') * (float)$state, 2))),
                                ]),

                                TextInput::make('amount')
                                    ->label('Total')
                                    ->numeric()
                                    ->default(0)
                                    ->readOnly()
                                    ->prefix('$'),
                            ])
                            ->defaultItems(1)
                            ->addActionLabel('Add Item')
                            ->reorderable()
                            ->collapsible(),
                    ]),

                Section::make('Totals & Notes')
                    ->icon('heroicon-o-calculator')
                    ->schema([
                        Grid::make(3)->schema([
                            TextInput::make('tax_rate')
                                ->label('Tax Rate (%)')
                                ->numeric()
                                ->default(0)
                                ->minValue(0)
                                ->maxValue(100)
                                ->suffix('%')
                                ->live(onBlur: true),

                            TextInput::make('discount_amount')
                                ->label('Discount')
                                ->numeric()
                                ->default(0)
                                ->minValue(0)
                                ->prefix('$')
                                ->live(onBlur: true),

                            ColorPicker::make('primary_color_field')
                                ->label('Receipt Colour')
                                ->default('#10b981')
                                ->live()
                                ->afterStateUpdated(fn ($state) => $this->primaryColor = $state),
                        ]),

                        Textarea::make('notes')
                            ->label('Notes / Thank-you message')
                            ->rows(2)
                            ->placeholder('Thank you for your service!'),
                    ]),

            ])
            ->statePath('data');
    }

    // ── Header Actions ───────────────────────────────────────────────────────────

    protected function getHeaderActions(): array
    {
        return [
            Action::make('change_template')
                ->label(fn () => $this->template === 'thermal' ? 'Switch to Standard' : 'Switch to Thermal')
                ->icon('heroicon-o-arrows-right-left')
                ->color('gray')
                ->action(function () {
                    $this->template = $this->template === 'standard' ? 'thermal' : 'standard';
                }),

            Action::make('save')
                ->label('Save Receipt')
                ->icon('heroicon-o-document-check')
                ->color('primary')
                ->action('save'),

            Action::make('download')
                ->label('Download PDF')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->action('downloadPdf')
                ->visible(fn () => (bool) $this->receiptId),
        ];
    }

    // ── Actions ──────────────────────────────────────────────────────────────────

    public function save(): void
    {
        $data = $this->form->getState();

        $items   = $data['items'] ?? [];
        $subtotal = collect($items)->sum(fn ($i) => (float) ($i['amount'] ?? 0));
        $discounted = $subtotal - (float) ($data['discount_amount'] ?? 0);
        $taxAmount  = ($discounted * (float) ($data['tax_rate'] ?? 0)) / 100;
        $total      = $discounted + $taxAmount;

        $tendered  = (float) ($data['amount_tendered'] ?? 0);
        $changeDue = $tendered > 0 ? max(0, $tendered - $total) : 0;

        $payload = [
            'user_id'               => auth()->id(),
            'receipt_number'        => $data['receipt_number'],
            'receipt_date'          => $data['receipt_date'],
            'invoice_client_id'     => $data['invoice_client_id'] ?? null,
            'customer_name'         => $data['customer_name'] ?? null,
            'customer_email'        => $data['customer_email'] ?? null,
            'customer_phone'        => $data['customer_phone'] ?? null,
            'payment_method'        => $data['payment_method'],
            'transaction_reference' => $data['transaction_reference'] ?? null,
            'amount_tendered'       => $tendered ?: null,
            'change_due'            => $changeDue ?: null,
            'currency'              => $data['currency'],
            'subtotal'              => $subtotal,
            'tax_rate'              => $data['tax_rate'] ?? 0,
            'tax_amount'            => $taxAmount,
            'discount_amount'       => $data['discount_amount'] ?? 0,
            'total_amount'          => $total,
            'items'                 => $items,
            'notes'                 => $data['notes'] ?? null,
            'template'              => $this->template,
            'primary_color'         => $this->primaryColor,
        ];

        if ($this->receiptId) {
            Receipt::where('user_id', auth()->id())->where('id', $this->receiptId)->update($payload);
        } else {
            $receipt = Receipt::create($payload);
            $this->receiptId = $receipt->id;
        }

        Notification::make()->title('Receipt saved')->success()->send();
    }

    public function downloadPdf(): mixed
    {
        if (! $this->receiptId) {
            Notification::make()->title('Save first')->warning()->body('Save the receipt before downloading.')->send();
            return null;
        }

        $this->save();

        $receipt = Receipt::where('user_id', auth()->id())->findOrFail($this->receiptId);

        return response()->streamDownload(
            fn () => print(app(ReceiptPDFService::class)->generate($receipt)),
            $receipt->receipt_number . '.pdf',
            ['Content-Type' => 'application/pdf']
        );
    }

    // ── Private helpers ──────────────────────────────────────────────────────────

    private function getDefaults(?string $invoiceId): array
    {
        $defaults = [
            'receipt_number' => Receipt::generateReceiptNumber(auth()->id()),
            'receipt_date'   => now()->toDateString(),
            'currency'       => 'USD',
            'tax_rate'       => 0,
            'discount_amount'=> 0,
            'payment_method' => 'cash',
            'items'          => [['description' => '', 'quantity' => 1, 'unit_price' => 0, 'amount' => 0]],
        ];

        if ($invoiceId) {
            $invoice = ClientInvoice::with(['client', 'items'])
                ->where('user_id', auth()->id())
                ->find($invoiceId);

            if ($invoice) {
                $defaults['invoice_client_id'] = $invoice->invoice_client_id;
                $defaults['currency']          = $invoice->currency;
                $defaults['tax_rate']          = $invoice->tax_rate;
                $defaults['discount_amount']   = $invoice->discount_amount;
                $defaults['linked_invoice_id'] = $invoice->id;
                $defaults['notes']             = "Payment for invoice {$invoice->invoice_number}";
                $defaults['items']             = $invoice->items->map(fn ($i) => [
                    'description' => $i->description,
                    'details'     => $i->details ?? '',
                    'quantity'    => $i->quantity,
                    'unit_price'  => $i->unit_price,
                    'amount'      => $i->amount,
                ])->toArray();
            }
        }

        return $defaults;
    }
}
