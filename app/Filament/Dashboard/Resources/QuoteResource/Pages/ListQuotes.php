<?php

namespace App\Filament\Dashboard\Resources\QuoteResource\Pages;

use App\Filament\Dashboard\Resources\QuoteResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;

class ListQuotes extends ListRecords
{
    protected static string $resource = QuoteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('new_quote')
                ->label('New Quote')
                ->icon('heroicon-o-plus')
                ->url(route('filament.dashboard.pages.quote-builder-page')),
        ];
    }
}
