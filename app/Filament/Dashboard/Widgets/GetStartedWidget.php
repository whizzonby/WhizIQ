<?php

namespace App\Filament\Dashboard\Widgets;

use App\Models\Appointment;
use App\Models\ClientInvoice;
use App\Models\Contact;
use Filament\Widgets\Widget;

class GetStartedWidget extends Widget
{
    protected static ?int $sort = -10;

    protected int|string|array $columnSpan = 'full';

    protected string $view = 'filament.dashboard.widgets.get-started-widget';

    /**
     * Only show this widget to users who registered within the last 7 days
     * and have fewer than 3 contacts — i.e. they haven't really started yet.
     */
    public static function canView(): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        $isNew = $user->created_at?->gt(now()->subDays(7));
        $hasData = Contact::where('user_id', $user->id)->exists()
            || ClientInvoice::where('user_id', $user->id)->exists()
            || Appointment::where('user_id', $user->id)->exists();

        return $isNew && ! $hasData;
    }

    public function getSteps(): array
    {
        return [
            [
                'icon'  => 'heroicon-o-user-plus',
                'label' => 'Add your first client',
                'url'   => route('filament.dashboard.resources.contacts.create'),
                'color' => 'text-blue-600',
                'bg'    => 'bg-blue-50',
            ],
            [
                'icon'  => 'heroicon-o-document-plus',
                'label' => 'Create an invoice',
                'url'   => route('filament.dashboard.pages.invoice-builder-page'),
                'color' => 'text-emerald-600',
                'bg'    => 'bg-emerald-50',
            ],
            [
                'icon'  => 'heroicon-o-calendar-plus',
                'label' => 'Book an appointment',
                'url'   => route('filament.dashboard.resources.appointments.create'),
                'color' => 'text-violet-600',
                'bg'    => 'bg-violet-50',
            ],
            [
                'icon'  => 'heroicon-o-link',
                'label' => 'Set up your booking page',
                'url'   => route('filament.dashboard.pages.booking-settings-page'),
                'color' => 'text-orange-600',
                'bg'    => 'bg-orange-50',
            ],
        ];
    }
}
