<?php

namespace App\Filament\Dashboard\Resources\AftercareSequenceResource\Pages;

use App\Filament\Dashboard\Resources\AftercareSequenceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAftercareSequences extends ListRecords
{
    protected static string $resource = AftercareSequenceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
