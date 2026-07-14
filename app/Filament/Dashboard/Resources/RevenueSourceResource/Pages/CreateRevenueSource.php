<?php

namespace App\Filament\Dashboard\Resources\RevenueSourceResource\Pages;

use App\Filament\Dashboard\Resources\RevenueSourceResource;
use App\Filament\Dashboard\Resources\Subscriptions\SubscriptionResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateRevenueSource extends CreateRecord
{
    protected static string $resource = RevenueSourceResource::class;

    public function mount(): void
    {
        if (!RevenueSourceResource::canCreate()) {
            Notification::make()
                ->title('Subscription Required')
                ->body('Please subscribe to a plan to add revenue sources. Choose a plan that fits your needs!')
                ->warning()
                ->persistent()
                ->send();

            $this->redirect(SubscriptionResource::getUrl('index'));

            return;
        }

        parent::mount();
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id();

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return RevenueSourceResource::getUrl('index');
    }
}
