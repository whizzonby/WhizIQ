<div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 py-8 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto">
        {{-- Header --}}
        <div class="text-center mb-6">
            @if($bookingSetting->logo_url)
                <img src="{{ $bookingSetting->logo_url }}" alt="Logo" class="h-16 mx-auto mb-4">
            @endif
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-2" style="color: {{ $bookingSetting->brand_color ?? '#3B82F6' }}">
                {{ $bookingSetting->display_name }}
            </h1>
        </div>

        {{-- Back Button --}}
        <div class="mb-6">
            <a href="{{ route('booking.show', ['slug' => $slug]) }}" class="inline-flex items-center gap-2 text-gray-600 hover:text-gray-900 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                <span class="font-medium">Back to Services</span>
            </a>
        </div>

        {{-- Main Content Grid --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            {{-- Left Column: Gallery and Description --}}
            <div class="lg:col-span-2 space-y-6">
                {{-- Gallery Section --}}
                <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
                    {{-- Main Cover Image --}}
                    @if($appointmentType->image_url)
                        <div class="relative h-96 bg-gradient-to-br from-gray-100 to-gray-200">
                            <img src="{{ Storage::url($appointmentType->image_url) }}" alt="{{ $appointmentType->name }}" class="w-full h-full object-cover">
                        </div>
                    @else
                        <div class="relative h-96 bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center">
                            <div class="text-center">
                                <svg class="w-24 h-24 mx-auto opacity-30" style="color: {{ $appointmentType->color }}" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                                </svg>
                            </div>
                        </div>
                    @endif

                    {{-- Gallery Grid --}}
                    @if($appointmentType->gallery_images && count($appointmentType->gallery_images) > 0)
                        <div class="p-4 grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                            @foreach($appointmentType->gallery_images as $image)
                                <div class="relative aspect-square rounded-lg overflow-hidden bg-gray-200 cursor-pointer hover:opacity-75 transition-opacity">
                                    <img src="{{ Storage::url($image) }}" alt="Gallery image" class="w-full h-full object-cover">
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Service Details Card --}}
                <div class="bg-white rounded-2xl shadow-sm p-6 sm:p-8">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-3 h-3 rounded-full" style="background-color: {{ $appointmentType->color }}"></div>
                        <h2 class="text-3xl font-bold text-gray-900">{{ $appointmentType->name }}</h2>
                    </div>

                    @if($appointmentType->description)
                        <div class="prose max-w-none text-gray-600 leading-relaxed">
                            <p class="text-lg">{{ $appointmentType->description }}</p>
                        </div>
                    @endif

                    {{-- Service Features --}}
                    <div class="mt-8 grid grid-cols-1 sm:grid-cols-2 gap-4">
                        {{-- Duration --}}
                        <div class="flex items-start gap-3 p-4 bg-gray-50 rounded-lg">
                            <div class="flex-shrink-0 w-10 h-10 rounded-lg flex items-center justify-center" style="background-color: {{ $bookingSetting->brand_color ?? '#3B82F6' }}15">
                                <svg class="w-5 h-5" style="color: {{ $bookingSetting->brand_color ?? '#3B82F6' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div>
                                <div class="font-semibold text-gray-900">Duration</div>
                                <div class="text-sm text-gray-600">{{ $appointmentType->duration_minutes }} minutes</div>
                            </div>
                        </div>

                        {{-- Format --}}
                        <div class="flex items-start gap-3 p-4 bg-gray-50 rounded-lg">
                            <div class="flex-shrink-0 w-10 h-10 rounded-lg flex items-center justify-center" style="background-color: {{ $bookingSetting->brand_color ?? '#3B82F6' }}15">
                                <svg class="w-5 h-5" style="color: {{ $bookingSetting->brand_color ?? '#3B82F6' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    @if($appointmentType->appointment_format === 'online')
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                    @elseif($appointmentType->appointment_format === 'phone')
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                    @else
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    @endif
                                </svg>
                            </div>
                            <div>
                                <div class="font-semibold text-gray-900">Format</div>
                                <div class="text-sm text-gray-600">
                                    {{ match($appointmentType->appointment_format) {
                                        'online' => 'Online (Video/Meeting)',
                                        'in_person' => 'In-Person',
                                        'hybrid' => 'Hybrid',
                                        'phone' => 'Phone Call',
                                        default => 'Online'
                                    } }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right Column: Sticky Booking Card --}}
            <div class="lg:col-span-1">
                <div class="sticky top-8 bg-white rounded-2xl shadow-lg p-6 border-2" style="border-color: {{ $bookingSetting->brand_color ?? '#3B82F6' }}">
                    {{-- Price --}}
                    <div class="text-center mb-6 pb-6 border-b border-gray-200">
                        @if($appointmentType->price > 0)
                            <div class="text-4xl font-bold mb-1" style="color: {{ $bookingSetting->brand_color ?? '#3B82F6' }}">
                                ${{ number_format($appointmentType->price, 2) }}
                            </div>
                            <div class="text-sm text-gray-600">per session</div>
                        @else
                            <div class="text-4xl font-bold mb-1 text-green-600">Free</div>
                            <div class="text-sm text-gray-600">No charge</div>
                        @endif
                    </div>

                    {{-- Quick Info --}}
                    <div class="space-y-3 mb-6">
                        <div class="flex items-center gap-3 text-sm text-gray-700">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span>{{ $appointmentType->duration_minutes }} minutes</span>
                        </div>
                        <div class="flex items-center gap-3 text-sm text-gray-700">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span>Instant Confirmation</span>
                        </div>
                    </div>

                    {{-- Book Now Button --}}
                    <a
                        href="{{ route('booking.show', ['slug' => $slug, 'service' => $serviceId]) }}"
                        class="block w-full py-4 rounded-xl font-semibold text-white shadow-lg hover:shadow-xl transition-all transform hover:scale-105 text-center"
                        style="background-color: {{ $bookingSetting->brand_color ?? '#3B82F6' }}"
                    >
                        Book Now
                    </a>

                    {{-- Additional Info --}}
                    <div class="mt-6 pt-6 border-t border-gray-200 text-center">
                        <p class="text-xs text-gray-500">
                            By booking, you agree to our terms and conditions
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
