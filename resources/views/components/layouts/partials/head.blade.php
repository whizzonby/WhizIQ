@php($description = isset($description) ? $description : config('app.description'))
<meta name="description" content="{{ $description }}">

@php($canonical = isset($canonical) ? $canonical : url()->current())
<link rel="canonical" href="{{ $canonical }}">

<title>
    @isset($title)
        {{ $title }} | {{ config('app.name', 'SaaSykit') }}
    @else
        {{ config('app.name', 'SaaSykit') }}
    @endisset
</title>

<link rel="shortcut icon" type="image/x-icon" href="{{asset('images/favicon.ico')}}">

@include('components.layouts.partials.social-cards')

<!-- Fonts: Inter for modern SaaS aesthetic -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">

<!-- Scripts -->
@vite(['resources/css/app.css'])

@stack('head')

@livewireStyles
