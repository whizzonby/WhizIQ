<?php

namespace App\Filament\Dashboard\Resources\ReceiptResource\Pages;

use App\Filament\Dashboard\Resources\ReceiptResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;

class ListReceipts extends ListRecords
{
    protected static string $resource = ReceiptResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('create_receipt')
                ->label('New Receipt')
                ->icon('heroicon-o-plus')
                ->color('primary')
                ->url(route('filament.dashboard.pages.receipt-builder-page')),
        ];
    }
}
