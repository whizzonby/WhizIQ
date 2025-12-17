<x-layouts.app>

    @push('head')
        @vite(['resources/js/blog.js'])
    @endpush

    <x-slot name="title">{{ $post->title }}</x-slot>

    @if(!empty($post->description))
        <x-slot name="description">{{ $post->description }}</x-slot>
    @endif

    <x-blog.post :post="$post" />

    <div class="text-primary-500 text-sm text-center mx-auto mt-8">
        {{ __('Share this post.') }}
    </div>
    <div class="flex gap-3 justify-center pt-3">
        <x-link.social-icon name="x" title="{{ __('Twitter page') }}" link="https://x.com/intent/post?text={{ urlencode($post->title) }}&url={{ urlencode(url()->current()) }}" class="hover:text-primary-500"/>
        <x-link.social-icon name="linkedin" title="{{ __('LinkedIn community') }}" link="https://www.linkedin.com/shareArticle?url={{ urlencode(url()->current()) }}&title={{ urlencode($post->title) }}" class="hover:text-primary-500"/>
    </div>

    <div class="text-center">
        <x-section.outro>
            <x-heading.h6 class="text-primary-50">
                {{ __('Stay up-to-date') }}
            </x-heading.h6>
            <x-heading.h2 class="text-primary-50">
                {{ __('Subscribe to our newsletter') }}
            </x-heading.h2>

            @if(session('success'))
                <div class="mx-auto mt-6 max-w-md bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mx-auto mt-6 max-w-md bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    {{ session('error') }}
                </div>
            @endif

            @if(session('info'))
                <div class="mx-auto mt-6 max-w-md bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded">
                    {{ session('info') }}
                </div>
            @endif

            <form method="POST" action="{{ route('newsletter.subscribe') }}" class="mx-auto mt-6 max-w-md">
                @csrf
                <input
                    type="email"
                    name="email"
                    placeholder="{{ __('Your email address') }}"
                    class="w-full px-4 py-3 rounded-lg bg-transparent border-2 border-primary-100 placeholder-primary-100 text-primary-50 focus:outline-none focus:border-primary-200"
                    required
                />

                <div class="mt-6">
                    <button type="submit" class="inline-flex items-center justify-center px-6 py-3 bg-secondary-500 hover:bg-secondary-600 text-white font-semibold rounded-lg transition">
                        {{ __('Subscribe') }}
                    </button>
                </div>
            </form>
        </x-section.outro>
    </div>

    @if (count($morePosts) > 0)

        <div class="text-center">
            <x-heading.h6 class="text-primary-500">
                {{ __('Don\'t miss this') }}
            </x-heading.h6>
            <x-heading.h2>
                {{ __('You might also like') }}
            </x-heading.h2>
        </div>

        <x-blog.post-cards :posts="$morePosts" link-to-more-posts="true"/>
    @endif

</x-layouts.app>
