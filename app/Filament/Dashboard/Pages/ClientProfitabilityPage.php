<?php

namespace App\Filament\Dashboard\Pages;

use App\Models\InvoiceClient;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Tables;
use Illuminate\Support\Facades\DB;
use UnitEnum;

class ClientProfitabilityPage extends Page implements HasTable
{
    use InteractsWithTable;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-currency-dollar';

    protected static ?string $navigationLabel = 'Client Profitability';

    protected static ?string $title = 'Client Profitability';

    protected static UnitEnum|string|null $navigationGroup = 'Clients';

    protected static ?int $navigationSort = 10;

    protected static bool $isLazy = true;

    protected string $view = 'filament.dashboard.pages.client-profitability-page';

    public function table(Table $table): Table
    {
        $userId = auth()->id();

        return $table
            ->query(
                InvoiceClient::query()
                    ->select([
                        'invoice_clients.*',
                        DB::raw('COALESCE(SUM(CASE WHEN ci.status NOT IN (\'draft\',\'cancelled\') THEN ci.total_amount ELSE 0 END), 0) as total_invoiced'),
                        DB::raw('COALESCE(SUM(CASE WHEN ci.status NOT IN (\'draft\',\'cancelled\') THEN ci.amount_paid ELSE 0 END), 0) as total_collected'),
                        DB::raw('COALESCE(SUM(CASE WHEN ci.status NOT IN (\'draft\',\'cancelled\') THEN (ci.total_amount - ci.amount_paid) ELSE 0 END), 0) as total_outstanding'),
                        DB::raw('COUNT(CASE WHEN ci.status NOT IN (\'draft\',\'cancelled\') THEN ci.id END) as invoice_count'),
                        DB::raw('COUNT(CASE WHEN ci.status = \'overdue\' THEN ci.id END) as overdue_count'),
                        DB::raw('MAX(ci.created_at) as last_invoice_at'),
                    ])
                    ->leftJoin('client_invoices as ci', 'ci.invoice_client_id', '=', 'invoice_clients.id')
                    ->where('invoice_clients.user_id', $userId)
                    ->groupBy('invoice_clients.id')
                    ->having(DB::raw('COUNT(CASE WHEN ci.status NOT IN (\'draft\',\'cancelled\') THEN ci.id END)'), '>', 0)
                    ->with('contact')
                    ->orderByDesc('total_invoiced')
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Client')
                    ->description(fn (InvoiceClient $record): ?string => $record->contact?->company ?? $record->email)
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('invoice_count')
                    ->label('Invoices')
                    ->alignCenter()
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_invoiced')
                    ->label('Total Invoiced')
                    ->money(fn () => \App\Models\BookingSetting::where('user_id', auth()->id())->value('currency') ?? 'USD')
                    ->sortable()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('total_collected')
                    ->label('Collected')
                    ->money(fn () => \App\Models\BookingSetting::where('user_id', auth()->id())->value('currency') ?? 'USD')
                    ->sortable()
                    ->color('success'),

                Tables\Columns\TextColumn::make('total_outstanding')
                    ->label('Outstanding')
                    ->money(fn () => \App\Models\BookingSetting::where('user_id', auth()->id())->value('currency') ?? 'USD')
                    ->sortable()
                    ->color(fn (InvoiceClient $record): string =>
                        $record->total_outstanding > 0 ? 'danger' : 'gray'
                    ),

                Tables\Columns\TextColumn::make('collection_rate')
                    ->label('Collected %')
                    ->getStateUsing(fn (InvoiceClient $record): string =>
                        $record->total_invoiced > 0
                            ? number_format(($record->total_collected / $record->total_invoiced) * 100, 1) . '%'
                            : '—'
                    )
                    ->badge()
                    ->color(fn (InvoiceClient $record): string => match(true) {
                        $record->total_invoiced == 0 => 'gray',
                        ($record->total_collected / $record->total_invoiced) >= 0.9 => 'success',
                        ($record->total_collected / $record->total_invoiced) >= 0.6 => 'warning',
                        default => 'danger',
                    })
                    ->alignCenter(),

                Tables\Columns\IconColumn::make('overdue_count')
                    ->label('Overdue')
                    ->getStateUsing(fn (InvoiceClient $record): bool => $record->overdue_count > 0)
                    ->boolean()
                    ->trueIcon('heroicon-o-exclamation-triangle')
                    ->falseIcon('heroicon-o-check-circle')
                    ->trueColor('danger')
                    ->falseColor('success')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('last_invoice_at')
                    ->label('Last Invoice')
                    ->since()
                    ->sortable()
                    ->color('gray'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('collection_health')
                    ->label('Payment Health')
                    ->options([
                        'has_outstanding' => 'Has Outstanding Balance',
                        'has_overdue'     => 'Has Overdue Invoices',
                        'fully_paid'      => 'Fully Paid Clients',
                    ])
                    ->query(function ($query, array $data) {
                        return match ($data['value'] ?? null) {
                            'has_outstanding' => $query->having('total_outstanding', '>', 0),
                            'has_overdue'     => $query->having('overdue_count', '>', 0),
                            'fully_paid'      => $query->having('total_outstanding', '=', 0),
                            default           => $query,
                        };
                    }),
            ])
            ->defaultSort('total_invoiced', 'desc')
            ->striped()
            ->paginated([15, 25, 50]);
    }

    public function getSummaryStats(): array
    {
        $userId = auth()->id();

        $stats = DB::table('invoice_clients')
            ->leftJoin('client_invoices as ci', function ($join) {
                $join->on('ci.invoice_client_id', '=', 'invoice_clients.id')
                    ->whereNotIn('ci.status', ['draft', 'cancelled']);
            })
            ->where('invoice_clients.user_id', $userId)
            ->selectRaw('
                COUNT(DISTINCT invoice_clients.id) as total_clients,
                COALESCE(SUM(ci.total_amount), 0) as total_invoiced,
                COALESCE(SUM(ci.amount_paid), 0) as total_collected,
                COALESCE(SUM(ci.total_amount - ci.amount_paid), 0) as total_outstanding
            ')
            ->first();

        return [
            'total_clients'    => $stats->total_clients ?? 0,
            'total_invoiced'   => $stats->total_invoiced ?? 0,
            'total_collected'  => $stats->total_collected ?? 0,
            'total_outstanding'=> $stats->total_outstanding ?? 0,
            'collection_rate'  => $stats->total_invoiced > 0
                ? round(($stats->total_collected / $stats->total_invoiced) * 100, 1)
                : 0,
        ];
    }
}
