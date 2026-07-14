<div class="text-sm">
    @if ($state === 'trialing')
        <div class="bg-blue-50 text-blue-900 text-center px-4">
            <div class="mx-auto py-3 flex gap-2 md:gap-8 items-center justify-center">
                <span class="line-clamp-3 md:line-clamp-2">
                    {{ $daysLeft }} {{ Str::plural('day', $daysLeft) }} left in your {{ $planName ?? 'free trial' }} &mdash; choose a plan to keep going without interruption.
                </span>
                <a href="{{ $ctaUrl }}" class="font-semibold whitespace-nowrap underline underline-offset-2 hover:opacity-80 transition-opacity">
                    {{ __('Choose a plan') }}
                </a>
            </div>
        </div>
    @elseif ($state === 'locked')
        <div class="bg-amber-50 text-amber-900 text-center px-4">
            <div class="mx-auto py-3 flex gap-2 md:gap-8 items-center justify-center">
                <span class="line-clamp-3 md:line-clamp-2">
                    Your free trial has ended. You can still view everything you've added &mdash; choose a plan to keep creating new records.
                </span>
                <a href="{{ $ctaUrl }}" class="font-semibold whitespace-nowrap underline underline-offset-2 hover:opacity-80 transition-opacity">
                    {{ __('Choose a plan') }}
                </a>
            </div>
        </div>
    @elseif ($state === 'no-plan')
        <div class="bg-amber-50 text-amber-900 text-center px-4">
            <div class="mx-auto py-3 flex gap-2 md:gap-8 items-center justify-center">
                <span class="line-clamp-3 md:line-clamp-2">
                    You don't have an active plan yet. You can still view everything you've added &mdash; choose a plan to start creating new records.
                </span>
                <a href="{{ $ctaUrl }}" class="font-semibold whitespace-nowrap underline underline-offset-2 hover:opacity-80 transition-opacity">
                    {{ __('Choose a plan') }}
                </a>
            </div>
        </div>
    @endif
</div>
