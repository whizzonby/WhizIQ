<x-layouts.app>
    <x-slot name="title">
        {{ __('WhizzIQ - The All-in-One Business Operating System for Entrepreneurs') }}
    </x-slot>

    {{-- HERO SECTION --}}
    <x-section.hero class="w-full mb-8 md:mb-72">
        <div class="mx-auto text-center h-160 md:h-180 px-4">
            <x-pill class="text-primary-500 bg-primary-50">{{ __('Stop Juggling 10+ Tools') }}</x-pill>
            <x-heading.h1 class="mt-4 text-primary-50 font-bold">
                {{ __('Run Your Entire Business') }}
                <br class="hidden sm:block">
                {{ __('From One Intelligent Platform') }}
            </x-heading.h1>

            <p class="text-primary-50 m-3 text-lg">{{ __('Manage clients, finances, appointments, tasks, documents, and more—all powered by AI. WhizzIQ replaces your entire software stack.') }}</p>

            <div class="flex flex-wrap gap-4 justify-center flex-col md:flex-row mt-6">
                <x-effect.glow></x-effect.glow>

                <x-button-link.secondary href="#pricing" class="self-center !py-3" elementType="a">
                    {{ __('Start Free Trial') }}
                </x-button-link.secondary>
                <x-button-link.primary-outline href="{{ route('register') }}" class="bg-transparent self-center !py-3 text-white border-white">
                    {{ __('See It In Action') }}
                </x-button-link.primary-outline>
            </div>

            <x-user-ratings link="#testimonials" class="items-center justify-center mt-6 relative z-40">
                <x-slot name="avatars">
                    <x-user-ratings.avatar src="https://unsplash.com/photos/rDEOVtE7vOs/download?ixid=M3wxMjA3fDB8MXxzZWFyY2h8Mnx8cGVyc29ufGVufDB8fHx8MTcxMzY4NDI1MHww&force=true&w=640" alt="testimonial 1"/>
                    <x-user-ratings.avatar src="https://unsplash.com/photos/c_GmwfHBDzk/download?ixid=M3wxMjA3fDB8MXxzZWFyY2h8M3x8cGVyc29ufGVufDB8fHx8MTcxMzY4NDI1MHww&force=true&w=640" alt="testimonial 2"/>
                    <x-user-ratings.avatar src="https://unsplash.com/photos/QXevDflbl8A/download?ixid=M3wxMjA3fDB8MXxzZWFyY2h8NHx8cGVyc29ufGVufDB8fHx8MTcxMzY4NDI1MHww&force=true&w=640" alt="testimonial 3"/>
                    <x-user-ratings.avatar src="https://unsplash.com/photos/mjRwhvqEC0U/download?ixid=M3wxMjA3fDB8MXxzZWFyY2h8Nnx8cGVyc29ufGVufDB8fHx8MTcxMzY4NDI1MHww&force=true&w=640" alt="testimonial 4"/>
                    <x-user-ratings.avatar src="https://unsplash.com/photos/C8Ta0gwPbQg/download?ixid=M3wxMjA3fDB8MXxzZWFyY2h8MTl8fHBlcnNvbnxlbnwwfHx8fDE3MTM2ODQyNTB8MA&force=true&w=640" alt="testimonial 5"/>
                </x-slot>

                {{ __('Trusted by thousands of entrepreneurs and small businesses worldwide') }}
            </x-user-ratings>

            <div class="mx-auto md:max-w-3xl lg:max-w-5xl">
                <img class="drop-shadow-2xl mt-8 transition hover:scale-101 rounded-2xl" src="{{URL::asset('/images/features/hero-image.png')}}" alt="WhizzIQ Dashboard" />
            </div>
        </div>
    </x-section.hero>

    {{-- PROBLEM SECTION --}}
    <div class="max-w-none md:max-w-4xl mx-auto text-center px-4 py-32 my-16">
        <x-heading.h6 class="text-primary-500">{{ __('The Problem') }}</x-heading.h6>
        <x-heading.h2 class="text-primary-900">{{ __('Too Many Tools. Too Much Chaos.') }}</x-heading.h2>
        <p class="mt-4 text-lg text-neutral-600">
            {{ __('Most businesses juggle 10+ disconnected tools—CRM, accounting, scheduling, documents, passwords, email marketing. The result? Data silos, wasted hours, and bills that add up to hundreds monthly.') }}
        </p>
    </div>

    {{-- SOLUTION SECTION --}}
    <x-section.columns class="max-w-none md:max-w-6xl pt-16 bg-primary-50 rounded-3xl p-8" id="features">
        <x-section.column>
            <div x-intersect="$el.classList.add('slide-in-top')">
                <x-heading.h6 class="text-primary-500">{{ __('The Solution') }}</x-heading.h6>
                <x-heading.h2 class="text-primary-900">
                    {{ __('Everything Your Business Needs, One Platform') }}
                </x-heading.h2>
            </div>

            <p class="mt-4 text-lg">
                {{ __('WhizzIQ is the first true all-in-one business operating system. We didn\'t just bundle features—we built an intelligent platform where everything connects seamlessly.') }}
            </p>
            <p class="mt-4">
                {{ __('From your first client contact to your year-end tax filing, WhizzIQ handles it all. No more jumping between apps. No more data entry twice. No more wondering where that important file went.') }}
            </p>
            <p class="mt-4 font-semibold text-primary-700">
                {{ __('One login. One dashboard. Complete control.') }}
            </p>
        </x-section.column>

        <x-section.column>
            <img src="{{URL::asset('/images/features/payments.png')}}" alt="All-in-One Platform" class="rounded-2xl shadow-lg"></img>
        </x-section.column>
    </x-section.columns>

    {{-- KEY FEATURES GRID --}}
    <div class="text-center mt-20 mx-4">
        <x-heading.h6 class="text-primary-500">{{ __('Complete Business Management') }}</x-heading.h6>
        <x-heading.h2 class="text-primary-900">{{ __('Every Tool You Need to Grow') }}</x-heading.h2>
        <p class="mt-4 text-neutral-600 max-w-3xl mx-auto">{{ __('Replace your entire software stack with one intelligent platform designed specifically for entrepreneurs and small businesses.') }}</p>
    </div>

    <x-section.columns class="max-w-none md:max-w-6xl mt-12 gap-6">
        <x-section.column class="flex flex-col items-start text-left p-6 bg-white rounded-xl shadow-sm hover:shadow-md transition">
            <div class="p-3 bg-primary-100 rounded-lg mb-4">
                <x-icon.fancy name="users" class="w-12 h-12 text-primary-600" />
            </div>
            <x-heading.h3 class="pt-2 text-primary-900">{{ __('CRM & Sales Pipeline') }}</x-heading.h3>
            <p class="mt-2 text-neutral-600">{{ __('Manage unlimited contacts, track deals through your pipeline, score relationships, and never miss a follow-up. AI helps identify your hottest leads.') }}</p>
        </x-section.column>

        <x-section.column class="flex flex-col items-start text-left p-6 bg-white rounded-xl shadow-sm hover:shadow-md transition">
            <div class="p-3 bg-primary-100 rounded-lg mb-4">
                <svg class="w-12 h-12 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
            </div>
            <x-heading.h3 class="pt-2 text-primary-900">{{ __('Financial Management') }}</x-heading.h3>
            <p class="mt-2 text-neutral-600">{{ __('Create professional invoices, track expenses, manage cash flow, and get real-time profit/loss insights. AI auto-categorizes expenses and optimizes taxes.') }}</p>
        </x-section.column>

        <x-section.column class="flex flex-col items-start text-left p-6 bg-white rounded-xl shadow-sm hover:shadow-md transition">
            <div class="p-3 bg-primary-100 rounded-lg mb-4">
                <svg class="w-12 h-12 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
            </div>
            <x-heading.h3 class="pt-2 text-primary-900">{{ __('Smart Scheduling') }}</x-heading.h3>
            <p class="mt-2 text-neutral-600">{{ __('Automated booking with calendar sync (Google, Outlook, iCloud), Zoom integration, recurring appointments, and aftercare workflows. Let clients book 24/7.') }}</p>
        </x-section.column>
    </x-section.columns>

    <x-section.columns class="max-w-none md:max-w-6xl mt-6 gap-6">
        <x-section.column class="flex flex-col items-start text-left p-6 bg-white rounded-xl shadow-sm hover:shadow-md transition">
            <div class="p-3 bg-primary-100 rounded-lg mb-4">
                <svg class="w-12 h-12 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            </div>
            <x-heading.h3 class="pt-2 text-primary-900">{{ __('Document Vault') }}</x-heading.h3>
            <p class="mt-2 text-neutral-600">{{ __('Secure storage for contracts, receipts, and files with AI analysis, OCR text extraction, version control, and smart search. Never lose an important document again.') }}</p>
        </x-section.column>

        <x-section.column class="flex flex-col items-start text-left p-6 bg-white rounded-xl shadow-sm hover:shadow-md transition">
            <div class="p-3 bg-primary-100 rounded-lg mb-4">
                <svg class="w-12 h-12 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
            </div>
            <x-heading.h3 class="pt-2 text-primary-900">{{ __('Task & Goal Management') }}</x-heading.h3>
            <p class="mt-2 text-neutral-600">{{ __('AI-powered task prioritization, goal tracking with OKRs, kanban boards, and automatic task extraction from documents and emails. Stay focused on what matters.') }}</p>
        </x-section.column>

        <x-section.column class="flex flex-col items-start text-left p-6 bg-white rounded-xl shadow-sm hover:shadow-md transition">
            <div class="p-3 bg-primary-100 rounded-lg mb-4">
                <svg class="w-12 h-12 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
            </div>
            <x-heading.h3 class="pt-2 text-primary-900">{{ __('Secure Password Vault') }}</x-heading.h3>
            <p class="mt-2 text-neutral-600">{{ __('Military-grade encrypted storage for all your business passwords. Password health monitoring, breach detection, and automatic update reminders keep you secure.') }}</p>
        </x-section.column>
    </x-section.columns>

    {{-- AI FEATURES HIGHLIGHT --}}
    <x-section.columns class="max-w-none md:max-w-6xl mt-20 bg-gradient-to-br from-primary-50 to-primary-100 rounded-3xl p-8 flex-wrap-reverse">
        <x-section.column>
            <img src="{{URL::asset('/images/features/colors.png')}}" alt="AI-Powered Features" class="rounded-2xl shadow-lg" />
        </x-section.column>

        <x-section.column>
            <div x-intersect="$el.classList.add('slide-in-top')">
                <x-heading.h6 class="text-primary-500">{{ __('Powered by Artificial Intelligence') }}</x-heading.h6>
                <x-heading.h2 class="text-primary-900">
                    {{ __('Your AI Business Co-Pilot') }}
                </x-heading.h2>
            </div>

            <p class="mt-4 text-lg">
                {{ __('WhizzIQ doesn\'t just store your data—it understands it and helps you make smarter decisions.') }}
            </p>

            <ul class="mt-6 space-y-3">
                <li class="flex gap-3">
                    <span class="flex-shrink-0 p-1 bg-primary-500 rounded-full h-6 w-6 flex items-center justify-center">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    </span>
                    <span><strong>Auto-categorize expenses</strong> and identify tax deductions</span>
                </li>
                <li class="flex gap-3">
                    <span class="flex-shrink-0 p-1 bg-primary-500 rounded-full h-6 w-6 flex items-center justify-center">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    </span>
                    <span><strong>Extract tasks</strong> automatically from documents and emails</span>
                </li>
                <li class="flex gap-3">
                    <span class="flex-shrink-0 p-1 bg-primary-500 rounded-full h-6 w-6 flex items-center justify-center">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    </span>
                    <span><strong>Generate SWOT analyses</strong> and risk assessments</span>
                </li>
                <li class="flex gap-3">
                    <span class="flex-shrink-0 p-1 bg-primary-500 rounded-full h-6 w-6 flex items-center justify-center">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    </span>
                    <span><strong>Forecast revenue</strong> and detect financial anomalies</span>
                </li>
                <li class="flex gap-3">
                    <span class="flex-shrink-0 p-1 bg-primary-500 rounded-full h-6 w-6 flex items-center justify-center">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    </span>
                    <span><strong>Optimize your taxes</strong> with intelligent deduction tracking</span>
                </li>
                <li class="flex gap-3">
                    <span class="flex-shrink-0 p-1 bg-primary-500 rounded-full h-6 w-6 flex items-center justify-center">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    </span>
                    <span><strong>Prioritize tasks</strong> based on impact and urgency</span>
                </li>
            </ul>
        </x-section.column>
    </x-section.columns>

    {{-- TAX MANAGEMENT FEATURE --}}
    <x-section.columns class="max-w-none md:max-w-6xl mt-20">
        <x-section.column>
            <div x-intersect="$el.classList.add('slide-in-top')">
                <x-heading.h6 class="text-primary-500">{{ __('Never Stress About Taxes Again') }}</x-heading.h6>
                <x-heading.h2 class="text-primary-900">
                    {{ __('Built-In Tax Optimization') }}
                </x-heading.h2>
            </div>

            <p class="mt-4 text-lg">
                {{ __('Most businesses overpay on taxes because they don\'t track deductions properly. WhizzIQ automatically identifies every tax-deductible expense and organizes your documents for filing.') }}
            </p>
            <p class="mt-4">
                {{ __('With AI-powered tax optimization, OCR document scanning, and automated forecasting, you\'ll maximize deductions and be ready for tax season in minutes, not weeks.') }}
            </p>

            <div class="mt-6 p-4 bg-primary-50 rounded-lg border-l-4 border-primary-500">
                <p class="font-semibold text-primary-900">💡 Pro & Premium plans only</p>
                <p class="mt-1 text-sm text-neutral-600">{{ __('Full tax features including AI optimization, OCR scanning, and unlimited document storage.') }}</p>
            </div>
        </x-section.column>

        <x-section.column>
            <img src="{{URL::asset('/images/features/plans.png')}}" alt="Tax Management" class="rounded-2xl shadow-lg"/>
        </x-section.column>
    </x-section.columns>

    {{-- INTEGRATIONS --}}
    <div class="text-center mt-20 mx-4">
        <x-heading.h6 class="text-primary-500">{{ __('Connects With Your Existing Tools') }}</x-heading.h6>
        <x-heading.h2 class="text-primary-900">{{ __('Seamless Integrations') }}</x-heading.h2>
        <p class="mt-4 text-neutral-600 max-w-3xl mx-auto">{{ __('WhizzIQ works with the tools you already use, making migration painless.') }}</p>
    </div>

    <div class="flex flex-wrap items-center justify-center gap-8 mt-12 px-4 max-w-4xl mx-auto">
        <div class="flex flex-col items-center text-center">
            <svg class="w-16 h-16 text-neutral-400" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>
            <p class="mt-2 font-medium">Google Calendar</p>
        </div>
        <div class="flex flex-col items-center text-center">
            <svg class="w-16 h-16 text-neutral-400" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>
            <p class="mt-2 font-medium">Zoom</p>
        </div>
        <div class="flex flex-col items-center text-center">
            <svg class="w-16 h-16 text-neutral-400" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>
            <p class="mt-2 font-medium">Outlook</p>
        </div>
        <div class="flex flex-col items-center text-center">
            <svg class="w-16 h-16 text-neutral-400" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>
            <p class="mt-2 font-medium">Stripe</p>
        </div>
        <div class="flex flex-col items-center text-center">
            <svg class="w-16 h-16 text-neutral-400" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>
            <p class="mt-2 font-medium">WhatsApp</p>
        </div>
        <div class="flex flex-col items-center text-center">
            <svg class="w-16 h-16 text-neutral-400" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>
            <p class="mt-2 font-medium">Mailgun</p>
        </div>
    </div>

    {{-- PRICING SECTION --}}
    <div class="mx-4 mt-24">
        <x-heading.h6 class="text-center text-primary-500" id="pricing">
            {{ __('Simple, Transparent Pricing') }}
        </x-heading.h6>
        <x-heading.h2 class="text-primary-900 text-center">
            {{ __('Choose Your Plan') }}
        </x-heading.h2>
        <p class="text-center mt-4 text-neutral-600 max-w-2xl mx-auto">
            {{ __('Start with what you need today. Upgrade as you grow. All plans include core features—higher tiers unlock AI superpowers and unlimited resources.') }}
        </p>
    </div>

    <div class="pricing mt-8">
        <x-plans.all calculate-saving-rates="true" show-default-product="1"/>
    </div>

    {{-- COMPARISON TABLE --}}
    <div class="max-w-none md:max-w-6xl mx-auto mt-16 px-4">
        <div class="text-center mb-8">
            <x-heading.h3 class="text-primary-900">{{ __('Compare Plans') }}</x-heading.h3>
            <p class="mt-2 text-neutral-600">{{ __('See exactly what\'s included in each plan') }}</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full border-collapse bg-white rounded-xl shadow-sm">
                <thead>
                    <tr class="border-b border-neutral-200">
                        <th class="text-left p-4 font-semibold text-primary-900">Feature</th>
                        <th class="text-center p-4 font-semibold text-primary-900">Starter</th>
                        <th class="text-center p-4 font-semibold text-primary-900 bg-primary-50">Pro</th>
                        <th class="text-center p-4 font-semibold text-primary-900">Premium</th>
                    </tr>
                </thead>
                <tbody class="text-sm">
                    <tr class="border-b border-neutral-100">
                        <td class="p-4 text-neutral-700">Contacts</td>
                        <td class="p-4 text-center">500</td>
                        <td class="p-4 text-center bg-primary-50">Unlimited</td>
                        <td class="p-4 text-center">Unlimited</td>
                    </tr>
                    <tr class="border-b border-neutral-100">
                        <td class="p-4 text-neutral-700">Appointments</td>
                        <td class="p-4 text-center">Unlimited</td>
                        <td class="p-4 text-center bg-primary-50">Unlimited</td>
                        <td class="p-4 text-center">Unlimited</td>
                    </tr>
                    <tr class="border-b border-neutral-100">
                        <td class="p-4 text-neutral-700">Invoices</td>
                        <td class="p-4 text-center">50/month</td>
                        <td class="p-4 text-center bg-primary-50">Unlimited</td>
                        <td class="p-4 text-center">Unlimited</td>
                    </tr>
                    <tr class="border-b border-neutral-100">
                        <td class="p-4 text-neutral-700">Document Storage</td>
                        <td class="p-4 text-center">1 GB</td>
                        <td class="p-4 text-center bg-primary-50">5 GB</td>
                        <td class="p-4 text-center">Unlimited</td>
                    </tr>
                    <tr class="border-b border-neutral-100">
                        <td class="p-4 text-neutral-700">AI Requests/Day</td>
                        <td class="p-4 text-center">20</td>
                        <td class="p-4 text-center bg-primary-50">75</td>
                        <td class="p-4 text-center">200</td>
                    </tr>
                    <tr class="border-b border-neutral-100">
                        <td class="p-4 text-neutral-700">Tax Features</td>
                        <td class="p-4 text-center text-neutral-400">—</td>
                        <td class="p-4 text-center bg-primary-50">✓ Basic</td>
                        <td class="p-4 text-center">✓ AI-Powered</td>
                    </tr>
                    <tr class="border-b border-neutral-100">
                        <td class="p-4 text-neutral-700">Calendar Integration</td>
                        <td class="p-4 text-center text-neutral-400">—</td>
                        <td class="p-4 text-center bg-primary-50">✓</td>
                        <td class="p-4 text-center">✓</td>
                    </tr>
                    <tr>
                        <td class="p-4 text-neutral-700">AI Document Analysis</td>
                        <td class="p-4 text-center text-neutral-400">—</td>
                        <td class="p-4 text-center bg-primary-50 text-neutral-400">—</td>
                        <td class="p-4 text-center">✓</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    {{-- FAQ SECTION --}}
    <div class="text-center mt-24 mx-4" id="faq">
        <x-heading.h6 class="text-primary-500">{{ __('FAQ') }}</x-heading.h6>
        <x-heading.h2 class="text-primary-900">{{ __('Frequently Asked Questions') }}</x-heading.h2>
        <p class="mt-4 text-neutral-600">{{ __('Everything you need to know about WhizzIQ') }}</p>
    </div>

    <div class="max-w-none md:max-w-4xl mx-auto">
        <x-accordion class="mt-8 p-4">
            <x-accordion.item active="true" name="faqs">
                <x-slot name="title">{{ __('What exactly is WhizzIQ?') }}</x-slot>
                <p>
                    {{ __('WhizzIQ is an all-in-one business operating system designed specifically for entrepreneurs, freelancers, and small businesses. Instead of juggling 10+ different tools (CRM, accounting software, scheduling apps, document storage, password managers, etc.), WhizzIQ combines everything into one intelligent platform.') }}
                </p>
                <p class="mt-3">
                    {{ __('Think of it as your complete business command center—from managing client relationships and creating invoices to scheduling appointments and filing taxes, all powered by AI to help you work smarter, not harder.') }}
                </p>
            </x-accordion.item>

            <x-accordion.item active="false" name="faqs">
                <x-slot name="title">{{ __('How is WhizzIQ different from other business software?') }}</x-slot>
                <p>
                    {{ __('Most business software forces you to piece together multiple disconnected tools: Salesforce for CRM, QuickBooks for accounting, Calendly for scheduling, Dropbox for files, LastPass for passwords, etc. This creates data silos, duplicate entry, and subscription fatigue.') }}
                </p>
                <p class="mt-3">
                    {{ __('WhizzIQ is the first true all-in-one platform where everything connects seamlessly. When you create an invoice, it links to the client, tracks revenue, updates your financial metrics, and sets a follow-up reminder—all automatically. Plus, our AI layer understands your data and helps you make better decisions.') }}
                </p>
            </x-accordion.item>

            <x-accordion.item active="false" name="faqs">
                <x-slot name="title">{{ __('Do I really need all these features?') }}</x-slot>
                <p>
                    {{ __('You might not use every feature on day one, but as your business grows, you will. That\'s the beauty of WhizzIQ—you start with what you need today (maybe just invoicing and appointments), and unlock more capabilities as you scale.') }}
                </p>
                <p class="mt-3">
                    {{ __('Even if you only use 50% of the platform, you\'re still replacing 5+ separate subscriptions at a fraction of the cost. And everything is already integrated, so there\'s no painful migration when you\'re ready to add CRM, document management, or tax optimization.') }}
                </p>
            </x-accordion.item>

            <x-accordion.item active="false" name="faqs">
                <x-slot name="title">{{ __('What makes the AI features special?') }}</x-slot>
                <p>
                    {{ __('Our AI doesn\'t just add a chatbot—it deeply integrates into your workflows to save you hours of manual work:') }}
                </p>
                <ul class="mt-3 list-disc list-inside space-y-2">
                    <li>{{ __('Automatically categorizes expenses and identifies tax deductions') }}</li>
                    <li>{{ __('Extracts action items from documents and meeting notes') }}</li>
                    <li>{{ __('Generates SWOT analyses and business insights from your data') }}</li>
                    <li>{{ __('Forecasts revenue trends and detects financial anomalies') }}</li>
                    <li>{{ __('Optimizes tax strategies based on your spending patterns') }}</li>
                    <li>{{ __('Scores leads and prioritizes tasks by impact') }}</li>
                </ul>
                <p class="mt-3">
                    {{ __('It\'s like having a smart business consultant working 24/7 in the background.') }}
                </p>
            </x-accordion.item>

            <x-accordion.item active="false" name="faqs">
                <x-slot name="title">{{ __('Can WhizzIQ really replace my accountant?') }}</x-slot>
                <p>
                    {{ __('WhizzIQ doesn\'t replace your accountant—it makes them more efficient (and cheaper). The platform automatically tracks every expense, categorizes transactions, identifies deductions, and organizes all your tax documents.') }}
                </p>
                <p class="mt-3">
                    {{ __('When tax season comes, you can hand your accountant a complete, organized package instead of a shoebox full of receipts. This reduces their billable hours significantly. For simple businesses, our Premium plan even includes tools to file basic taxes yourself, though we always recommend consulting a professional for complex situations.') }}
                </p>
            </x-accordion.item>

            <x-accordion.item active="false" name="faqs">
                <x-slot name="title">{{ __('Is my data secure?') }}</x-slot>
                <p>
                    {{ __('Absolutely. We take security seriously:') }}
                </p>
                <ul class="mt-3 list-disc list-inside space-y-2">
                    <li>{{ __('Military-grade encryption for sensitive data (passwords, financial info)') }}</li>
                    <li>{{ __('Two-factor authentication (2FA) for all accounts') }}</li>
                    <li>{{ __('Regular security audits and penetration testing') }}</li>
                    <li>{{ __('Compliant with GDPR, SOC 2, and industry standards') }}</li>
                    <li>{{ __('Daily encrypted backups') }}</li>
                    <li>{{ __('Role-based access controls for teams') }}</li>
                </ul>
                <p class="mt-3">
                    {{ __('Your business data is yours alone. We never sell or share it with third parties.') }}
                </p>
            </x-accordion.item>

            <x-accordion.item active="false" name="faqs">
                <x-slot name="title">{{ __('What if I\'m already using QuickBooks/Salesforce/etc.?') }}</x-slot>
                <p>
                    {{ __('We make migration painless with CSV/Excel import tools for contacts, invoices, and other core data. You can also run WhizzIQ alongside your existing tools initially and gradually transition features over time.') }}
                </p>
                <p class="mt-3">
                    {{ __('Many customers start by using WhizzIQ for just scheduling and client management, then migrate their financial data over a few months once they\'re comfortable. There\'s no rush—use the integration approach that works for your business.') }}
                </p>
            </x-accordion.item>

            <x-accordion.item active="false" name="faqs">
                <x-slot name="title">{{ __('Do you offer a free trial?') }}</x-slot>
                <p>
                    {{ __('Yes! Every plan includes a 14-day free trial with full access to all features. No credit card required to start. Test drive the entire platform, import your data, and make sure WhizzIQ is the right fit before committing.') }}
                </p>
                <p class="mt-3">
                    {{ __('If you need more time to evaluate, just reach out to our support team. We want you to be confident in your decision.') }}
                </p>
            </x-accordion.item>

            <x-accordion.item active="false" name="faqs">
                <x-slot name="title">{{ __('What kind of support do you provide?') }}</x-slot>
                <p>
                    {{ __('All plans include:') }}
                </p>
                <ul class="mt-3 list-disc list-inside space-y-2">
                    <li><strong>Starter:</strong> {{ __('Email support with 24-48 hour response time + comprehensive documentation') }}</li>
                    <li><strong>Pro:</strong> {{ __('Priority email support with 12-hour response time + video tutorials') }}</li>
                    <li><strong>Premium:</strong> {{ __('Premium support with 4-hour response time + dedicated account manager + onboarding call') }}</li>
                </ul>
                <p class="mt-3">
                    {{ __('Plus, our Help Center includes step-by-step guides, video tutorials, and FAQs to help you get the most out of WhizzIQ.') }}
                </p>
            </x-accordion.item>

            <x-accordion.item active="false" name="faqs">
                <x-slot name="title">{{ __('Can I cancel anytime?') }}</x-slot>
                <p>
                    {{ __('Yes, absolutely. All plans are month-to-month with no long-term contracts. You can cancel anytime from your dashboard. If you cancel, you\'ll have access until the end of your current billing period, and you can export all your data before leaving.') }}
                </p>
                <p class="mt-3">
                    {{ __('We also offer annual plans with significant discounts (save up to 20%) if you want to commit for a year.') }}
                </p>
            </x-accordion.item>

            <x-accordion.item active="false" name="faqs">
                <x-slot name="title">{{ __('Who is WhizzIQ best for?') }}</x-slot>
                <p>
                    {{ __('WhizzIQ is designed for:') }}
                </p>
                <ul class="mt-3 list-disc list-inside space-y-2">
                    <li>{{ __('Freelancers and solopreneurs managing clients and projects') }}</li>
                    <li>{{ __('Small business owners (1-50 employees) needing complete business management') }}</li>
                    <li>{{ __('Service-based professionals (consultants, coaches, agencies)') }}</li>
                    <li>{{ __('Entrepreneurs who want data-driven insights to grow faster') }}</li>
                    <li>{{ __('Anyone tired of juggling multiple subscriptions and tools') }}</li>
                </ul>
                <p class="mt-3">
                    {{ __('If you\'re currently using 5+ different business tools, WhizzIQ can probably replace most of them while saving you time and money.') }}
                </p>
            </x-accordion.item>
        </x-accordion>
    </div>

</x-layouts.app>
