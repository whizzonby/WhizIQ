<?php

namespace App\Filament\Dashboard\Resources\FinanceResource\Widgets;

use App\Models\Expense;
use App\Models\RevenueSource;
use Carbon\Carbon;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\DB;

class FinanceImportHistoryWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected static bool $isLazy = true;

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Recent Expenses')
            ->query(
                Expense::query()
                    ->where('user_id', auth()->id())
                    ->orderByDesc('created_at')
                    ->limit(50)
            )
            ->columns([
                Tables\Columns\TextColumn::make('date')
                    ->date()
                    ->sortable()
                    ->label('Date'),
                Tables\Columns\TextColumn::make('category')
                    ->label('Category')
                    ->formatStateUsing(fn (string $state): string => ucwords(str_replace('_', ' ', $state)))
                    ->searchable(),
                Tables\Columns\TextColumn::make('description')
                    ->limit(50)
                    ->searchable()
                    ->wrap(),
                Tables\Columns\TextColumn::make('amount')
                    ->money('USD')
                    ->sortable()
                    ->color('danger'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Imported')
                    ->dateTime()
                    ->sortable()
                    ->since()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated([10, 25, 50]);
    }
}
