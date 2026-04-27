<div class="min-h-screen bg-white">
    {{-- EXACT FRESHA LAYOUT --}}

    {{-- Hero Cover Image Section (Fresha has a large cover photo) --}}
    <div class="relative w-full h-64 md:h-80 bg-gray-200">
        @if($bookingSetting->logo_url)
            <img src="{{ $bookingSetting->logo_url }}" alt="{{ $bookingSetting->display_name }}" class="w-full h-full object-cover">
        @else
            <div class="w-full h-full bg-gradient-to-br from-gray-100 to-gray-300"></div>
        @endif

        {{-- Share Button (Top Right like Fresha) --}}
        <button onclick="navigator.share({url: window.location.href, title: '{{ $bookingSetting->display_name }}'}).catch(() => {})"
                class="absolute top-4 right-4 bg-white rounded-full p-3 shadow-lg hover:bg-gray-50 transition-colors">
            <svg class="w-5 h-5 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/>
            </svg>
        </button>
    </div>

    {{-- Business Info Header (Below cover image, EXACT Fresha style) --}}
    <div class="max-w-6xl mx-auto px-4 sm:px-6">
        <div class="py-6 border-b border-gray-200">
            {{-- Business Name --}}
            <h1 class="text-3xl md:text-4xl font-bold text-gray-900 mb-3">
                {{ $bookingSetting->display_name }}
            </h1>

            {{-- Rating + Status + Location (Exact Fresha layout) --}}
            <div class="flex flex-wrap items-center gap-3 text-sm mb-4">
                {{-- Rating --}}
                @if($bookingSetting->review_count > 0)
                    <div class="flex items-center gap-1.5">
                        <div class="flex items-center">
                            @for($i = 1; $i <= 5; $i++)
                                <svg class="w-4 h-4 {{ $i <= round($bookingSetting->average_rating) ? 'text-yellow-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                            @endfor
                        </div>
                        <span class="font-semibold text-gray-900">{{ number_format($bookingSetting->average_rating, 1) }}</span>
                        <span class="text-gray-600">({{ number_format($bookingSetting->review_count) }})</span>
                    </div>
                    <span class="text-gray-300">•</span>
                @endif

                {{-- Status: "Closed - opens at 9:00 am" --}}
                @php
                    $status = $bookingSetting->current_status ?? ['message' => 'Available'];
                @endphp
                <span class="text-gray-700">{{ $status['message'] }}</span>
            </div>

            {{-- Get Directions Button (Fresha has this) --}}
            <div class="flex gap-3">
                <button class="inline-flex items-center gap-2 px-4 py-2.5 bg-gray-100 hover:bg-gray-200 rounded-lg text-sm font-medium text-gray-900 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    Get directions
                </button>
            </div>
        </div>
    </div>

    {{-- TAB NAVIGATION (Exact Fresha style - sticky tabs) --}}
    @if($currentStep === 1 && !$confirmed)
        <div class="sticky top-0 z-20 bg-white border-b border-gray-200 shadow-sm">
            <div class="max-w-6xl mx-auto px-4 sm:px-6">
                <nav class="flex gap-8 overflow-x-auto">
                    <button wire:click="switchTab('photos')" class="py-4 text-sm font-medium whitespace-nowrap border-b-2 transition-colors {{ $activeTab === 'photos' ? 'border-gray-900 text-gray-900' : 'border-transparent text-gray-600 hover:text-gray-900' }}">
                        Photos
                    </button>
                    <button wire:click="switchTab('services')" class="py-4 text-sm font-medium whitespace-nowrap border-b-2 transition-colors {{ $activeTab === 'services' ? 'border-gray-900 text-gray-900' : 'border-transparent text-gray-600 hover:text-gray-900' }}">
                        Services
                    </button>
                    <button wire:click="switchTab('reviews')" class="py-4 text-sm font-medium whitespace-nowrap border-b-2 transition-colors {{ $activeTab === 'reviews' ? 'border-gray-900 text-gray-900' : 'border-transparent text-gray-600 hover:text-gray-900' }}">
                        Reviews @if($bookingSetting->review_count > 0)<span class="text-gray-400">({{ $bookingSetting->review_count }})</span>@endif
                    </button>
                    <button wire:click="switchTab('about')" class="py-4 text-sm font-medium whitespace-nowrap border-b-2 transition-colors {{ $activeTab === 'about' ? 'border-gray-900 text-gray-900' : 'border-transparent text-gray-600 hover:text-gray-900' }}">
                        About
                    </button>
                </nav>
            </div>
        </div>
    @endif

    {{-- MAIN CONTENT AREA --}}
    <div class="max-w-6xl mx-auto px-4 sm:px-6 py-8">
        @if($currentStep === 1)
            {{-- PHOTOS TAB --}}
            @if($activeTab === 'photos')
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 mb-6">Photos</h2>

                    {{-- Photo Grid (Fresha style) --}}
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                        {{-- Placeholder photos - you'll replace with actual gallery --}}
                        @for($i = 1; $i <= 6; $i++)
                            <div class="aspect-square bg-gray-200 rounded-lg overflow-hidden">
                                @if($bookingSetting->logo_url)
                                    <img src="{{ $bookingSetting->logo_url }}" alt="Photo {{ $i }}" class="w-full h-full object-cover hover:scale-105 transition-transform duration-300 cursor-pointer">
                                @else
                                    <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-gray-100 to-gray-300">
                                        <span class="text-gray-400 text-sm">Photo {{ $i }}</span>
                                    </div>
                                @endif
                            </div>
                        @endfor
                    </div>
                </div>

            {{-- SERVICES TAB (EXACT Fresha layout) --}}
            @elseif($activeTab === 'services')
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 mb-6">Services</h2>

                    {{-- Services List (Fresha exact style) --}}
                    <div class="space-y-8">
                        {{-- You can group services by category here --}}
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">All Services</h3>
                            <div class="space-y-4">
                                @forelse($appointmentTypes as $type)
                                    <div class="flex gap-4 p-4 rounded-lg border border-gray-200 hover:border-gray-300 transition-colors cursor-pointer" wire:click="selectType({{ $type->id }})">
                                        {{-- Service Thumbnail --}}
                                        @if($type->image_url)
                                            <div class="flex-shrink-0">
                                                <img src="{{ Storage::url($type->image_url) }}" alt="{{ $type->name }}" class="w-20 h-20 rounded-lg object-cover">
                                            </div>
                                        @else
                                            <div class="flex-shrink-0 w-20 h-20 rounded-lg bg-gray-100 flex items-center justify-center">
                                                <svg class="w-8 h-8 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd"/>
                                                </svg>
                                            </div>
                                        @endif

                                        {{-- Service Info --}}
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-start justify-between mb-1">
                                                <h4 class="font-semibold text-gray-900">{{ $type->name }}</h4>
                                                <div class="ml-4 text-right flex-shrink-0">
                                                    @if($type->price > 0)
                                                        <div class="font-semibold text-gray-900">{{ $type->price > 0 ? '$' . number_format($type->price, 2) : '' }}</div>
                                                    @else
                                                        <div class="font-semibold text-green-600">Free</div>
                                                    @endif
                                                </div>
                                            </div>

                                            @if($type->description)
                                                <p class="text-sm text-gray-600 mb-2 line-clamp-2">{{ $type->description }}</p>
                                            @endif

                                            <div class="flex items-center gap-4 text-sm text-gray-500">
                                                <span>{{ $type->duration_minutes }} min</span>

                                                @if($type->review_count > 0)
                                                    <span>•</span>
                                                    <div class="flex items-center gap-1">
                                                        <svg class="w-4 h-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                                        </svg>
                                                        <span>{{ number_format($type->approved_reviews_avg_rating ?? 0, 1) }} ({{ $type->review_count }})</span>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-center py-12 text-gray-500">
                                        <p>No services available</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>

            {{-- REVIEWS TAB (Exact Fresha style) --}}
            @elseif($activeTab === 'reviews')
                <div>
                    {{-- Overall Rating Summary --}}
                    <div class="mb-8">
                        <h2 class="text-2xl font-bold text-gray-900 mb-4">Reviews</h2>
                        @if($bookingSetting->review_count > 0)
                            <div class="flex items-center gap-3">
                                <div class="flex items-center">
                                    @for($i = 1; $i <= 5; $i++)
                                        <svg class="w-6 h-6 {{ $i <= round($bookingSetting->average_rating) ? 'text-yellow-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                        </svg>
                                    @endfor
                                </div>
                                <span class="text-2xl font-bold text-gray-900">{{ number_format($bookingSetting->average_rating, 1) }}</span>
                                <span class="text-gray-600">({{ number_format($bookingSetting->review_count) }} reviews)</span>
                            </div>
                        @endif
                    </div>

                    {{-- Individual Reviews --}}
                    <div class="space-y-6">
                        @forelse($reviews as $review)
                            <div class="pb-6 border-b border-gray-200 last:border-0">
                                <div class="flex gap-4">
                                    {{-- Avatar --}}
                                    <div class="flex-shrink-0">
                                        <div class="w-12 h-12 rounded-full bg-gray-200 flex items-center justify-center font-semibold text-gray-700 text-lg">
                                            {{ substr($review->display_name, 0, 1) }}
                                        </div>
                                    </div>

                                    {{-- Review Content --}}
                                    <div class="flex-1">
                                        <div class="flex items-center justify-between mb-2">
                                            <div>
                                                <div class="font-semibold text-gray-900">{{ $review->display_name }}</div>
                                                <div class="text-sm text-gray-500">{{ $review->created_at->format('M j, Y') }}</div>
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
                                            <div class="text-sm text-gray-600 mb-2">{{ $review->appointmentType->name }}</div>
                                        @endif

                                        @if($review->comment)
                                            <p class="text-gray-700">{{ $review->comment }}</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-12 text-gray-500">
                                <p>No reviews yet</p>
                                <p class="text-sm mt-2">Be the first to leave a review!</p>
                            </div>
                        @endforelse
                    </div>
                </div>

            {{-- ABOUT TAB --}}
            @elseif($activeTab === 'about')
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 mb-6">About</h2>

                    {{-- Description --}}
                    @if($bookingSetting->welcome_message)
                        <div class="mb-8">
                            <p class="text-gray-700 leading-relaxed">{{ $bookingSetting->welcome_message }}</p>
                        </div>
                    @endif

                    {{-- Opening Hours --}}
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Opening times</h3>
                        <div class="space-y-3">
                            @php
                                $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
                            @endphp
                            @foreach($days as $dayIndex => $dayName)
                                @php
                                    $daySchedule = $availabilitySchedule->get($dayIndex);
                                @endphp
                                <div class="flex items-center justify-between py-2">
                                    <span class="font-medium text-gray-900">{{ $dayName }}</span>
                                    @if($daySchedule && $daySchedule->isNotEmpty())
                                        <span class="text-gray-600">
                                            {{ \Carbon\Carbon::parse($daySchedule->first()->start_time)->format('g:i A') }} - {{ \Carbon\Carbon::parse($daySchedule->first()->end_time)->format('g:i A') }}
                                        </span>
                                    @else
                                        <span class="text-gray-400">Closed</span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Additional Info --}}
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Additional information</h3>
                        <div class="space-y-2 text-gray-700">
                            <div class="flex items-center gap-2">
                                <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <span>Instant confirmation</span>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

        {{-- Keep your existing booking flow (Step 2, Step 3, Confirmation) --}}
        @elseif($currentStep === 2)
            {{-- Your existing Step 2 code --}}
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <h2 class="text-2xl font-bold mb-4">Select Date & Time</h2>
                <p class="text-gray-600">Continue with your booking...</p>
            </div>
        @endif
    </div>
</div>
