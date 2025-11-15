<x-nav.item route="#features">{{ __('Features') }}</x-nav.item>
<x-nav.item route="#pricing">{{ __('Pricing') }}</x-nav.item>
<x-nav.item route="#faq">{{ __('FAQ') }}</x-nav.item>
@if(config('app.roadmap_enabled'))
    <x-nav.item route="roadmap">{{ __('Roadmap') }}</x-nav.item>
@endif
<x-nav.item route="blog">{{ __('Blog') }}</x-nav.item>
<li><a href="/how-to-use-whizziq" class="text-sm block py-2 px-3 md:p-0 rounded hover:bg-primary-600 md:hover:bg-transparent md:hover:text-white text-primary-50">{{ __('How to Use WhizzIQ') }}</a></li>
@guest
    <x-nav.item route="login" class="md:hidden">{{ __('Login') }}</x-nav.item>
@endguest
