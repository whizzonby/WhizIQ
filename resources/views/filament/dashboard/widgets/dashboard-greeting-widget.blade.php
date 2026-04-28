@php $d = $this->getGreetingData(); @endphp

<div style="padding:2rem 0 1.5rem;">
    <p class="text-gray-500 dark:text-gray-500" style="font-size:.8rem;font-weight:500;margin-bottom:.5rem;letter-spacing:.02em;">
        {{ $d['date'] }}
    </p>

    <h1 class="text-gray-900 dark:text-white" style="font-size:1.9rem;font-weight:800;letter-spacing:-.025em;line-height:1.2;margin-bottom:.5rem;">
        Hey {{ $d['name'] }}, good {{ $d['time_of_day'] }}.
    </h1>

    @if($d['attention_count'] > 0)
        <p class="text-violet-600 dark:text-violet-400" style="font-size:.975rem;font-weight:500;">
            You have {{ $d['attention_count'] }} {{ $d['attention_count'] === 1 ? 'thing' : 'things' }} that need your attention today.
        </p>
    @else
        <p class="text-green-600 dark:text-green-400" style="font-size:.975rem;font-weight:500;">
            You're all caught up — nothing needs your attention right now.
        </p>
    @endif
</div>
