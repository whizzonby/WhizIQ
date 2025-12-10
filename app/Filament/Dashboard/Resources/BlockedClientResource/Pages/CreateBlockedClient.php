<?php

namespace App\Filament\Dashboard\Resources\BlockedClientResource\Pages;

use App\Filament\Dashboard\Resources\BlockedClientResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBlockedClient extends CreateRecord
{
    protected static string $resource = BlockedClientResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id();

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
