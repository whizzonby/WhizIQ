<x-layouts.app>

    <x-slot name="title">
        {{ $page->title }}
    </x-slot>

    @if($page->meta_description)
        <x-slot name="description">
            {{ $page->meta_description }}
        </x-slot>
    @endif

    <div class="max-w-6xl mx-auto px-4 py-8">
        <x-heading.h1 class="md:text-4xl! text-4xl! pt-4 pb-6">
            {{ $page->title }}
        </x-heading.h1>

        @if($page->featured_image)
            <div class="mb-8">
                <img src="{{ Storage::url($page->featured_image) }}"
                     alt="{{ $page->title }}"
                     class="w-full h-auto rounded-lg shadow-lg">
            </div>
        @endif

        <div class="prose prose-lg max-w-none">
            {!! $page->content_html !!}
        </div>

        @if($page->published_at)
            <div class="mt-8 pt-6 border-t border-gray-200 text-sm text-gray-500">
                {{ __('Last updated') }}: {{ $page->published_at->format('F j, Y') }}
            </div>
        @endif
    </div>

</x-layouts.app>
