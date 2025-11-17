<x-filament-panels::page>
    <div class="relative">
        @if (\Saasykit\FilamentOnboarding\FilamentOnboardingPlugin::get()->isSkippable())
            <a wire:click="skip" class="cursor-pointer text-sm text-neutral-500 hover:text-neutral-900 absolute top-0 right-0 -mt-16 -mr-4">
                {{ __('Skip Onboarding') }}
            </a>
        @endif

        <h1 class="text-3xl font-bold text-center">{{ __('Welcome to :app_name', ['app_name' => config('app.name')]) }}!</h1>
        <p class="text-center mt-4 text-neutral-500">{{ __('Let\'s get started by setting up your account.') }}</p>
    </div>
    <form wire:submit="submit">
        {{ $this->form }}
        
        @if(count($this->getFormActions()) > 0)
            <div class="mt-6">
                <x-filament::actions
                    :actions="$this->getFormActions()"
                    alignment="end"
                />
            </div>
        @endif

        <x-filament-actions::modals />
    </form>
</x-filament-panels::page>
