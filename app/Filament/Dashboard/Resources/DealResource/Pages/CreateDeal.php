<?php

namespace App\Filament\Dashboard\Resources\DealResource\Pages;

use App\Filament\Dashboard\Resources\DealResource;
use App\Filament\Dashboard\Resources\Subscriptions\SubscriptionResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateDeal extends CreateRecord
{
    protected static string $resource = DealResource::class;

    public function mount(): void
    {
        if (!DealResource::canCreate()) {
            Notification::make()
                ->title('Subscription Required')
                ->body('Please subscribe to a plan to create deals. Choose a plan that fits your needs!')
                ->warning()
                ->persistent()
                ->send();

            $this->redirect(SubscriptionResource::getUrl('index'));
        }
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id();

        // Ensure required fields have default values
        $data['stage'] = $data['stage'] ?? 'lead';
        $data['value'] = $data['value'] ?? 0;
        $data['priority'] = $data['priority'] ?? 'medium';
        $data['currency'] = $data['currency'] ?? 'USD';
        $data['probability'] = $data['probability'] ?? 50;
        $data['expected_close_date'] = $data['expected_close_date'] ?? now()->addDays(30)->format('Y-m-d');

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return DealResource::getUrl('index');
    }
}
