<x-layouts.app>
    <x-slot name="title">
        {{ __('WhizIQ: All-in-One Business Management Platform | Replace 10+ Tools') }}
    </x-slot>

    {{-- Hero Section --}}
    <x-section.hero class="w-full mb-8 md:mb-72">
        <div class="mx-auto text-center h-160 md:h-180 px-4">
            <x-pill class="text-primary-500 bg-primary-50">{{ __('The Complete Business Operating System') }}</x-pill>
            <x-heading.h1 class="mt-4 text-primary-50 font-bold text-4xl md:text-6xl">
                {{ __('Stop Juggling 10+ Tools.') }}
                <br class="hidden sm:block">
                {{ __('Run Your Entire Business from One Platform.') }}
            </x-heading.h1>

            <p class="text-primary-50 m-3 text-lg md:text-xl max-w-3xl mx-auto">
                {{ __('CRM, invoicing, scheduling, project management, and marketing—all in one platform.') }}
            </p>

            <div class="flex flex-wrap gap-4 justify-center flex-col md:flex-row mt-8">
                <x-effect.glow></x-effect.glow>

                <x-button-link.secondary href="{{ route('register') }}" class="self-center py-4 px-8 text-lg font-semibold" elementType="a">
                    {{ __('Start Free 14-Day Trial') }}
                </x-button-link.secondary>
                <x-button-link.primary-outline href="#features" class="bg-transparent self-center py-4 px-8 text-lg font-semibold text-white border-white hover:bg-white hover:text-primary-600">
                    {{ __('See All Features') }}
                </x-button-link.primary-outline>
            </div>

            {{-- Trust Indicators --}}
            <div class="mt-6 flex flex-wrap justify-center items-center gap-6 text-primary-50 text-sm">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <span>{{ __('No Credit Card Required') }}</span>
                </div>
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                    </svg>
                    <span>{{ __('14-Day Free Trial') }}</span>
                </div>
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                    </svg>
                    <span>{{ __('Replace $500+/Month in Tools') }}</span>
                </div>
            </div>

            <div class="mx-auto md:max-w-3xl lg:max-w-5xl">
                <img class="drop-shadow-2xl mt-8 transition hover:scale-101 rounded-2xl border-4 border-white/20" src="{{URL::asset('/images/features/hero-image.png')}}" alt="WhizIQ Dashboard" />
            </div>
        </div>
    </x-section.hero>

    {{-- Solution Section --}}
    <section class="pt-64 md:pt-96 pb-16 bg-white" id="features">
        <div class="max-w-6xl mx-auto px-4">
            <div class="text-center mb-12">
                <x-heading.h6 class="text-primary-500">{{ __('The Solution') }}</x-heading.h6>
                <x-heading.h2 class="text-gray-900">{{ __('Everything Your Business Needs in One Platform') }}</x-heading.h2>
                <p class="mt-4 text-gray-600 max-w-2xl mx-auto">{{ __('Stop paying for 10+ tools. WhizIQ gives you a complete business operating system for less than the cost of a single CRM.') }}</p>
            </div>

            {{-- Feature Grid --}}
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8 mt-12">
                {{-- CRM --}}
                <div class="bg-gray-50 p-6 rounded-xl hover:shadow-lg transition">
                    <div class="bg-primary-100 text-primary-600 w-12 h-12 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold mb-2">{{ __('Complete CRM') }}</h3>
                    <p class="text-gray-600 mb-4">{{ __('Manage all your contacts, track interactions, score relationships, and never miss a follow-up. Replace HubSpot or Salesforce.') }}</p>
                    <ul class="space-y-2 text-sm text-gray-600">
                        <li class="flex items-start">
                            <svg class="w-4 h-4 text-green-500 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                            {{ __('Contact relationship scoring') }}
                        </li>
                        <li class="flex items-start">
                            <svg class="w-4 h-4 text-green-500 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                            {{ __('Interaction history & notes') }}
                        </li>
                        <li class="flex items-start">
                            <svg class="w-4 h-4 text-green-500 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                            {{ __('Automatic follow-up reminders') }}
                        </li>
                    </ul>
                </div>

                {{-- Sales Pipeline --}}
                <div class="bg-gray-50 p-6 rounded-xl hover:shadow-lg transition">
                    <div class="bg-primary-100 text-primary-600 w-12 h-12 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold mb-2">{{ __('Sales Pipeline') }}</h3>
                    <p class="text-gray-600 mb-4">{{ __('Track deals from lead to close, forecast revenue, and know exactly where every opportunity stands. Replace Pipedrive.') }}</p>
                    <ul class="space-y-2 text-sm text-gray-600">
                        <li class="flex items-start">
                            <svg class="w-4 h-4 text-green-500 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                            {{ __('Visual deal pipeline') }}
                        </li>
                        <li class="flex items-start">
                            <svg class="w-4 h-4 text-green-500 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                            {{ __('Weighted revenue forecasting') }}
                        </li>
                        <li class="flex items-start">
                            <svg class="w-4 h-4 text-green-500 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                            {{ __('Win/loss tracking & analysis') }}
                        </li>
                    </ul>
                </div>

                {{-- Appointment Booking --}}
                <div class="bg-gray-50 p-6 rounded-xl hover:shadow-lg transition">
                    <div class="bg-primary-100 text-primary-600 w-12 h-12 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold mb-2">{{ __('Appointment Booking') }}</h3>
                    <p class="text-gray-600 mb-4">{{ __('Public booking pages, calendar sync, automatic Zoom/Meet links, and attendance tracking. Replace Calendly and Acuity.') }}</p>
                    <ul class="space-y-2 text-sm text-gray-600">
                        <li class="flex items-start">
                            <svg class="w-4 h-4 text-green-500 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                            {{ __('Google/Outlook/Apple sync') }}
                        </li>
                        <li class="flex items-start">
                            <svg class="w-4 h-4 text-green-500 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                            {{ __('Auto Zoom/Meet link generation') }}
                        </li>
                        <li class="flex items-start">
                            <svg class="w-4 h-4 text-green-500 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                            {{ __('Recurring appointments') }}
                        </li>
                    </ul>
                </div>

                {{-- Invoicing & Payments --}}
                <div class="bg-gray-50 p-6 rounded-xl hover:shadow-lg transition">
                    <div class="bg-primary-100 text-primary-600 w-12 h-12 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2zM10 8.5a.5.5 0 11-1 0 .5.5 0 011 0zm5 5a.5.5 0 11-1 0 .5.5 0 011 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold mb-2">{{ __('Invoicing & Payments') }}</h3>
                    <p class="text-gray-600 mb-4">{{ __('Create professional invoices, accept payments via Stripe, track overdue invoices, and automate reminders. Replace FreshBooks.') }}</p>
                    <ul class="space-y-2 text-sm text-gray-600">
                        <li class="flex items-start">
                            <svg class="w-4 h-4 text-green-500 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                            {{ __('Stripe/Paddle integration') }}
                        </li>
                        <li class="flex items-start">
                            <svg class="w-4 h-4 text-green-500 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                            {{ __('Custom branded templates') }}
                        </li>
                        <li class="flex items-start">
                            <svg class="w-4 h-4 text-green-500 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                            {{ __('Payment reminders & tracking') }}
                        </li>
                    </ul>
                </div>

                {{-- Task Management --}}
                <div class="bg-gray-50 p-6 rounded-xl hover:shadow-lg transition">
                    <div class="bg-primary-100 text-primary-600 w-12 h-12 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold mb-2">{{ __('Task & Project Management') }}</h3>
                    <p class="text-gray-600 mb-4">{{ __('Kanban boards, AI-powered prioritization, goal tracking (OKRs), and deadline management. Replace Asana or Monday.') }}</p>
                    <ul class="space-y-2 text-sm text-gray-600">
                        <li class="flex items-start">
                            <svg class="w-4 h-4 text-green-500 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                            {{ __('AI task extraction from notes') }}
                        </li>
                        <li class="flex items-start">
                            <svg class="w-4 h-4 text-green-500 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                            {{ __('Kanban board views') }}
                        </li>
                        <li class="flex items-start">
                            <svg class="w-4 h-4 text-green-500 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                            {{ __('OKR-style goal tracking') }}
                        </li>
                    </ul>
                </div>

                {{-- Marketing Automation --}}
                <div class="bg-gray-50 p-6 rounded-xl hover:shadow-lg transition">
                    <div class="bg-primary-100 text-primary-600 w-12 h-12 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold mb-2">{{ __('Marketing Automation') }}</h3>
                    <p class="text-gray-600 mb-4">{{ __('Email campaigns, social media management, analytics tracking, and ROI calculations. Replace Mailchimp and Hootsuite.') }}</p>
                    <ul class="space-y-2 text-sm text-gray-600">
                        <li class="flex items-start">
                            <svg class="w-4 h-4 text-green-500 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                            {{ __('Email campaigns with templates') }}
                        </li>
                        <li class="flex items-start">
                            <svg class="w-4 h-4 text-green-500 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                            {{ __('Social media integration') }}
                        </li>
                        <li class="flex items-start">
                            <svg class="w-4 h-4 text-green-500 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                            {{ __('CLV/CAC tracking') }}
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    {{-- Pricing Section --}}
    <section class="mt-24 pt-64 md:pt-80 pb-16 bg-gray-50" id="pricing">
        <div class="max-w-6xl mx-auto px-4">
            <div class="text-center mb-12">
                <x-heading.h6 class="text-primary-500">{{ __('Pricing') }}</x-heading.h6>
                <x-heading.h2 class="text-gray-900">{{ __('One Platform. One Price. Everything You Need.') }}</x-heading.h2>
                <p class="mt-4 text-gray-600 max-w-2xl mx-auto">{{ __('Stop paying for HubSpot, QuickBooks, Calendly, Mailchimp, Asana, and more. Get everything in one platform.') }}</p>
            </div>

            <div class="pricing">
                <x-plans.all calculate-saving-rates="true" show-default-product="1"/>
                <x-products.all />
            </div>
        </div>
    </section>

    {{-- FAQ Section --}}
    <section class="py-16 bg-white" id="faq">
        <div class="max-w-4xl mx-auto px-4">
            <div class="text-center mb-12">
                <x-heading.h6 class="text-primary-500">{{ __('Common Questions') }}</x-heading.h6>
                <x-heading.h2 class="text-gray-900">{{ __('Everything You Need to Know') }}</x-heading.h2>
            </div>

            <x-accordion class="mt-4">
                <x-accordion.item active="true" name="faqs">
                    <x-slot name="title">{{ __('What exactly is WhizIQ?') }}</x-slot>
                    <p>{{ __('WhizIQ is a complete business operating system that replaces 10+ separate tools. You get CRM, sales pipeline, invoicing, appointment booking, task management, marketing automation, financial tracking, document storage, and AI-powered insights—all in one platform. Built specifically for solopreneurs and service businesses making $50K-$500K annually.') }}</p>
                </x-accordion.item>

                <x-accordion.item active="false" name="faqs">
                    <x-slot name="title">{{ __('How does WhizIQ compare to HubSpot, QuickBooks, or other tools?') }}</x-slot>
                    <p>{{ __('HubSpot costs $800+/month for CRM and marketing. QuickBooks is $50-200/month. Calendly is $15-50/month. Asana is $10-25/month. Add up your tools and you\'re easily spending $500-1,000/month. WhizIQ gives you all of this functionality for $29.99-49.99/month—a 90% cost savings with zero data silos.') }}</p>
                </x-accordion.item>

                <x-accordion.item active="false" name="faqs">
                    <x-slot name="title">{{ __('Is my data secure?') }}</x-slot>
                    <p>{{ __('Yes. All data is encrypted with bank-level 256-bit SSL encryption. We never sell your data to third parties. You can export or delete your information anytime. WhizIQ is fully compliant with GDPR and data protection regulations.') }}</p>
                </x-accordion.item>

                <x-accordion.item active="false" name="faqs">
                    <x-slot name="title">{{ __('How long does setup take?') }}</x-slot>
                    <p>{{ __('Most users are up and running in 15-20 minutes. Import your contacts via CSV, connect your calendar (Google/Outlook), and link Stripe for payments. WhizIQ includes setup guides and onboarding checklists to help you get started quickly.') }}</p>
                </x-accordion.item>

                <x-accordion.item active="false" name="faqs">
                    <x-slot name="title">{{ __('Can I cancel anytime?') }}</x-slot>
                    <p>{{ __('Yes. Cancel with one click—no phone calls or retention tactics required. Your data remains accessible for 90 days after cancellation so you can export everything. After 90 days, we permanently delete your data per our privacy policy.') }}</p>
                </x-accordion.item>

                <x-accordion.item active="false" name="faqs">
                    <x-slot name="title">{{ __('What integrations does WhizIQ support?') }}</x-slot>
                    <p>{{ __('WhizIQ integrates with: Google Calendar, Outlook, Apple Calendar, Zoom, Google Meet, Stripe, Paddle, Facebook, Instagram, LinkedIn, Twitter, QuickBooks (import), and more. We add new integrations monthly based on user requests.') }}</p>
                </x-accordion.item>
            </x-accordion>
        </div>
    </section>

</x-layouts.app>
