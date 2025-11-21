<x-layouts.app>
    <x-slot name="title">
        {{ __('WhizzIQ - Intelligent Business Management Platform') }}
    </x-slot>

    {{-- Hero Section --}}
    <x-section.hero class="w-full mb-12 md:mb-20">
        <div class="mx-auto text-center px-4 py-16 md:py-24">
            <x-pill class="text-primary-500 bg-primary-50 text-lg px-6 py-3">{{ __('Automated Tax Compliance for Small Business') }}</x-pill>

            <x-heading.h1 class="mt-8 text-primary-50 font-bold text-5xl md:text-7xl lg:text-8xl leading-tight">
                {{ __('Stop Losing Money to Tax Penalties.') }}
            </x-heading.h1>

            <x-heading.h2 class="mt-4 text-primary-50 font-semibold text-3xl md:text-5xl lg:text-6xl leading-tight">
                {{ __('WhizzIQ Automates Your Bookkeeping & Tax Filing.') }}
            </x-heading.h2>

            <p class="text-primary-50 text-lg md:text-xl lg:text-2xl mt-8 max-w-4xl mx-auto leading-relaxed">{{ __('The only business platform that automatically categorizes expenses, calculates quarterly taxes, and files for you. Built for solopreneurs and small businesses making $50K-$500K.') }}</p>

            <div class="flex flex-wrap gap-4 justify-center flex-col md:flex-row mt-12">
                <x-effect.glow></x-effect.glow>

                <x-button-link.secondary href="#pricing" class="self-center py-4 px-8 text-lg!" elementType="a">
                    {{ __('Start Free 14-Day Trial') }}
                </x-button-link.secondary>
                <x-button-link.primary-outline href="#features" class="bg-transparent self-center py-4 px-8 text-lg! text-white border-white border-2">
                    {{ __('See Tax Automation Demo') }}
                </x-button-link.primary-outline>
            </div>
        </div>
    </x-section.hero>

    {{-- Value Proposition Section --}}
    <div class="max-w-none md:max-w-6xl mx-auto mt-16 px-4" id="features">
        <div class="text-center mb-12">
            <x-heading.h6 class="text-primary-500">
                {{ __('Built for Small Business Owners') }}
            </x-heading.h6>
            <x-heading.h2 class="text-primary-900">
                {{ __('You\'re Paying $300-500/Month for Accounting. Here\'s Why That Ends Today.') }}
            </x-heading.h2>
            <p class="mt-4 text-gray-600 max-w-2xl mx-auto">
                {{ __('Most small business owners waste thousands of dollars and countless hours on disconnected tools and manual processes. WhizzIQ automates your entire back office—bookkeeping, invoicing, expense tracking, and tax compliance—so you can focus on growing revenue instead of managing spreadsheets.') }}
            </p>
        </div>

        {{-- Comparison Grid --}}
        <div class="grid md:grid-cols-2 gap-8 mt-12">
            {{-- Without WhizzIQ --}}
            <div class="bg-gray-50 border-2 border-gray-200 rounded-2xl p-8">
                <div class="text-center mb-6">
                    <x-heading.h3 class="text-gray-700">{{ __('Without WhizzIQ') }}</x-heading.h3>
                </div>
                <ul class="space-y-4">
                    <li class="flex items-start">
                        <svg class="w-6 h-6 text-red-500 mr-3 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        <div>
                            <span class="font-semibold text-gray-900">{{ __('$300-500/month') }}</span>
                            <span class="text-gray-600"> {{ __('paying an accountant or bookkeeper') }}</span>
                        </div>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-6 h-6 text-red-500 mr-3 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        <div>
                            <span class="font-semibold text-gray-900">{{ __('15+ hours per week') }}</span>
                            <span class="text-gray-600"> {{ __('manually categorizing expenses and creating invoices') }}</span>
                        </div>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-6 h-6 text-red-500 mr-3 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        <div>
                            <span class="font-semibold text-gray-900">{{ __('$2,000-5,000') }}</span>
                            <span class="text-gray-600"> {{ __('in IRS penalties from missed deadlines and errors') }}</span>
                        </div>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-6 h-6 text-red-500 mr-3 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        <div>
                            <span class="font-semibold text-gray-900">{{ __('5-10 different apps') }}</span>
                            <span class="text-gray-600"> {{ __('for CRM, invoicing, tasks, documents, scheduling') }}</span>
                        </div>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-6 h-6 text-red-500 mr-3 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        <div>
                            <span class="font-semibold text-gray-900">{{ __('Constant stress') }}</span>
                            <span class="text-gray-600"> {{ __('worrying about tax deadlines and compliance') }}</span>
                        </div>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-6 h-6 text-red-500 mr-3 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        <div>
                            <span class="font-semibold text-gray-900">{{ __('Lost receipts') }}</span>
                            <span class="text-gray-600"> {{ __('and missed tax deductions worth thousands') }}</span>
                        </div>
                    </li>
                </ul>
                <div class="mt-8 text-center">
                    <p class="text-xl font-bold text-red-600">{{ __('Total Annual Cost: $8,600 - $14,000+') }}</p>
                    <p class="text-sm text-gray-500 mt-2">{{ __('Plus 780+ hours of your time') }}</p>
                </div>
            </div>

            {{-- With WhizzIQ --}}
            <div class="relative">
                <div class="absolute top-0 left-1/2 transform -translate-x-1/2 -translate-y-1/2 z-10">
                    <span class="bg-primary-500 text-white px-6 py-2 rounded-full text-sm font-semibold block">{{ __('Recommended') }}</span>
                </div>
                <div class="bg-primary-50 border-2 border-primary-500 rounded-2xl p-8 pt-8">
                    <div class="text-center mb-6 mt-4">
                        <x-heading.h3 class="text-primary-900">{{ __('With WhizzIQ') }}</x-heading.h3>
                    </div>
                <ul class="space-y-4">
                    <li class="flex items-start">
                        <svg class="w-6 h-6 text-green-500 mr-3 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <div>
                            <span class="font-semibold text-gray-900">{{ __('$29-49/month') }}</span>
                            <span class="text-gray-700"> {{ __('all-inclusive. No accountant needed.') }}</span>
                        </div>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-6 h-6 text-green-500 mr-3 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <div>
                            <span class="font-semibold text-gray-900">{{ __('30 minutes per week') }}</span>
                            <span class="text-gray-700"> {{ __('with fully automated expense tracking and invoicing') }}</span>
                        </div>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-6 h-6 text-green-500 mr-3 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <div>
                            <span class="font-semibold text-gray-900">{{ __('$0 in penalties') }}</span>
                            <span class="text-gray-700"> {{ __('automatic deadline reminders and tax calculations') }}</span>
                        </div>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-6 h-6 text-green-500 mr-3 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <div>
                            <span class="font-semibold text-gray-900">{{ __('1 unified platform') }}</span>
                            <span class="text-gray-700"> {{ __('everything you need in one place') }}</span>
                        </div>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-6 h-6 text-green-500 mr-3 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <div>
                            <span class="font-semibold text-gray-900">{{ __('Peace of mind') }}</span>
                            <span class="text-gray-700"> {{ __('never worry about tax compliance again') }}</span>
                        </div>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-6 h-6 text-green-500 mr-3 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <div>
                            <span class="font-semibold text-gray-900">{{ __('Every deduction captured') }}</span>
                            <span class="text-gray-700"> {{ __('automatic receipt scanning and categorization') }}</span>
                        </div>
                    </li>
                </ul>
                <div class="mt-8 text-center">
                    <p class="text-xl font-bold text-green-600">{{ __('Total Annual Cost: $348 - $588') }}</p>
                    <p class="text-sm text-gray-700 mt-2">{{ __('Plus only 26 hours of your time') }}</p>
                </div>
                </div>
            </div>
        </div>

        {{-- Savings Highlight --}}
        <div class="mt-12 bg-gradient-to-r from-primary-600 to-primary-700 rounded-2xl p-8 md:p-12 text-center text-white">
            <x-heading.h2 class="text-white mb-4">
                {{ __('Save $8,000 - $14,000 Per Year') }}
            </x-heading.h2>
            <p class="text-xl text-primary-50 mb-2">
                {{ __('Plus get back 750+ hours to focus on growing your business instead of managing spreadsheets.') }}
            </p>
            <p class="text-lg text-primary-100 mb-8">
                {{ __('That\'s like hiring a full-time bookkeeper and accountant for less than the cost of a Netflix subscription.') }}
            </p>
            <div class="flex flex-col md:flex-row gap-4 justify-center items-center">
                <x-button-link.secondary href="#pricing" class="bg-white text-primary-600 hover:bg-primary-50 px-8 py-4 text-lg" elementType="a">
                    {{ __('Start Your Free Trial Now') }}
                </x-button-link.secondary>
                <p class="text-sm text-primary-100">{{ __('14 days free. No credit card required. Cancel anytime.') }}</p>
            </div>
        </div>
    </div>

    {{-- Additional Features --}}
    <div class="max-w-none md:max-w-5xl mx-auto mt-20 px-4">
        <div class="text-center mb-8">
            <x-heading.h3 class="text-primary-900">
                {{ __('Everything Else You Need to Run Your Business') }}
            </x-heading.h3>
            <p class="text-gray-600 mt-2">{{ __('All included. No extra fees or add-ons.') }}</p>
        </div>

        <div class="grid md:grid-cols-3 gap-6 mt-8">
            <div class="bg-white border border-gray-200 rounded-xl p-6 text-center hover:border-primary-300 transition">
                <div class="inline-flex items-center justify-center w-12 h-12 bg-primary-100 text-primary-600 rounded-lg mb-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <h4 class="font-semibold text-gray-900 mb-2">{{ __('Appointment Scheduling') }}</h4>
                <p class="text-base text-gray-700">{{ __('Public booking pages, calendar sync, and automatic Zoom/Meet links.') }}</p>
            </div>

            <div class="bg-white border border-gray-200 rounded-xl p-6 text-center hover:border-primary-300 transition">
                <div class="inline-flex items-center justify-center w-12 h-12 bg-primary-100 text-primary-600 rounded-lg mb-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
                <h4 class="font-semibold text-gray-900 mb-2">{{ __('Business Analytics') }}</h4>
                <p class="text-base text-gray-700">{{ __('Financial insights, cash flow forecasting, and performance tracking.') }}</p>
            </div>

            <div class="bg-white border border-gray-200 rounded-xl p-6 text-center hover:border-primary-300 transition">
                <div class="inline-flex items-center justify-center w-12 h-12 bg-primary-100 text-primary-600 rounded-lg mb-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path>
                    </svg>
                </div>
                <h4 class="font-semibold text-gray-900 mb-2">{{ __('Marketing Tools') }}</h4>
                <p class="text-base text-gray-700">{{ __('Email campaigns and marketing performance tracking.') }}</p>
            </div>

            <div class="bg-white border border-gray-200 rounded-xl p-6 text-center hover:border-primary-300 transition">
                <div class="inline-flex items-center justify-center w-12 h-12 bg-primary-100 text-primary-600 rounded-lg mb-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                </div>
                <h4 class="font-semibold text-gray-900 mb-2">{{ __('Secure Password Vault') }}</h4>
                <p class="text-base text-gray-700">{{ __('Encrypted storage for credentials and sensitive business data.') }}</p>
            </div>

            <div class="bg-white border border-gray-200 rounded-xl p-6 text-center hover:border-primary-300 transition">
                <div class="inline-flex items-center justify-center w-12 h-12 bg-primary-100 text-primary-600 rounded-lg mb-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
                <h4 class="font-semibold text-gray-900 mb-2">{{ __('Client Management') }}</h4>
                <p class="text-base text-gray-700">{{ __('Track contacts, manage deals, and store client communication history.') }}</p>
            </div>

            <div class="bg-white border border-gray-200 rounded-xl p-6 text-center hover:border-primary-300 transition">
                <div class="inline-flex items-center justify-center w-12 h-12 bg-primary-100 text-primary-600 rounded-lg mb-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path>
                    </svg>
                </div>
                <h4 class="font-semibold text-gray-900 mb-2">{{ __('Plus 50+ More Features') }}</h4>
                <p class="text-base text-gray-700">{{ __('Document vault, task management, goal tracking, reporting, and much more.') }}</p>
            </div>
        </div>
    </div>

    {{-- Pricing Section --}}
    <div class="mx-4 mt-16">
        <x-heading.h6 class="text-center mt-20 text-primary-500" id="pricing">
            {{ __('Ready to Save $8,000+ This Year?') }}
        </x-heading.h6>
        <x-heading.h2 class="text-primary-900 text-center">
            {{ __('Start Your Free Trial Today!') }}
        </x-heading.h2>
    </div>

    <div class="pricing">
        <x-plans.all calculate-saving-rates="true" show-default-product="1"/>
        <x-products.all />
    </div>

    {{-- FAQ Section --}}
    <div class="text-center mt-24 mx-4" id="faq">
        <x-heading.h6 class="text-primary-500">
            {{ __('Common Questions') }}
        </x-heading.h6>
        <x-heading.h2 class="text-primary-900">
            {{ __('Everything You Need to Know Before Starting') }}
        </x-heading.h2>
        <p>{{ __('Real answers to questions from business owners like you.') }}</p>
    </div>

    <div class="max-w-none md:max-w-6xl mx-auto">
        <x-accordion class="mt-4 p-8">
            <x-accordion.item active="true" name="faqs">
                <x-slot name="title">{{ __('Is my financial data secure?') }}</x-slot>

                <p>
                    {{ __('Yes. Your data is encrypted with bank-level 256-bit SSL encryption, the same security used by major financial institutions. We never sell your data to third parties, and you can export or delete your information anytime. WhizzIQ is fully compliant with data protection regulations.') }}
                </p>
            </x-accordion.item>

            <x-accordion.item active="false" name="faqs">
                <x-slot name="title">{{ __('I\'m not tech-savvy. Is WhizzIQ easy to use?') }}</x-slot>

                <p>
                    {{ __('Absolutely. WhizzIQ is designed for business owners, not accountants. Most users are up and running in under 15 minutes. Just snap photos of receipts with your phone, and WhizzIQ handles the categorization, calculations, and tax prep automatically. No accounting knowledge required.') }}
                </p>
            </x-accordion.item>

            <x-accordion.item active="false" name="faqs">
                <x-slot name="title">{{ __('What if I forget to cancel before the trial ends?') }}</x-slot>

                <p>
                    {{ __('We send you 3 reminder emails before your trial ends (at 7 days, 3 days, and 1 day remaining). If you do get charged and want to cancel, just email us within 7 days for a full refund—no questions asked. We want you to use WhizzIQ because it saves you money, not because you forgot to cancel.') }}
                </p>
            </x-accordion.item>

            <x-accordion.item active="false" name="faqs">
                <x-slot name="title">{{ __('Does WhizzIQ actually file my taxes or just prepare them?') }}</x-slot>

                <p>
                    {{__('WhizzIQ prepares your quarterly tax estimates and generates all the forms you need. For actual filing, we integrate with the IRS e-file system for easy submission. You review everything before it\'s filed—you stay in control. Think of it as having a bookkeeper who prepares everything perfectly, then you click "send."')}}
                </p>
            </x-accordion.item>

            <x-accordion.item active="false" name="faqs">
                <x-slot name="title">{{ __('How long does it take to set up?') }}</x-slot>

                <p>
                    {{ __('Most users complete setup in 10-15 minutes. You\'ll connect your bank account (read-only access), set your business category, and you\'re done. WhizzIQ automatically imports your transactions and starts categorizing expenses immediately. You can start uploading receipts on day one.')}}
                </p>
            </x-accordion.item>

            <x-accordion.item active="false" name="faqs">
                <x-slot name="title">{{ __('Can I cancel anytime? What happens to my data?') }}</x-slot>

                <p>
                    {{ __('Yes, cancel anytime with one click—no phone calls or retention tactics. Your data remains accessible for 90 days after cancellation so you can export everything. After 90 days, we permanently delete your data per our privacy policy. You can also request immediate deletion anytime.') }}
                </p>
            </x-accordion.item>
        </x-accordion>
    </div>

</x-layouts.app>
