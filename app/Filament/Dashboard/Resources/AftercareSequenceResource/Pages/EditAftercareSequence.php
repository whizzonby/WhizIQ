<?php

namespace App\Filament\Dashboard\Resources\AftercareSequenceResource\Pages;

use App\Filament\Dashboard\Resources\AftercareSequenceResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAftercareSequence extends EditRecord
{
    protected static string $resource = AftercareSequenceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
