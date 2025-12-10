<?php

namespace App\Filament\Dashboard\Resources\BlockedClientResource\Pages;

use App\Filament\Dashboard\Resources\BlockedClientResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBlockedClient extends EditRecord
{
    protected static string $resource = BlockedClientResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Convert empty string or "Permanent" to null for blocked_until
        if (isset($data['blocked_until']) && (empty($data['blocked_until']) || $data['blocked_until'] === 'Permanent')) {
            $data['blocked_until'] = null;
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
