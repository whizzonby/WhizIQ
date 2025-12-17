<li {{ $attributes->merge(['class' => 'flex gap-2 items-start']) }}>
    <span class="flex-shrink-0 p-1 bg-primary-50 rounded-full h-6 w-6 flex items-center justify-center">@svg('check', 'stroke-black')</span>
    <span class="flex-1 text-left">{{ $slot }}</span>
</li>
