<?php

namespace App\Filament\Dashboard\Resources\BlockedClientResource\Pages;

use App\Filament\Dashboard\Resources\BlockedClientResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBlockedClients extends ListRecords
{
    protected static string $resource = BlockedClientResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
