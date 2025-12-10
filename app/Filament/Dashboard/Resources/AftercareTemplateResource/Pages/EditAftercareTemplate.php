<?php

namespace App\Filament\Dashboard\Resources\AftercareTemplateResource\Pages;

use App\Filament\Dashboard\Resources\AftercareTemplateResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAftercareTemplate extends EditRecord
{
    protected static string $resource = AftercareTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
