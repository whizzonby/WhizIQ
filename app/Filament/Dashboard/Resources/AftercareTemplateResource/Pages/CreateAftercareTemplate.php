<?php

namespace App\Filament\Dashboard\Resources\AftercareTemplateResource\Pages;

use App\Filament\Dashboard\Resources\AftercareTemplateResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAftercareTemplate extends CreateRecord
{
    protected static string $resource = AftercareTemplateResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id();

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
