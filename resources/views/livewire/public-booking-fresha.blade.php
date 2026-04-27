<div class="min-h-screen bg-gray-50">
    {{-- Hero Section with Business Info (Fresha Style) --}}
    <div class="bg-white border-b border-gray-200">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex flex-col md:flex-row gap-6">
                {{-- Business Image --}}
                @if($bookingSetting->logo_url)
                    <div class="flex-shrink-0">
                        <img src="{{ $bookingSetting->logo_url }}" alt="{{ $bookingSetting->display_name }}" class="w-20 h-20 sm:w-28 sm:h-28 rounded-2xl object-cover">
                    </div>
                @endif

                {{-- Business Details --}}
                <div class="flex-1 min-w-0">
                    <h1 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-3">
                        {{ $bookingSetting->display_name }}
                    </h1>

                    {{-- Rating --}}
                    @if($bookingSetting->review_count > 0)
                        <div class="flex items-center gap-2 mb-4">
                            <div class="flex items-center">
                                @for($i = 1; $i <= 5; $i++)
                                    <svg class="w-5 h-5 {{ $i <= round($bookingSetting->average_rating) ? 'text-yellow-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                @endfor
                            </div>
                            <span class="text-base font-semibold text-gray-900">{{ number_format($bookingSetting->average_rating, 1) }}</span>
                            <span class="text-sm text-gray-600">({{ $bookingSetting->review_count }} reviews)</span>
                        </div>
                    @endif

                    @if($bookingSetting->welcome_message)
                        <p class="text-gray-600 text-base leading-relaxed">{{ $bookingSetting->welcome_message }}</p>
                    @endif
                </div>

                {{-- Share Button --}}
                <div class="flex-shrink-0">
                    <button onclick="navigator.share({url: window.location.href, title: '{{ $bookingSetting->display_name }}'}).catch(() => {})" class="inline-flex items-center gap-2 px-5 py-2.5 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors text-sm font-medium text-gray-700">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/>
                        </svg>
                        Share
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Tab Navigation (Fresha Style) --}}
    @if($currentStep === 1 && !$confirmed)
        <div class="bg-white border-b border-gray-200 sticky top-0 z-10">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                <nav class="flex gap-8">
                    <button
                        wire:click="switchTab('services')"
                        class="py-4 text-sm font-medium border-b-2 transition-colors {{ $activeTab === 'services' ? 'border-black text-black' : 'border-transparent text-gray-500 hover:text-gray-700' }}"
                    >
                        Services
                    </button>
                    <button
                        wire:click="switchTab('about')"
                        class="py-4 text-sm font-medium border-b-2 transition-colors {{ $activeTab === 'about' ? 'border-black text-black' : 'border-transparent text-gray-500 hover:text-gray-700' }}"
                    >
                        About
                    </button>
                    <button
                        wire:click="switchTab('reviews')"
                        class="py-4 text-sm font-medium border-b-2 transition-colors {{ $activeTab === 'reviews' ? 'border-black text-black' : 'border-transparent text-gray-500 hover:text-gray-700' }}"
                    >
                        Reviews
                        @if($bookingSetting->review_count > 0)
                            <span class="ml-1 text-gray-400">({{ $bookingSetting->review_count }})</span>
                        @endif
                    </button>
                </nav>
            </div>
        </div>
    @endif

    {{-- Main Content --}}
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @if($currentStep === 1)
            @if($activeTab === 'services')
                {{-- Services List (Fresha Style) --}}
                <div class="bg-white rounded-lg border border-gray-200">
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-2xl font-bold text-gray-900">Services</h2>
                        <p class="text-gray-600 mt-1">{{ $appointmentTypes->count() }} {{ Str::plural('service', $appointmentTypes->count()) }} available</p>
                    </div>

                    <div class="divide-y divide-gray-200">
                        @forelse($appointmentTypes as $type)
                            <div class="p-6 hover:bg-gray-50 transition-colors cursor-pointer" wire:click="selectType({{ $type->id }})">
                                <div class="flex gap-4">
                                    {{-- Service Image --}}
                                    @if($type->image_url)
                                        <div class="flex-shrink-0">
                                            <img src="{{ Storage::url($type->image_url) }}" alt="{{ $type->name }}" class="w-20 h-20 rounded-lg object-cover">
                                        </div>
                                    @else
                                        <div class="flex-shrink-0 w-20 h-20 rounded-lg flex items-center justify-center" style="background: linear-gradient(135deg, {{ $type->color }}15 0%, {{ $type->color }}30 100%)">
                                            <svg class="w-10 h-10 opacity-30" style="color: {{ $type->color }}" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                                            </svg>
                                        </div>
                                    @endif

                                    {{-- Service Details --}}
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-start justify-between gap-4 mb-2">
                                            <h3 class="text-lg font-semibold text-gray-900">{{ $type->name }}</h3>
                                            <div class="flex-shrink-0 text-right">
                                                @if($type->price > 0)
                                                    <div class="text-lg font-bold text-gray-900">${{ number_format($type->price, 2) }}</div>
                                                @else
                                                    <div class="text-lg font-bold text-green-600">Free</div>
                                                @endif
                                            </div>
                                        </div>

                                        {{-- Rating for service --}}
                                        @if($type->review_count > 0)
                                            <div class="flex items-center gap-1.5 mb-2">
                                                @php
                                                    $avgRating = round($type->approved_reviews_avg_rating ?? 0);
                                                @endphp
                                                @for($i = 1; $i <= 5; $i++)
                                                    <svg class="w-4 h-4 {{ $i <= $avgRating ? 'text-yellow-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20">
                                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                                    </svg>
                                                @endfor
                                                <span class="text-sm text-gray-600 ml-0.5">({{ $type->review_count }})</span>
                                            </div>
                                        @endif

                                        @if($type->description)
                                            <p class="text-gray-600 text-sm mb-3 line-clamp-2">{{ $type->description }}</p>
                                        @endif

                                        <div class="flex items-center gap-4 text-sm text-gray-500">
                                            <span class="flex items-center gap-1.5">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                                {{ $type->duration_minutes }} min
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="p-12 text-center text-gray-500">
                                <p class="text-lg font-medium">No services available</p>
                            </div>
                        @endforelse
                    </div>
                </div>

            @elseif($activeTab === 'about')
                {{-- About Tab --}}
                <div class="bg-white rounded-lg border border-gray-200 p-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">About</h2>
                    @if($bookingSetting->welcome_message)
                        <p class="text-gray-600 text-base leading-relaxed">{{ $bookingSetting->welcome_message }}</p>
                    @else
                        <p class="text-gray-500">No description available.</p>
                    @endif
                </div>

            @elseif($activeTab === 'reviews')
                {{-- Reviews Tab (Fresha Style) --}}
                <div class="bg-white rounded-lg border border-gray-200">
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-2xl font-bold text-gray-900 mb-3">Reviews</h2>
                        @if($bookingSetting->review_count > 0)
                            <div class="flex items-center gap-3">
                                <div class="flex items-center">
                                    @for($i = 1; $i <= 5; $i++)
                                        <svg class="w-6 h-6 {{ $i <= round($bookingSetting->average_rating) ? 'text-yellow-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                        </svg>
                                    @endfor
                                </div>
                                <span class="text-xl font-semibold text-gray-900">{{ number_format($bookingSetting->average_rating, 1) }}</span>
                                <span class="text-gray-600">({{ $bookingSetting->review_count }} reviews)</span>
                            </div>
                        @endif
                    </div>

                    <div class="divide-y divide-gray-200">
                        @forelse($reviews as $review)
                            <div class="p-6">
                                <div class="flex items-start gap-4">
                                    <div class="flex-shrink-0 w-12 h-12 rounded-full bg-gray-200 flex items-center justify-center font-semibold text-gray-700 text-lg">
                                        {{ substr($review->display_name, 0, 1) }}
                                    </div>
                                    <div class="flex-1">
                                        <div class="flex items-center justify-between mb-2">
                                            <div>
                                                <div class="font-semibold text-gray-900">{{ $review->display_name }}</div>
                                                <div class="text-sm text-gray-500">{{ $review->created_at->format('F j, Y') }}</div>
                                            </div>
                                            <div class="flex items-center">
                                                @for($i = 1; $i <= 5; $i++)
                                                    <svg class="w-4 h-4 {{ $i <= $review->rating ? 'text-yellow-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20">
                                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                                    </svg>
                                                @endfor
                                            </div>
                                        </div>
                                        @if($review->appointmentType)
                                            <div class="text-sm text-gray-500 mb-2">{{ $review->appointmentType->name }}</div>
                                        @endif
                                        @if($review->comment)
                                            <p class="text-gray-700 text-base">{{ $review->comment }}</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="p-12 text-center text-gray-500">
                                <p class="text-lg font-medium">No reviews yet</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            @endif
        @endif
    </div>
</div>
