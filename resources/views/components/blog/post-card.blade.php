<div {{ $attributes->merge(['class' => 'flex flex-col rounded-2xl shadow-xl overflow-hidden transition hover:scale-103']) }}>

    @if (!empty($post->getFirstMediaUrl('blog-images')))
        <a href="{{route('blog.view', $post->slug)}}">
            <img src="{{$post->getFirstMediaUrl('blog-images')}}" alt="{{$post->title}}" class="h-52 object-cover w-full">
        </a>
    @else
        {{-- Fallback placeholder if no media uploaded --}}
        <a href="{{route('blog.view', $post->slug)}}">
            <div class="h-52 w-full bg-gradient-to-br from-primary-400 to-primary-600 flex items-center justify-center">
                <svg class="w-16 h-16 text-white opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
            </div>
        </a>
    @endif
    <div class="flex flex-col flex-wrap gap-1 mb-6 grow align-items-stretch p-4 mt-1">
        <a href="{{route('blog.view', $post->slug)}}" class="text-primary-900">
            <x-heading.h6 class="">{{ $post->title }}</x-heading.h6>
        </a>
        <div class="text-neutral-400 text-xs">
            @if($post->is_published)
                {{ date(config('app.date_format'), strtotime($post->published_at)) }}
            @else
                [{{ __('Draft') }}]
            @endif
            —
            {{ $post->author->getPublicName() }}
        </div>
        @if ($post->blogPostCategory)
            <div class="text-neutral-400 rounded-lg px-2 py-1 border border-neutral-300 max-w-fit text-xs mt-3 hover:bg-neutral-100">
                <a href="{{route('blog.category', ['slug' => $post->blogPostCategory->slug])}}">{{ $post->blogPostCategory->name }}</a>
            </div>
        @endif
    </div>

    <div class="flex justify-end content-end pb-4 pr-4 text-xs font-light">
        <a href="{{route('blog.view', $post->slug)}}">{{ __('Read more >') }}</a>
    </div>
</div>
