@php
    $rating = (float) ($rating ?? 0);
    $size = $size ?? 'w-4 h-4';
    $color = $color ?? 'text-amber-400';
@endphp
<span class="inline-flex items-center gap-0.5 {{ $color }}" aria-hidden="true">
    @for ($i = 1; $i <= 5; $i++)
        <svg class="{{ $size }}" viewBox="0 0 20 20" fill="{{ $i <= round($rating) ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="1.2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10 1.5l2.6 5.27 5.82.85-4.21 4.1.99 5.8L10 14.9l-5.2 2.73.99-5.8-4.21-4.1 5.82-.85L10 1.5z"/>
        </svg>
    @endfor
</span>
