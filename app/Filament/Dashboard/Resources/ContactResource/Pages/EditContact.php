<?php

namespace App\Filament\Dashboard\Resources\ContactResource\Pages;

use App\Filament\Dashboard\Resources\ContactResource;
use App\Models\InvoiceClient;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditContact extends EditRecord
{
    protected static string $resource = ContactResource::class;

    protected function getHeaderActions(): array
    {
        $record = $this->getRecord();

        $hasInvoices = InvoiceClient::where('contact_id', $record->id)->exists();

        return [
            Action::make('view_profitability')
                ->label('View Profitability')
                ->icon('heroicon-o-currency-dollar')
                ->color('success')
                ->url(route('filament.dashboard.pages.client-profitability-page'))
                ->visible($hasInvoices),

            DeleteAction::make(),
        ];
    }
}
