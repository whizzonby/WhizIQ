<?php

namespace App\Filament\Admin\Resources\WhatsAppAdminConfigResource\Pages;

use App\Filament\Admin\Resources\WhatsAppAdminConfigResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditWhatsAppAdminConfig extends EditRecord
{
    protected static string $resource = WhatsAppAdminConfigResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->disabled(), // Don't allow deletion since it's a singleton
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // If activating, mark as configured
        if (($data['is_active'] ?? false) && !$this->record->is_active) {
            $data['configured_at'] = now();
            $data['last_verified_at'] = now();
        }

        return $data;
    }

    protected function afterSave(): void
    {
        // Clear cache after saving admin config
        \App\Models\WhatsAppAdminConfig::clearCache();
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->title('WhatsApp Configuration Updated')
            ->body('The WhatsApp Business API configuration has been saved successfully.')
            ->success();
    }
}

