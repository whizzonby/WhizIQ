<?php

namespace App\Filament\Admin\Resources\WhatsAppAdminConfigResource\Pages;

use App\Filament\Admin\Resources\WhatsAppAdminConfigResource;
use App\Models\WhatsAppAdminConfig;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;
use Filament\Notifications\Notification;

class ManageWhatsAppAdminConfig extends ManageRecords
{
    protected static string $resource = WhatsAppAdminConfigResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Configure WhatsApp')
                ->visible(fn () => !WhatsAppAdminConfig::exists())
                ->mutateFormDataUsing(function (array $data): array {
                    if ($data['is_active'] ?? false) {
                        $data['configured_at'] = now();
                        $data['last_verified_at'] = now();
                    }
                    return $data;
                })
                ->after(function () {
                    // Clear cache after creating admin config
                    \App\Models\WhatsAppAdminConfig::clearCache();
                }),
        ];
    }

    public function mount(): void
    {
        parent::mount();

        // Auto-create config if it doesn't exist
        if (!WhatsAppAdminConfig::exists()) {
            WhatsAppAdminConfig::create([
                'api_version' => 'v21.0',
                'is_active' => false,
            ]);
        }
    }
}

