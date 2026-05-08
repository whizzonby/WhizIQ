<?php

namespace App\Livewire;

use Livewire\Component;

class WelcomeModal extends Component
{
    public bool $show = false;
    public string $selected = 'backfill';

    public function mount(): void
    {
        $this->show = (bool) session()->pull('show_welcome_modal', false);
    }

    public function select(string $option): void
    {
        $this->selected = $option;
    }

    public function skip(): void
    {
        $this->show = false;
    }

    public function getStarted(): void
    {
        $this->show = false;

        $url = match ($this->selected) {
            'backfill', 'last14' => route('filament.dashboard.resources.expenses.index'),
            'fresh'              => route('filament.dashboard.resources.revenue-sources.index'),
            default              => route('filament.dashboard.pages.dashboard'),
        };

        $this->redirect($url, navigate: true);
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.welcome-modal');
    }
}
