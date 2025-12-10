<?php

namespace App\Filament\Dashboard\Resources\AftercareTemplateResource\Pages;

use App\Filament\Dashboard\Resources\AftercareTemplateResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAftercareTemplates extends ListRecords
{
    protected static string $resource = AftercareTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
