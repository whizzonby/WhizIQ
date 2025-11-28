<x-layouts.app>
    <x-slot name="title">
        {{ __('WhizzIQ: All-in-One Business Platform | Save $10,000/Year | Free Trial') }}
    </x-slot>

    <style>
        /* Bloom-inspired Complete Redesign */
        * {
            box-sizing: border-box;
        }

        :root {
            --color-cream: #FAF7F0;
            --color-beige: #F5EFE6;
            --color-dark: #1a1a1a;
            --color-accent: #E94560;
            --color-blue: #3B82F6;
            --color-green: #10B981;
            --color-purple: #8B5CF6;
            --color-orange: #F97316;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            color: var(--color-dark);
            line-height: 1.6;
        }

        html {
            scroll-behavior: smooth;
        }

        /* Hero Section */
        .hero-section {
            background: linear-gradient(135deg, var(--color-cream) 0%, var(--color-beige) 100%);
            min-height: 90vh;
            position: relative;
            overflow: hidden;
            padding: 100px 20px 100px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .hero-content {
            max-width: 1200px;
            margin: 0 auto;
            text-align: center;
            position: relative;
            z-index: 10;
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            background: white;
            border: 3px solid var(--color-dark);
            border-radius: 100px;
            font-weight: 700;
            font-size: 14px;
            box-shadow: 5px 5px 0 var(--color-dark);
            margin-bottom: 30px;
            animation: slideDown 0.6s ease-out;
        }

        .hero-title {
            font-size: clamp(40px, 7vw, 80px);
            font-weight: 900;
            line-height: 1.1;
            margin: 30px 0;
            color: var(--color-dark);
            animation: slideUp 0.8s ease-out;
        }

        .hero-title .highlight {
            position: relative;
            display: inline-block;
        }

        .hero-title .highlight::after {
            content: '';
            position: absolute;
            bottom: 10px;
            left: -5px;
            right: -5px;
            height: 20px;
            background: var(--color-accent);
            opacity: 0.3;
            z-index: -1;
            border-radius: 5px;
        }

        .hero-subtitle {
            font-size: clamp(18px, 3vw, 24px);
            color: #4a4a4a;
            max-width: 800px;
            margin: 0 auto 40px;
            line-height: 1.6;
            animation: slideUp 1s ease-out;
        }

        .hero-cta-group {
            display: flex;
            gap: 20px;
            justify-content: center;
            flex-wrap: wrap;
            margin-bottom: 30px;
            animation: slideUp 1.2s ease-out;
        }

        .btn-primary {
            padding: 18px 40px;
            background: var(--color-dark);
            color: white;
            border: 3px solid var(--color-dark);
            border-radius: 50px;
            font-weight: 700;
            font-size: 18px;
            text-decoration: none;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            box-shadow: 5px 5px 0 rgba(0,0,0,0.1);
        }

        .btn-primary:hover {
            background: white;
            color: var(--color-dark);
            transform: translate(-2px, -2px);
            box-shadow: 7px 7px 0 rgba(0,0,0,0.15);
        }

        .btn-secondary {
            padding: 18px 40px;
            background: transparent;
            color: var(--color-dark);
            border: 3px solid var(--color-dark);
            border-radius: 50px;
            font-weight: 700;
            font-size: 18px;
            text-decoration: none;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }

        .btn-secondary:hover {
            background: var(--color-dark);
            color: white;
        }

        .hero-trust {
            display: flex;
            gap: 30px;
            justify-content: center;
            align-items: center;
            flex-wrap: wrap;
            font-size: 15px;
            color: var(--color-dark);
            margin-bottom: 50px;
            font-weight: 600;
        }

        .trust-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }


        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(50px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Problem Section */
        .problem-section {
            padding: 100px 20px;
            background: white;
            position: relative;
        }

        .problem-section::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, #e5e5e5 20%, #e5e5e5 80%, transparent);
        }

        .problem-content {
            max-width: 1200px;
            margin: 0 auto;
        }

        .section-header {
            text-align: center;
            margin-bottom: 70px;
        }

        .section-label {
            display: inline-block;
            padding: 8px 20px;
            background: var(--color-beige);
            border: 2px solid var(--color-dark);
            border-radius: 50px;
            font-weight: 700;
            font-size: 14px;
            margin-bottom: 20px;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
        }

        .section-label:hover {
            transform: translateY(-2px);
            box-shadow: 3px 3px 0 var(--color-dark);
        }

        .section-title {
            font-size: clamp(32px, 5vw, 56px);
            font-weight: 900;
            line-height: 1.2;
            margin-bottom: 20px;
            color: var(--color-dark);
        }

        .section-subtitle {
            font-size: 20px;
            color: #666;
            max-width: 700px;
            margin: 0 auto;
        }

        .problem-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 40px;
            margin-bottom: 50px;
            max-width: 1000px;
            margin-left: auto;
            margin-right: auto;
        }

        .problem-card {
            background: #FFF5F5;
            border: 3px solid var(--color-accent);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 6px 6px 0 var(--color-accent);
            transition: all 0.3s;
        }

        .problem-card:hover {
            transform: translate(-3px, -3px);
            box-shadow: 9px 9px 0 var(--color-accent);
        }

        .problem-icon {
            width: 80px;
            height: 80px;
            background: white;
            border: 3px solid var(--color-accent);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 25px;
            flex-shrink: 0;
        }

        .problem-icon svg {
            width: 40px;
            height: 40px;
            stroke: var(--color-accent);
        }

        .problem-title {
            font-size: 24px;
            font-weight: 800;
            margin-bottom: 15px;
        }

        .problem-description {
            font-size: 16px;
            color: #666;
            line-height: 1.6;
        }

        /* Solution/Features Section */
        .features-section {
            padding: 100px 20px;
            background: var(--color-beige);
            position: relative;
        }

        .features-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 4px;
            background: var(--color-green);
            border-radius: 2px;
        }

        .features-grid {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 30px;
        }

        .feature-card {
            background: white;
            border: 3px solid var(--color-dark);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 6px 6px 0 var(--color-dark);
            transition: all 0.3s;
            position: relative;
        }

        .feature-card:hover {
            transform: translate(-3px, -3px);
            box-shadow: 9px 9px 0 var(--color-dark);
        }

        .feature-number {
            position: absolute;
            top: -15px;
            right: 30px;
            width: 40px;
            height: 40px;
            background: var(--color-accent);
            border: 3px solid var(--color-dark);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 900;
            color: white;
        }

        .feature-icon-wrapper {
            width: 70px;
            height: 70px;
            background: var(--color-beige);
            border: 3px solid var(--color-dark);
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
        }

        .feature-title {
            font-size: 24px;
            font-weight: 800;
            margin-bottom: 15px;
            color: var(--color-dark);
        }

        .feature-description {
            font-size: 16px;
            color: #666;
            margin-bottom: 15px;
            line-height: 1.6;
        }

        .feature-replaces {
            display: inline-block;
            padding: 8px 16px;
            background: #FFF5E5;
            border: 2px solid var(--color-orange);
            border-radius: 50px;
            font-size: 13px;
            font-weight: 700;
            color: var(--color-dark);
        }

        /* Comparison Table */
        .comparison-section {
            padding: 100px 20px;
            background: linear-gradient(180deg, white 0%, var(--color-cream) 100%);
            position: relative;
        }

        .comparison-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 4px;
            background: var(--color-purple);
            border-radius: 2px;
        }

        .comparison-container {
            max-width: 1400px;
            margin: 0 auto;
        }

        .comparison-table-wrapper {
            overflow-x: auto;
            margin-top: 50px;
            border: 3px solid var(--color-dark);
            border-radius: 20px;
            background: white;
        }

        .comparison-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 600px;
        }

        .comparison-table thead {
            background: var(--color-beige);
        }

        .comparison-table th {
            padding: 30px 20px;
            text-align: center;
            border-right: 2px solid #e5e5e5;
            vertical-align: middle;
        }

        .comparison-table th:first-child {
            text-align: left;
            background: var(--color-dark);
            color: white;
            font-size: 18px;
            font-weight: 900;
            vertical-align: middle;
        }

        .comparison-table th:last-child {
            border-right: none;
        }

        .company-logo {
            font-size: 18px;
            font-weight: 900;
            margin-bottom: 10px;
            color: var(--color-dark);
            line-height: 1.3;
        }

        .company-logo.highlight {
            color: var(--color-accent);
            font-size: 20px;
        }

        .company-tagline {
            font-size: 12px;
            color: #666;
            font-weight: 600;
            line-height: 1.4;
            margin: 0 auto;
            max-width: 140px;
        }

        .comparison-table tbody tr {
            border-bottom: 2px solid #e5e5e5;
            min-height: 60px;
        }

        .comparison-table tbody tr:last-child {
            border-bottom: none;
        }

        .comparison-table td {
            padding: 20px;
            text-align: center;
            border-right: 2px solid #e5e5e5;
            font-size: 15px;
            vertical-align: middle;
            min-height: 60px;
        }

        .comparison-table td:first-child {
            text-align: left;
            font-weight: 700;
            background: var(--color-beige);
            color: var(--color-dark);
        }

        .comparison-table td:last-child {
            border-right: none;
        }

        .feature-check {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: var(--color-green);
            font-size: 28px;
            font-weight: 900;
            line-height: 1;
            width: 30px;
            height: 30px;
        }

        .feature-none {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #ccc;
            font-size: 24px;
            font-weight: 600;
            line-height: 1;
            width: 30px;
            height: 30px;
        }

        .feature-price {
            font-size: 14px;
            color: #666;
            font-weight: 600;
        }

        .highlight-col {
            background: #fffbf0;
        }

        .pricing-row {
            background: var(--color-beige);
            font-weight: 900;
        }

        .pricing-row td {
            padding: 30px 20px;
            font-size: 20px;
            vertical-align: middle;
        }

        .pricing-highlight {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: var(--color-green);
            font-size: 24px;
            font-weight: 900;
        }

        .pricing-competitor {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: var(--color-accent);
            font-size: 16px;
            font-weight: 700;
        }

        /* Testimonials */
        .testimonials-section {
            padding: 100px 20px;
            background: var(--color-cream);
            position: relative;
        }

        .testimonials-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 4px;
            background: var(--color-accent);
            border-radius: 2px;
        }

        .testimonials-grid {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 30px;
        }

        .testimonial-card {
            background: var(--color-beige);
            border: 3px solid var(--color-dark);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 6px 6px 0 var(--color-dark);
            transition: all 0.3s;
        }

        .testimonial-card:hover {
            transform: translate(-3px, -3px);
            box-shadow: 9px 9px 0 var(--color-dark);
        }

        .testimonial-header {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 25px;
        }

        .testimonial-avatar {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            border: 3px solid var(--color-dark);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 900;
            font-size: 24px;
            background: white;
        }

        .testimonial-info h4 {
            font-size: 18px;
            font-weight: 800;
            margin-bottom: 5px;
        }

        .testimonial-info p {
            font-size: 14px;
            color: #666;
        }

        .testimonial-content {
            font-size: 16px;
            line-height: 1.7;
            color: var(--color-dark);
            font-style: italic;
        }

        .testimonial-rating {
            margin-top: 20px;
            color: var(--color-orange);
            font-size: 20px;
        }

        /* Pricing Section */
        .pricing-section {
            padding: 100px 20px;
            background: var(--color-beige);
            color: var(--color-dark);
            position: relative;
        }

        .pricing-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 4px;
            background: var(--color-blue);
            border-radius: 2px;
        }

        .pricing-cards {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 20px;
        }

        /* Override pricing card styles for better alignment */
        .pricing-section ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .pricing-section ul li {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 12px 0;
            line-height: 1.6;
        }

        .pricing-section ul li::before {
            content: '✓';
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 20px;
            height: 20px;
            min-width: 20px;
            color: var(--color-green);
            font-weight: 900;
            font-size: 18px;
            margin-top: 2px;
            flex-shrink: 0;
        }

        .pricing-section .section-label {
            background: var(--color-dark);
            color: white;
        }

        .pricing-section .section-title {
            color: var(--color-dark);
        }

        .pricing-section .section-subtitle {
            color: #666;
        }

        /* FAQ Section */
        .faq-section {
            padding: 100px 20px;
            background: white;
            border-top: 3px solid var(--color-dark);
            position: relative;
        }

        .faq-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 3px;
            background: var(--color-accent);
            margin-top: -3px;
        }

        .faq-container {
            max-width: 900px;
            margin: 0 auto;
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            .hero-title {
                font-size: 40px;
            }

            .problem-grid,
            .features-grid,
            .testimonials-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }

            .problem-grid {
                max-width: 100%;
            }

            .section-title {
                font-size: 36px;
            }

            .comparison-table-wrapper {
                border-radius: 10px;
            }

            .comparison-table th,
            .comparison-table td {
                padding: 12px 8px;
                font-size: 12px;
            }

            .company-logo {
                font-size: 13px;
            }

            .company-tagline {
                font-size: 9px;
                max-width: 100px;
            }

            .feature-check {
                font-size: 20px;
                width: 24px;
                height: 24px;
            }

            .feature-none {
                font-size: 18px;
                width: 24px;
                height: 24px;
            }

            .pricing-highlight {
                font-size: 16px;
            }

            .pricing-competitor {
                font-size: 13px;
            }
        }
    </style>

    {{-- HERO SECTION --}}
    <section class="hero-section">
        <div class="hero-content">
            <div class="hero-badge">
                <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                </svg>
                {{ __('The All-in-One Business Operating System') }}
            </div>

            <h1 class="hero-title">
                {{ __('Run your business — not your tools') }}
            </h1>

            <p class="hero-subtitle">
                {{ __('Cut your tool stack, save $10k+/year, and reclaim 5–10 hours of admin time every week.') }}
            </p>

            <div class="hero-cta-group">
                <a href="{{ route('register') }}" class="btn-primary">
                    {{ __('Start Your 14-Day Free Trial') }}
                    <svg width="20" height="20" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L12.586 11H5a1 1 0 110-2h7.586l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd"/>
                    </svg>
                </a>
            </div>

            <div class="hero-trust">
                <div class="trust-item">
                    <svg width="20" height="20" fill="#10B981" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    {{ __('14-day free trial') }}
                </div>
                <div class="trust-item">
                    <svg width="20" height="20" fill="#10B981" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    {{ __('Cancel anytime') }}
                </div>
                <div class="trust-item">
                    <svg width="20" height="20" fill="#10B981" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    {{ __('Setup in 15 minutes') }}
                </div>
            </div>
        </div>
    </section>

    {{-- PROBLEM SECTION --}}
    <section class="problem-section">
        <div class="problem-content">
            <div class="section-header">
                <span class="section-label">{{ __('The Problem') }}</span>
                <h2 class="section-title">{{ __('Are you tired of...') }}</h2>
                <p class="section-subtitle">{{ __('You\'re not alone. Most small businesses face these same frustrations every day.') }}</p>
            </div>

            <div class="problem-grid">
                <div class="problem-card">
                    <div class="problem-icon">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h3 class="problem-title">{{ __('Paying $500–$1,200/mo on disconnected tools?') }}</h3>
                    <p class="problem-description">{{ __('HubSpot, QuickBooks, Calendly, Asana, Mailchimp... Each has its own subscription, login, and learning curve. You\'re spending $6,000-14,000 per year and your tools don\'t even talk to each other.') }}</p>
                </div>

                <div class="problem-card">
                    <div class="problem-icon">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h3 class="problem-title">{{ __('Wasting 5–10 hours/week on admin instead of billable work?') }}</h3>
                    <p class="problem-description">{{ __('Switching between apps, copying data, chasing invoices, missing follow-ups. Hours wasted every week managing your tool stack instead of serving clients or growing your business.') }}</p>
                </div>
            </div>

            <div style="text-align: center; margin-top: 50px;">
                <p style="font-size: 24px; font-weight: 800; color: var(--color-dark); margin-bottom: 20px;">
                    {{ __('We built WhizzIQ so you don\'t have to.') }}
                </p>
            </div>
        </div>
    </section>

    {{-- SOLUTION/FEATURES SECTION --}}
    <section class="features-section" id="features">
        <div class="section-header">
            <span class="section-label">{{ __('The Solution') }}</span>
            <h2 class="section-title">{{ __('Meet WhizzIQ — Your All-in-One Business Control Center') }}</h2>
            <p class="section-subtitle">{{ __('Replace 10+ tools with one platform. Save thousands. Get back your time.') }}</p>
        </div>

        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-number">1</div>
                <div class="feature-icon-wrapper">
                    <svg width="36" height="36" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <h3 class="feature-title">{{ __('Never lose a deal because you forgot to follow up') }}</h3>
                <p class="feature-description">{{ __('Unified CRM and sales pipeline keeps every lead, conversation, and deal in one place. Automated reminders ensure you never miss a follow-up.') }}</p>
                <span class="feature-replaces">{{ __('Replaces: HubSpot ($800+/mo), Pipedrive') }}</span>
            </div>

            <div class="feature-card">
                <div class="feature-number">2</div>
                <div class="feature-icon-wrapper">
                    <svg width="36" height="36" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2zM10 8.5a.5.5 0 11-1 0 .5.5 0 011 0zm5 5a.5.5 0 11-1 0 .5.5 0 011 0z"/>
                    </svg>
                </div>
                <h3 class="feature-title">{{ __('Get paid faster — send branded invoices and accept payments in minutes') }}</h3>
                <p class="feature-description">{{ __('Professional invoicing with one-click payments. Track every dollar in and out. Stop chasing unpaid invoices.') }}</p>
                <span class="feature-replaces">{{ __('Replaces: QuickBooks ($50-200/mo), FreshBooks') }}</span>
            </div>

            <div class="feature-card">
                <div class="feature-number">3</div>
                <div class="feature-icon-wrapper">
                    <svg width="36" height="36" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <h3 class="feature-title">{{ __('Automate bookings and avoid double-bookings & calendar chaos') }}</h3>
                <p class="feature-description">{{ __('Clients book directly into your calendar. Automatic Zoom links, reminders, and rescheduling. No more email back-and-forth.') }}</p>
                <span class="feature-replaces">{{ __('Replaces: Calendly ($15-50/mo), Acuity') }}</span>
            </div>

            <div class="feature-card">
                <div class="feature-number">4</div>
                <div class="feature-icon-wrapper">
                    <svg width="36" height="36" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7a3 3 0 11-6 0 3 3 0 016 0zm6 0a3 3 0 11-6 0 3 3 0 016 0zm-9 6a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <h3 class="feature-title">{{ __('Manage all tasks and projects without juggling 3 different apps') }}</h3>
                <p class="feature-description">{{ __('Keep your team (or yourself) aligned with project boards. Run email campaigns and social posts without leaving the platform.') }}</p>
                <span class="feature-replaces">{{ __('Replaces: Asana ($10-25/mo), Mailchimp ($20-300/mo)') }}</span>
            </div>

            <div class="feature-card">
                <div class="feature-number">5</div>
                <div class="feature-icon-wrapper">
                    <svg width="36" height="36" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                    </svg>
                </div>
                <h3 class="feature-title">{{ __('Grow without the overwhelm — built-in analytics & AI tools') }}</h3>
                <p class="feature-description">{{ __('See what\'s working with real-time analytics. AI-powered insights help you make smarter decisions and automate repetitive work.') }}</p>
                <span class="feature-replaces">{{ __('Included at no extra cost') }}</span>
            </div>
        </div>
    </section>

    {{-- COMPARISON SECTION --}}
    <section class="comparison-section" id="comparison">
        <div class="comparison-container">
            <div class="section-header">
                <span class="section-label">{{ __('The Comparison') }}</span>
                <h2 class="section-title">{{ __('WhizzIQ vs Others') }}</h2>
                <p class="section-subtitle">{{ __('See how WhizzIQ compares to the tools you\'re probably using right now') }}</p>
            </div>

            <div class="comparison-table-wrapper">
                <table class="comparison-table">
                    <thead>
                        <tr>
                            <th>{{ __('Features') }}</th>
                            <th class="highlight-col">
                                <div class="company-logo highlight">WhizzIQ</div>
                                <div class="company-tagline">{{ __('All-in-one for small businesses') }}</div>
                            </th>
                            <th>
                                <div class="company-logo">HubSpot</div>
                                <div class="company-tagline">{{ __('Enterprise CRM & Marketing') }}</div>
                            </th>
                            <th>
                                <div class="company-logo">QuickBooks</div>
                                <div class="company-tagline">{{ __('Accounting software') }}</div>
                            </th>
                            <th>
                                <div class="company-logo">Calendly</div>
                                <div class="company-tagline">{{ __('Scheduling tool') }}</div>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>{{ __('CRM & Contact Management') }}</td>
                            <td class="highlight-col"><span class="feature-check">✓</span></td>
                            <td><span class="feature-check">✓</span></td>
                            <td><span class="feature-none">—</span></td>
                            <td><span class="feature-none">—</span></td>
                        </tr>
                        <tr>
                            <td>{{ __('Sales Pipeline') }}</td>
                            <td class="highlight-col"><span class="feature-check">✓</span></td>
                            <td><span class="feature-check">✓</span></td>
                            <td><span class="feature-none">—</span></td>
                            <td><span class="feature-none">—</span></td>
                        </tr>
                        <tr>
                            <td>{{ __('Invoicing & Payments') }}</td>
                            <td class="highlight-col"><span class="feature-check">✓</span></td>
                            <td><span class="feature-none">—</span></td>
                            <td><span class="feature-check">✓</span></td>
                            <td><span class="feature-none">—</span></td>
                        </tr>
                        <tr>
                            <td>{{ __('Appointment Scheduling') }}</td>
                            <td class="highlight-col"><span class="feature-check">✓</span></td>
                            <td><span class="feature-none">—</span></td>
                            <td><span class="feature-none">—</span></td>
                            <td><span class="feature-check">✓</span></td>
                        </tr>
                        <tr>
                            <td>{{ __('Task & Project Management') }}</td>
                            <td class="highlight-col"><span class="feature-check">✓</span></td>
                            <td><span class="feature-none">—</span></td>
                            <td><span class="feature-none">—</span></td>
                            <td><span class="feature-none">—</span></td>
                        </tr>
                        <tr>
                            <td>{{ __('Marketing Automation') }}</td>
                            <td class="highlight-col"><span class="feature-check">✓</span></td>
                            <td><span class="feature-check">✓</span></td>
                            <td><span class="feature-none">—</span></td>
                            <td><span class="feature-none">—</span></td>
                        </tr>
                        <tr>
                            <td>{{ __('Email Campaigns') }}</td>
                            <td class="highlight-col"><span class="feature-check">✓</span></td>
                            <td><span class="feature-check">✓</span></td>
                            <td><span class="feature-none">—</span></td>
                            <td><span class="feature-none">—</span></td>
                        </tr>
                        <tr>
                            <td>{{ __('Financial Tracking') }}</td>
                            <td class="highlight-col"><span class="feature-check">✓</span></td>
                            <td><span class="feature-none">—</span></td>
                            <td><span class="feature-check">✓</span></td>
                            <td><span class="feature-none">—</span></td>
                        </tr>
                        <tr>
                            <td>{{ __('Client Portal') }}</td>
                            <td class="highlight-col"><span class="feature-check">✓</span></td>
                            <td><span class="feature-none">—</span></td>
                            <td><span class="feature-none">—</span></td>
                            <td><span class="feature-none">—</span></td>
                        </tr>
                        <tr>
                            <td>{{ __('AI-Powered Insights') }}</td>
                            <td class="highlight-col"><span class="feature-check">✓</span></td>
                            <td><span class="feature-none">—</span></td>
                            <td><span class="feature-none">—</span></td>
                            <td><span class="feature-none">—</span></td>
                        </tr>
                        <tr class="pricing-row">
                            <td>{{ __('Total Cost (All Features)') }}</td>
                            <td class="highlight-col"><span class="pricing-highlight">$29.99/mo</span></td>
                            <td><span class="pricing-competitor">$800+/mo</span></td>
                            <td><span class="pricing-competitor">$50-200/mo</span></td>
                            <td><span class="pricing-competitor">$15-50/mo</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div style="text-align: center; margin-top: 50px;">
                <a href="{{ route('register') }}" class="btn-primary">
                    {{ __('Start Your 14-Day Free Trial') }}
                    <svg width="20" height="20" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L12.586 11H5a1 1 0 110-2h7.586l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd"/>
                    </svg>
                </a>
            </div>
        </div>
    </section>

    {{-- TESTIMONIALS --}}
    <section class="testimonials-section">
        <div class="section-header">
            <span class="section-label">{{ __('Social Proof') }}</span>
            <h2 class="section-title">{{ __('Join 500+ Businesses Already Saving') }}</h2>
            <p class="section-subtitle">{{ __('See how entrepreneurs simplified their business and saved thousands with WhizzIQ') }}</p>
        </div>

        <div class="testimonials-grid">
            <div class="testimonial-card">
                <div class="testimonial-header">
                    <div class="testimonial-avatar" style="background: #FFE5E5; color: var(--color-accent);">SM</div>
                    <div class="testimonial-info">
                        <h4>Sarah Mitchell</h4>
                        <p>Marketing Consultant, Mitchell Digital</p>
                    </div>
                </div>
                <div class="testimonial-content">
                    "{{ __('I was paying $850/month for HubSpot, Calendly, and Asana. WhizzIQ replaced all three for $30/month. I\'ve saved $9,600 in my first year and cut my admin time by 60%. Now I spend those hours serving clients instead of managing software.') }}"
                </div>
                <div class="testimonial-rating">★★★★★</div>
            </div>

            <div class="testimonial-card">
                <div class="testimonial-header">
                    <div class="testimonial-avatar" style="background: #E5F4FF; color: var(--color-blue);">JD</div>
                    <div class="testimonial-info">
                        <h4>James Davidson</h4>
                        <p>Freelance Designer, JD Creative Studio</p>
                    </div>
                </div>
                <div class="testimonial-content">
                    "{{ __('Setup took 20 minutes. I imported 200+ contacts from HubSpot, connected my Google Calendar, and sent my first invoice the same day. My clients love the automated booking system. This saved me 10+ hours per week in the first month alone.') }}"
                </div>
                <div class="testimonial-rating">★★★★★</div>
            </div>

            <div class="testimonial-card">
                <div class="testimonial-header">
                    <div class="testimonial-avatar" style="background: #E5FFE5; color: var(--color-green);">LK</div>
                    <div class="testimonial-info">
                        <h4>Lisa Kim</h4>
                        <p>Business Coach, Momentum Coaching</p>
                    </div>
                </div>
                <div class="testimonial-content">
                    "{{ __('After trying every business tool on the market, WhizzIQ is the only one that actually delivers on "all-in-one." The CRM is better than HubSpot for small businesses, invoicing is cleaner than QuickBooks, and I save 5-7 hours every week. My revenue grew 30% in 3 months just from the time I got back.') }}"
                </div>
                <div class="testimonial-rating">★★★★★</div>
            </div>
        </div>
    </section>

    {{-- PRICING --}}
    <section class="pricing-section" id="pricing">
        <div class="section-header">
            <span class="section-label">{{ __('Pricing') }}</span>
            <h2 class="section-title">{{ __('Simple, Transparent Pricing') }}</h2>
            <p class="section-subtitle">{{ __('One platform. One price. Everything included. No hidden fees. 30-day money-back guarantee.') }}</p>
        </div>

        <div class="pricing-cards">
            <x-plans.all calculate-saving-rates="true" show-default-product="1"/>
            <x-products.all />
        </div>

        <div style="text-align: center; margin-top: 30px; padding: 20px; background: white; border: 3px solid var(--color-dark); border-radius: 20px; max-width: 800px; margin-left: auto; margin-right: auto;">
            <p style="font-size: 16px; color: #666; margin-bottom: 15px;">
                <strong style="color: var(--color-dark);">✓ 30-day money-back guarantee</strong> — Try risk-free<br>
                <strong style="color: var(--color-dark);">✓ Cancel anytime</strong> — No questions asked<br>
                <strong style="color: var(--color-dark);">✓ Join 500+ businesses</strong> already saving thousands
            </p>
        </div>

        <div style="text-align: center; margin-top: 50px;">
            <a href="{{ route('register') }}" class="btn-primary">
                {{ __('Start Your 14-Day Free Trial') }}
                <svg width="20" height="20" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L12.586 11H5a1 1 0 110-2h7.586l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd"/>
                </svg>
            </a>
        </div>
    </section>

    {{-- FAQ --}}
    <section class="faq-section" id="faq">
        <div class="faq-container">
            <div class="section-header">
                <span class="section-label">{{ __('FAQ') }}</span>
                <h2 class="section-title">{{ __('Common Questions') }}</h2>
                <p class="section-subtitle">{{ __('Everything you need to know about WhizzIQ') }}</p>
            </div>

            <x-accordion class="mt-4">
                <x-accordion.item active="true" name="faqs">
                    <x-slot name="title">{{ __('What exactly is WhizzIQ?') }}</x-slot>
                    <p>{{ __('WhizzIQ is an all-in-one business operating system that replaces 10+ expensive tools. You get CRM, sales pipeline, invoicing, appointment booking, task management, marketing automation, financial tracking, and more—all perfectly integrated in one platform.') }}</p>
                </x-accordion.item>

                <x-accordion.item active="false" name="faqs">
                    <x-slot name="title">{{ __('How much will I really save?') }}</x-slot>
                    <p>{{ __('Most business owners pay $500-1,200/month for tools like HubSpot ($800+), QuickBooks ($50-200), Calendly ($15-50), Asana ($10-25), and Mailchimp ($20-300). WhizzIQ replaces all of them for $29.99-49.99/month—saving you $6,000-14,000 per year.') }}</p>
                </x-accordion.item>

                <x-accordion.item active="false" name="faqs">
                    <x-slot name="title">{{ __('Can I import my data from other tools?') }}</x-slot>
                    <p>{{ __('Yes! Import contacts from HubSpot, Salesforce, or any CSV file. Sync calendars from Google, Outlook, or Apple. Import financial data from QuickBooks. We have migration guides for each platform.') }}</p>
                </x-accordion.item>

                <x-accordion.item active="false" name="faqs">
                    <x-slot name="title">{{ __('How long does setup take?') }}</x-slot>
                    <p>{{ __('Most users are fully set up in 15-20 minutes. Import contacts via CSV, connect your calendar (Google/Outlook/Apple), link Stripe for payments, and you\'re done. We provide step-by-step guides for everything.') }}</p>
                </x-accordion.item>

                <x-accordion.item active="false" name="faqs">
                    <x-slot name="title">{{ __('What if I don\'t like it?') }}</x-slot>
                    <p>{{ __('Cancel anytime with one click. No phone calls, no retention tactics, no questions asked. Your data stays accessible for 90 days after cancellation so you can export everything. After 90 days, we permanently delete your data per GDPR.') }}</p>
                </x-accordion.item>

                <x-accordion.item active="false" name="faqs">
                    <x-slot name="title">{{ __('Is my data secure?') }}</x-slot>
                    <p>{{ __('Absolutely. All data is encrypted with bank-level 256-bit SSL. We never sell your data to third parties. You can export or delete everything anytime. We\'re fully GDPR and SOC 2 compliant.') }}</p>
                </x-accordion.item>
            </x-accordion>
        </div>
    </section>

</x-layouts.app>