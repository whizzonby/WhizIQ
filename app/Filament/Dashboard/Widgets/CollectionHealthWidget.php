<?php

namespace App\Filament\Dashboard\Widgets;

use App\Models\ClientInvoice;
use App\Models\ClientPayment;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Cache;

class CollectionHealthWidget extends Widget
{
    protected static ?int $sort = 4;

    protected static bool $isLazy = true;

    protected int|string|array $columnSpan = 'full';

    protected string $view = 'filament.dashboard.widgets.collection-health-widget';

    public function getData(): array
    {
        $user     = auth()->user();
        $cacheKey = "collection_health_{$user->id}_" . now()->format('Y-m-d-H');

        return Cache::remember($cacheKey, 1800, function () use ($user) {
            $totalInvoiced = (float) ClientInvoice::where('user_id', $user->id)
                ->whereIn('status', ['sent', 'partial', 'overdue', 'paid'])
                ->whereBetween('invoice_date', [now()->startOfMonth(), now()->endOfMonth()])
                ->sum('total_amount');

            $cashCollected = (float) ClientPayment::where('user_id', $user->id)
                ->whereBetween('payment_date', [now()->startOfMonth(), now()->endOfMonth()])
                ->sum('amount');

            $outstanding = (float) ClientInvoice::where('user_id', $user->id)
                ->whereIn('status', ['sent', 'partial', 'overdue'])
                ->selectRaw('COALESCE(SUM(total_amount - amount_paid), 0) as total')
                ->value('total');

            $overdueAmount = (float) ClientInvoice::where('user_id', $user->id)
                ->where('status', 'overdue')
                ->selectRaw('COALESCE(SUM(total_amount - amount_paid), 0) as total')
                ->value('total');

            $oldestOverdueDays = (int) ClientInvoice::where('user_id', $user->id)
                ->where('status', 'overdue')
                ->whereNotNull('due_date')
                ->selectRaw('COALESCE(MAX(DATEDIFF(CURDATE(), due_date)), 0) as days')
                ->value('days');

            $base = max($totalInvoiced, $outstanding + $cashCollected, 1);

            return [
                'total_invoiced'      => $totalInvoiced,
                'cash_collected'      => $cashCollected,
                'outstanding'         => $outstanding,
                'overdue_amount'      => $overdueAmount,
                'oldest_overdue_days' => $oldestOverdueDays,
                'collected_pct'       => min(100, round(($cashCollected / $base) * 100, 1)),
                'outstanding_pct'     => min(100, round(($outstanding / $base) * 100, 1)),
                'overdue_pct'         => $outstanding > 0
                    ? min(100, round(($overdueAmount / $outstanding) * 100, 1))
                    : 0,
                'month_name'          => now()->format('F Y'),
            ];
        });
    }
}
