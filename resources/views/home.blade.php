<x-layouts.app>
    <x-slot name="title">
        {{ __('WhizzIQ - The All-in-One Business Operating System for Entrepreneurs') }}
    </x-slot>

    {{-- 1. HERO SECTION (Above the Fold) --}}
    <section class="relative overflow-hidden bg-gradient-to-br from-neutral-50 via-white to-primary-50/30 min-h-[90vh] flex items-center">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-16 md:py-24">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-16 items-center">
                <div class="text-left">
                    <h1 class="text-4xl sm:text-5xl lg:text-6xl xl:text-7xl font-bold text-primary-900 leading-[1.1] tracking-tight">
                        {{ __('Run Your Entire Business From One Intelligent Command Center') }}
                    </h1>
                    <p class="mt-6 text-lg md:text-xl text-neutral-600 leading-relaxed max-w-xl">
                        {{ __('Replace CRM, invoicing, scheduling, tasks, and scattered tools with one AI-powered platform built for entrepreneurs.') }}
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4 mt-8">
                        <a href="{{ route('register') }}" class="inline-flex items-center justify-center rounded-xl bg-primary-500 text-white font-semibold px-8 py-4 text-base hover:bg-primary-600 focus:ring-4 focus:ring-primary-200 transition shadow-lg shadow-primary-500/25 hover:shadow-xl hover:shadow-primary-500/30">
                            {{ __('Start Free Trial — No Credit Card Required') }}
                        </a>
                        <a href="#watch-demo" class="inline-flex items-center justify-center rounded-xl border-2 border-neutral-300 text-neutral-700 font-semibold px-8 py-4 text-base hover:bg-neutral-50 hover:border-neutral-400 transition">
                            {{ __('Watch 2-Min Demo') }}
                        </a>
                    </div>
                    <p class="mt-4 text-sm text-neutral-500">
                        {{ __('Cancel anytime. Setup in minutes.') }}
                    </p>
                </div>
                <div class="relative order-first lg:order-last">
                    <div class="relative z-10 rounded-2xl overflow-hidden shadow-2xl ring-1 ring-neutral-200/50">
                        <img class="w-full h-auto transition duration-300 hover:scale-[1.02]" src="{{ asset('images/features/hero-image.png') }}" alt="WhizzIQ Dashboard" />
                    </div>
                    <div class="absolute inset-0 bg-gradient-to-br from-primary-100/40 to-primary-50/20 rounded-3xl transform translate-x-4 translate-y-4 -z-10 blur-xl"></div>
                </div>
            </div>
        </div>
    </section>

    {{-- 2. SOCIAL PROOF STRIP --}}
    <section class="border-y border-neutral-100 bg-white/80 py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <p class="text-center text-sm font-medium text-neutral-500 mb-6">{{ __('Trusted by growing businesses worldwide') }}</p>
            <div class="flex flex-wrap items-center justify-center gap-8 md:gap-12 opacity-60 grayscale">
                <div class="h-8 w-24 bg-neutral-200 rounded-lg flex items-center justify-center text-neutral-500 text-xs font-semibold">Logo</div>
                <div class="h-8 w-24 bg-neutral-200 rounded-lg flex items-center justify-center text-neutral-500 text-xs font-semibold">Logo</div>
                <div class="h-8 w-24 bg-neutral-200 rounded-lg flex items-center justify-center text-neutral-500 text-xs font-semibold">Logo</div>
                <div class="h-8 w-24 bg-neutral-200 rounded-lg flex items-center justify-center text-neutral-500 text-xs font-semibold">Logo</div>
                <div class="flex items-center gap-2 text-neutral-600">
                    <span class="text-sm font-semibold">4.8/5</span>
                    <div class="flex text-amber-400">
                        <svg class="w-4 h-4 fill-current" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                        <svg class="w-4 h-4 fill-current" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                        <svg class="w-4 h-4 fill-current" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                        <svg class="w-4 h-4 fill-current" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                        <svg class="w-4 h-4 fill-current" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- 3. PROBLEM SECTION --}}
    <section class="py-20 md:py-28 bg-white opacity-0 transition-all duration-700 ease-out" x-data x-intersect.once="$el.classList.add('opacity-100')">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <h2 class="text-3xl md:text-4xl font-bold text-primary-900 text-center mb-16 md:mb-20">
                {{ __('Running a Business Shouldn\'t Feel This Complicated') }}
            </h2>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-20 items-center">
                <div class="space-y-5">
                    <div class="flex items-center gap-4">
                        <div class="flex-shrink-0 w-10 h-10 rounded-xl bg-red-50 flex items-center justify-center">
                            <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                        </div>
                        <p class="text-neutral-700 font-medium">{{ __('Switching between 6+ tools daily') }}</p>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="flex-shrink-0 w-10 h-10 rounded-xl bg-red-50 flex items-center justify-center">
                            <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <p class="text-neutral-700 font-medium">{{ __('Manually chasing invoices') }}</p>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="flex-shrink-0 w-10 h-10 rounded-xl bg-red-50 flex items-center justify-center">
                            <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        </div>
                        <p class="text-neutral-700 font-medium">{{ __('Losing track of leads') }}</p>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="flex-shrink-0 w-10 h-10 rounded-xl bg-red-50 flex items-center justify-center">
                            <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </div>
                        <p class="text-neutral-700 font-medium">{{ __('Scattered documents & tasks') }}</p>
                    </div>
                </div>
                <div class="rounded-2xl bg-neutral-50 p-8 md:p-10 border border-neutral-100">
                    <p class="text-lg text-neutral-700 leading-relaxed">
                        {{ __('Most entrepreneurs waste hours every week managing disconnected tools. WhizzIQ brings everything into one unified system.') }}
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- 4. SOLUTION – 3 PILLARS --}}
    <section class="py-20 md:py-28 bg-gradient-to-br from-primary-50/50 to-white opacity-0 transition-all duration-700 ease-out" id="features" x-data x-intersect.once="$el.classList.add('opacity-100')">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <h2 class="text-3xl md:text-4xl font-bold text-primary-900 text-center mb-16">
                {{ __('Everything You Need. Nothing You Don\'t.') }}
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 lg:gap-10">
                <div class="bg-white rounded-2xl p-8 shadow-sm hover:shadow-md border border-neutral-100 transition-shadow">
                    <div class="w-14 h-14 rounded-xl bg-primary-100 flex items-center justify-center mb-6">
                        <svg class="w-7 h-7 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </div>
                    <h3 class="text-xl font-bold text-primary-900 mb-3">{{ __('Manage Clients & Sales') }}</h3>
                    <p class="text-neutral-600 text-[15px] leading-relaxed">{{ __('Track leads, automate follow-ups, close deals faster.') }}</p>
                </div>
                <div class="bg-white rounded-2xl p-8 shadow-sm hover:shadow-md border border-neutral-100 transition-shadow">
                    <div class="w-14 h-14 rounded-xl bg-primary-100 flex items-center justify-center mb-6">
                        <svg class="w-7 h-7 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    </div>
                    <h3 class="text-xl font-bold text-primary-900 mb-3">{{ __('Handle Finances With Confidence') }}</h3>
                    <p class="text-neutral-600 text-[15px] leading-relaxed">{{ __('Send invoices, track payments, monitor cash flow in one place.') }}</p>
                </div>
                <div class="bg-white rounded-2xl p-8 shadow-sm hover:shadow-md border border-neutral-100 transition-shadow">
                    <div class="w-14 h-14 rounded-xl bg-primary-100 flex items-center justify-center mb-6">
                        <svg class="w-7 h-7 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                    </div>
                    <h3 class="text-xl font-bold text-primary-900 mb-3">{{ __('Automate & Optimize With AI') }}</h3>
                    <p class="text-neutral-600 text-[15px] leading-relaxed">{{ __('Let AI handle repetitive work and give you smart business insights.') }}</p>
                </div>
            </div>
        </div>
    </section>

    {{-- 5. PRODUCT SHOWCASE --}}
    <section class="py-20 md:py-28 bg-white opacity-0 transition-all duration-700 ease-out" x-data x-intersect.once="$el.classList.add('opacity-100')">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            {{-- Block 1 --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-20 items-center mb-24 md:mb-28">
                <div>
                    <h2 class="text-3xl md:text-4xl font-bold text-primary-900 mb-4">
                        {{ __('Your Business, Visualized in One Dashboard') }}
                    </h2>
                    <p class="text-lg text-neutral-600 leading-relaxed">
                        {{ __('See everything that matters at a glance—metrics, tasks, pipeline, and financial health in one place.') }}
                    </p>
                </div>
                <div class="rounded-2xl bg-neutral-100 border border-neutral-200 aspect-video flex items-center justify-center text-neutral-400 text-sm">
                    {{ __('Dashboard image placeholder') }}
                </div>
            </div>

            {{-- Block 2 --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-20 items-center mb-24 md:mb-28">
                <div class="order-2 lg:order-1 rounded-2xl bg-neutral-100 border border-neutral-200 aspect-video flex items-center justify-center text-neutral-400 text-sm">
                    {{ __('Connected features image placeholder') }}
                </div>
                <div class="order-1 lg:order-2">
                    <h2 class="text-3xl md:text-4xl font-bold text-primary-900 mb-4">
                        {{ __('Invoices, Calendar, Tasks — Fully Connected') }}
                    </h2>
                    <p class="text-lg text-neutral-600 leading-relaxed">
                        {{ __('When everything talks to each other, you save time and never drop a follow-up again.') }}
                    </p>
                </div>
            </div>

            {{-- Block 3 --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-20 items-center">
                <div>
                    <h2 class="text-3xl md:text-4xl font-bold text-primary-900 mb-4">
                        {{ __('Meet Your AI Business Co-Pilot') }}
                    </h2>
                    <p class="text-lg text-neutral-600 leading-relaxed">
                        {{ __('Get instant insights, automate repetitive tasks, and make smarter decisions with AI that understands your business.') }}
                    </p>
                </div>
                <div class="rounded-2xl bg-neutral-100 border border-neutral-200 aspect-video flex items-center justify-center text-neutral-400 text-sm">
                    {{ __('AI Co-Pilot image placeholder') }}
                </div>
            </div>
        </div>
    </section>

    {{-- 6. HOW IT WORKS --}}
    <section class="py-20 md:py-28 bg-gradient-to-br from-white to-primary-50/30 opacity-0 transition-all duration-700 ease-out" x-data x-intersect.once="$el.classList.add('opacity-100')">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <h2 class="text-3xl md:text-4xl font-bold text-primary-900 text-center mb-16">
                {{ __('Get Started in Minutes') }}
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-10 lg:gap-12">
                <div class="text-center">
                    <div class="w-14 h-14 rounded-2xl bg-primary-500 text-white flex items-center justify-center text-xl font-bold mx-auto mb-5">1</div>
                    <h3 class="text-lg font-bold text-primary-900 mb-2">{{ __('Sign up & create your workspace') }}</h3>
                    <p class="text-neutral-600 text-sm">{{ __('No credit card. Ready in under 5 minutes.') }}</p>
                </div>
                <div class="text-center">
                    <div class="w-14 h-14 rounded-2xl bg-primary-500 text-white flex items-center justify-center text-xl font-bold mx-auto mb-5">2</div>
                    <h3 class="text-lg font-bold text-primary-900 mb-2">{{ __('Import clients & connect workflows') }}</h3>
                    <p class="text-neutral-600 text-sm">{{ __('Bring in your data. Sync calendars and tools.') }}</p>
                </div>
                <div class="text-center">
                    <div class="w-14 h-14 rounded-2xl bg-primary-500 text-white flex items-center justify-center text-xl font-bold mx-auto mb-5">3</div>
                    <h3 class="text-lg font-bold text-primary-900 mb-2">{{ __('Run everything from one dashboard') }}</h3>
                    <p class="text-neutral-600 text-sm">{{ __('Manage clients, finances, and AI—all in one place.') }}</p>
                </div>
            </div>
        </div>
    </section>

    {{-- 7. TESTIMONIALS --}}
    <section class="py-20 md:py-28 bg-white opacity-0 transition-all duration-700 ease-out" id="testimonials" x-data x-intersect.once="$el.classList.add('opacity-100')">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <h2 class="text-3xl md:text-4xl font-bold text-primary-900 text-center mb-16">
                {{ __('Loved by Entrepreneurs Worldwide') }}
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="bg-neutral-50 rounded-2xl p-8 border border-neutral-100">
                    <div class="flex items-center gap-4 mb-4">
                        <div class="w-12 h-12 rounded-full bg-primary-200 flex items-center justify-center text-primary-700 font-semibold text-sm">SJ</div>
                        <div>
                            <p class="font-semibold text-primary-900">Sarah Johnson</p>
                            <p class="text-sm text-neutral-500">Freelance Consultant</p>
                        </div>
                    </div>
                    <p class="text-neutral-700 leading-relaxed text-[15px]">
                        {{ __('"WhizzIQ saved me 10+ hours per week. I went from juggling 7 tools to one place. Game changer."') }}
                    </p>
                </div>
                <div class="bg-neutral-50 rounded-2xl p-8 border border-neutral-100">
                    <div class="flex items-center gap-4 mb-4">
                        <div class="w-12 h-12 rounded-full bg-primary-200 flex items-center justify-center text-primary-700 font-semibold text-sm">MC</div>
                        <div>
                            <p class="font-semibold text-primary-900">Michael Chen</p>
                            <p class="text-sm text-neutral-500">Small Business Owner</p>
                        </div>
                    </div>
                    <p class="text-neutral-700 leading-relaxed text-[15px]">
                        {{ __('"Cut my software costs by 60% and finally have visibility into my finances. The AI features are incredible."') }}
                    </p>
                </div>
                <div class="bg-neutral-50 rounded-2xl p-8 border border-neutral-100">
                    <div class="flex items-center gap-4 mb-4">
                        <div class="w-12 h-12 rounded-full bg-primary-200 flex items-center justify-center text-primary-700 font-semibold text-sm">ER</div>
                        <div>
                            <p class="font-semibold text-primary-900">Emma Rodriguez</p>
                            <p class="text-sm text-neutral-500">Marketing Agency Founder</p>
                        </div>
                    </div>
                    <p class="text-neutral-700 leading-relaxed text-[15px]">
                        {{ __('"Automated follow-ups helped me close 30% more deals. This platform pays for itself many times over."') }}
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- 8. PRICING --}}
    <section class="py-20 md:py-28 bg-gradient-to-br from-primary-50/50 to-white opacity-0 transition-all duration-700 ease-out" id="pricing" x-data x-intersect.once="$el.classList.add('opacity-100')">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <h2 class="text-3xl md:text-4xl font-bold text-primary-900 text-center mb-4">
                {{ __('Choose Your Plan') }}
            </h2>
            <p class="text-center text-neutral-600 max-w-xl mx-auto mb-12">
                {{ __('Start with what you need. Upgrade as you grow.') }}
            </p>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 lg:gap-8 max-w-5xl mx-auto">
                {{-- Starter --}}
                <div class="bg-white rounded-2xl p-8 border-2 border-neutral-200 shadow-sm flex flex-col">
                    <h3 class="text-lg font-bold text-primary-900">{{ __('Starter') }}</h3>
                    <p class="text-sm text-neutral-500 mt-1 mb-4">{{ __('For solopreneurs getting started') }}</p>
                    <div class="text-3xl font-bold text-primary-900">$29<span class="text-base font-normal text-neutral-500">/mo</span></div>
                    <ul class="mt-6 space-y-3 flex-1">
                        <li class="flex items-center gap-2 text-neutral-700 text-sm"><svg class="w-5 h-5 text-primary-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg> 500 contacts</li>
                        <li class="flex items-center gap-2 text-neutral-700 text-sm"><svg class="w-5 h-5 text-primary-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg> Unlimited appointments</li>
                        <li class="flex items-center gap-2 text-neutral-700 text-sm"><svg class="w-5 h-5 text-primary-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg> 50 invoices/month</li>
                        <li class="flex items-center gap-2 text-neutral-700 text-sm"><svg class="w-5 h-5 text-primary-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg> 1 GB storage</li>
                        <li class="flex items-center gap-2 text-neutral-700 text-sm"><svg class="w-5 h-5 text-primary-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg> 20 AI requests/day</li>
                    </ul>
                    <a href="{{ route('register') }}" class="mt-8 w-full inline-flex items-center justify-center rounded-xl border-2 border-primary-500 text-primary-600 font-semibold px-6 py-3.5 text-sm hover:bg-primary-50 transition">
                        {{ __('Start Free Trial') }}
                    </a>
                </div>

                {{-- Pro (Most Popular) --}}
                <div class="bg-white rounded-2xl p-8 border-2 border-primary-500 shadow-lg shadow-primary-500/10 relative flex flex-col md:-my-2 md:scale-[1.02] z-10">
                    <div class="absolute -top-3 left-1/2 -translate-x-1/2 bg-primary-500 text-white text-xs font-semibold px-3 py-1 rounded-full">
                        {{ __('Most Popular') }}
                    </div>
                    <h3 class="text-lg font-bold text-primary-900">{{ __('Pro') }}</h3>
                    <p class="text-sm text-neutral-500 mt-1 mb-4">{{ __('For growing teams and serious pros') }}</p>
                    <div class="text-3xl font-bold text-primary-900">$79<span class="text-base font-normal text-neutral-500">/mo</span></div>
                    <ul class="mt-6 space-y-3 flex-1">
                        <li class="flex items-center gap-2 text-neutral-700 text-sm"><svg class="w-5 h-5 text-primary-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg> Unlimited contacts</li>
                        <li class="flex items-center gap-2 text-neutral-700 text-sm"><svg class="w-5 h-5 text-primary-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg> Unlimited appointments & invoices</li>
                        <li class="flex items-center gap-2 text-neutral-700 text-sm"><svg class="w-5 h-5 text-primary-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg> 5 GB storage</li>
                        <li class="flex items-center gap-2 text-neutral-700 text-sm"><svg class="w-5 h-5 text-primary-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg> 75 AI requests/day</li>
                        <li class="flex items-center gap-2 text-neutral-700 text-sm"><svg class="w-5 h-5 text-primary-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg> Calendar & basic tax</li>
                    </ul>
                    <a href="{{ route('register') }}" class="mt-8 w-full inline-flex items-center justify-center rounded-xl bg-primary-500 text-white font-semibold px-6 py-3.5 text-sm hover:bg-primary-600 shadow-lg shadow-primary-500/25 transition">
                        {{ __('Start Free Trial') }}
                    </a>
                </div>

                {{-- Premium --}}
                <div class="bg-white rounded-2xl p-8 border-2 border-neutral-200 shadow-sm flex flex-col">
                    <h3 class="text-lg font-bold text-primary-900">{{ __('Premium') }}</h3>
                    <p class="text-sm text-neutral-500 mt-1 mb-4">{{ __('For scale and AI-powered everything') }}</p>
                    <div class="text-3xl font-bold text-primary-900">$149<span class="text-base font-normal text-neutral-500">/mo</span></div>
                    <ul class="mt-6 space-y-3 flex-1">
                        <li class="flex items-center gap-2 text-neutral-700 text-sm"><svg class="w-5 h-5 text-primary-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg> Everything in Pro</li>
                        <li class="flex items-center gap-2 text-neutral-700 text-sm"><svg class="w-5 h-5 text-primary-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg> Unlimited storage</li>
                        <li class="flex items-center gap-2 text-neutral-700 text-sm"><svg class="w-5 h-5 text-primary-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg> 200 AI requests/day</li>
                        <li class="flex items-center gap-2 text-neutral-700 text-sm"><svg class="w-5 h-5 text-primary-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg> AI-powered tax & docs</li>
                        <li class="flex items-center gap-2 text-neutral-700 text-sm"><svg class="w-5 h-5 text-primary-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg> Priority support</li>
                    </ul>
                    <a href="{{ route('register') }}" class="mt-8 w-full inline-flex items-center justify-center rounded-xl border-2 border-primary-500 text-primary-600 font-semibold px-6 py-3.5 text-sm hover:bg-primary-50 transition">
                        {{ __('Start Free Trial') }}
                    </a>
                </div>
            </div>
        </div>
    </section>

    {{-- 9. FAQ --}}
    <section class="py-20 md:py-28 bg-white opacity-0 transition-all duration-700 ease-out" id="faq" x-data x-intersect.once="$el.classList.add('opacity-100')">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h2 class="text-3xl md:text-4xl font-bold text-primary-900 text-center mb-12">
                {{ __('Frequently Asked Questions') }}
            </h2>
            <x-accordion class="join join-vertical w-full rounded-2xl overflow-hidden border border-neutral-200">
                <x-accordion.item active="true" name="faq-landing">
                    <x-slot name="title">{{ __('Is there a free trial?') }}</x-slot>
                    {{ __('Yes. Every plan includes a 14-day free trial with full access. No credit card required.') }}
                </x-accordion.item>
                <x-accordion.item active="false" name="faq-landing">
                    <x-slot name="title">{{ __('Can I cancel anytime?') }}</x-slot>
                    {{ __('Yes. All plans are month-to-month. Cancel from your dashboard anytime; you keep access until the end of the billing period.') }}
                </x-accordion.item>
                <x-accordion.item active="false" name="faq-landing">
                    <x-slot name="title">{{ __('Is my data secure?') }}</x-slot>
                    {{ __('Yes. We use encryption, two-factor authentication, and comply with industry standards. Your data is never sold or shared.') }}
                </x-accordion.item>
                <x-accordion.item active="false" name="faq-landing">
                    <x-slot name="title">{{ __('Do I need technical knowledge?') }}</x-slot>
                    {{ __('No. WhizzIQ is built for entrepreneurs. Setup takes minutes, and our interface is designed to be intuitive.') }}
                </x-accordion.item>
                <x-accordion.item active="false" name="faq-landing">
                    <x-slot name="title">{{ __('What makes WhizzIQ different?') }}</x-slot>
                    {{ __('WhizzIQ replaces 6–8 disconnected tools with one platform. Everything—CRM, invoicing, scheduling, tasks, finances, and AI—connects in a single dashboard.') }}
                </x-accordion.item>
            </x-accordion>
        </div>
    </section>

    {{-- 10. FINAL CTA --}}
    <section class="py-20 md:py-28 bg-primary-600">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl md:text-5xl font-bold text-white mb-6 leading-tight">
                {{ __('Stop Juggling Tools. Start Running Your Business.') }}
            </h2>
            <p class="text-lg md:text-xl text-primary-100 mb-8">
                {{ __('No credit card required. Setup takes less than 5 minutes.') }}
            </p>
            <a href="{{ route('register') }}" class="inline-flex items-center justify-center rounded-xl bg-white text-primary-600 font-semibold px-10 py-4 text-lg hover:bg-primary-50 shadow-xl transition">
                {{ __('Start Your Free Trial Today') }}
            </a>
        </div>
    </section>

</x-layouts.app>
