<article class="blog-post mx-auto max-w-none md:max-w-3xl p-4 mt-6">
    <div class="flex flex-col flex-wrap gap-1 mb-6 grow align-items-stretch">
        <x-heading.h1 class="text-primary-900 font-medium text-4xl!">{{ $post->title }}</x-heading.h1>
        <div class="text-neutral-500 text-sm mt-4">
            @if($post->is_published)
                {{ date(config('app.date_format'), strtotime($post->published_at)) }}
            @else
                [{{ __('Draft') }}]
            @endif
            —
            {{ $post->author->getPublicName() }}
            @if ($post->blogPostCategory()->exists())
                <span class="text-neutral-400 rounded-lg px-2 py-1 border border-neutral-300 max-w-fit text-xs ms-3 hover:bg-neutral-100">
                    <a href="{{route('blog.category', ['slug' => $post->blogPostCategory->slug])}}">{{ $post->blogPostCategory->name }}</a>
                </span>
            @endif
        </div>
    </div>


    @if ($post->media->count() > 0)
        <x-slot name="socialCard">
            {{ $post->getFirstMediaUrl('blog-images') }}
        </x-slot>

        <div class="mt-8">
            <img src="{{$post->getFirstMediaUrl('blog-images')}}" alt="{{$post->title}}" class="rounded-2xl	">
        </div>
    @else
        {{-- Fallback placeholder if no media uploaded --}}
        <div class="mt-8">
            <div class="rounded-2xl w-full bg-gradient-to-br from-primary-400 to-primary-600 flex items-center justify-center" style="height: 500px;">
                <svg class="w-24 h-24 text-white opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
            </div>
        </div>
    @endif

    <div class="pt-4">
        {!! $post->body !!}
    </div>

</article>
