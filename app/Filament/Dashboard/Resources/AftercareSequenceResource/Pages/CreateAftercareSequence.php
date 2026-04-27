<?php

namespace App\Filament\Dashboard\Resources\AftercareSequenceResource\Pages;

use App\Filament\Dashboard\Resources\AftercareSequenceResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAftercareSequence extends CreateRecord
{
    protected static string $resource = AftercareSequenceResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id();
        return $data;
    }
}
