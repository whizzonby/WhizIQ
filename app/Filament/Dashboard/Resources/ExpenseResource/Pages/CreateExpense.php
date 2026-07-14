<?php

namespace App\Filament\Dashboard\Resources\ExpenseResource\Pages;

use App\Filament\Dashboard\Resources\ExpenseResource;
use App\Filament\Dashboard\Resources\Subscriptions\SubscriptionResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateExpense extends CreateRecord
{
    protected static string $resource = ExpenseResource::class;

    public function mount(): void
    {
        if (!ExpenseResource::canCreate()) {
            Notification::make()
                ->title('Subscription Required')
                ->body('Please subscribe to a plan to add expenses. Choose a plan that fits your needs!')
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
        return ExpenseResource::getUrl('index');
    }
}
