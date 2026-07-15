<div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 py-8 px-4 sm:px-6 lg:px-8" wire:loading.class="opacity-50">
    <style>
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #cbd5e0;
            border-radius: 10px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #a0aec0;
        }
    </style>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('bookingCalendar', (availableDates, color, initialSelected) => {
                const availableSet = new Set(availableDates);
                const todayObj = new Date();
                todayObj.setHours(0, 0, 0, 0);

                function toDateStr(y, m, d) {
                    return y + '-' + String(m + 1).padStart(2, '0') + '-' + String(d).padStart(2, '0');
                }

                const todayStr = toDateStr(todayObj.getFullYear(), todayObj.getMonth(), todayObj.getDate());

                let initYear = todayObj.getFullYear();
                let initMonth = todayObj.getMonth();
                if (availableDates.length > 0) {
                    const parts = availableDates[0].split('-');
                    const firstDate = new Date(+parts[0], +parts[1] - 1, +parts[2]);
                    if (firstDate > todayObj) { initYear = firstDate.getFullYear(); initMonth = firstDate.getMonth(); }
                }

                let maxYear = initYear, maxMonth = initMonth;
                if (availableDates.length > 0) {
                    const lp = availableDates[availableDates.length - 1].split('-');
                    maxYear = +lp[0]; maxMonth = +lp[1] - 1;
                }

                return {
                    year: initYear,
                    month: initMonth,
                    color: color,
                    selectedDate: initialSelected || null,

                    get monthLabel() {
                        return new Date(this.year, this.month, 1)
                            .toLocaleDateString('en-US', { month: 'long', year: 'numeric' });
                    },

                    get canPrev() {
                        return !(this.year === todayObj.getFullYear() && this.month === todayObj.getMonth());
                    },

                    get canNext() {
                        return this.year < maxYear || (this.year === maxYear && this.month < maxMonth);
                    },

                    prevMonth() {
                        if (!this.canPrev) return;
                        if (this.month === 0) { this.year--; this.month = 11; } else this.month--;
                    },

                    nextMonth() {
                        if (!this.canNext) return;
                        if (this.month === 11) { this.year++; this.month = 0; } else this.month++;
                    },

                    get calendarDays() {
                        const days = [];
                        const firstDay = new Date(this.year, this.month, 1).getDay();
                        const daysInMonth = new Date(this.year, this.month + 1, 0).getDate();
                        for (let i = 0; i < firstDay; i++) days.push({ empty: true });
                        for (let d = 1; d <= daysInMonth; d++) {
                            const ds = toDateStr(this.year, this.month, d);
                            const isPast = new Date(this.year, this.month, d) < todayObj;
                            const isAvail = availableSet.has(ds);
                            days.push({
                                empty: false,
                                num: d,
                                date: ds,
                                available: isAvail,
                                past: isPast,
                                selected: this.selectedDate === ds,
                                isToday: ds === todayStr,
                                clickable: isAvail && !isPast,
                            });
                        }
                        return days;
                    },

                };
            });
        });
    </script>

    <div class="max-w-7xl mx-auto">
        {{-- Header --}}
        <div class="text-center mb-6">
            @if($bookingSetting->logo_url)
                <img src="{{ $bookingSetting->logo_url }}" alt="Logo" class="h-16 mx-auto mb-4">
            @endif
            <h1 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-2" style="color: {{ $bookingSetting->brand_color ?? '#3B82F6' }}">
                {{ $bookingSetting->display_name }}
            </h1>
            @if($bookingSetting->welcome_message)
                <p class="mt-2 text-gray-600 text-base sm:text-lg max-w-2xl mx-auto">{{ $bookingSetting->welcome_message }}</p>
            @endif

            @if($bookingSetting->review_count > 0)
                <div class="mt-4 flex items-center justify-center gap-2 text-sm text-gray-700">
                    <span class="font-semibold">{{ number_format($bookingSetting->average_rating, 1) }}</span>
                    <span class="tracking-wide text-yellow-500">{{ str_repeat('*', (int) round($bookingSetting->average_rating)) }}</span>
                    <span>from {{ $bookingSetting->review_count }} {{ Str::plural('review', $bookingSetting->review_count) }}</span>
                </div>
            @endif

            {{-- Location Display --}}
            @if($bookingSetting->business_address || $bookingSetting->business_city || $bookingSetting->business_country)
                <div class="mt-3 flex items-center justify-center gap-2 text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <span class="text-sm">
                        @if($bookingSetting->business_address){{ $bookingSetting->business_address }}@endif
                        @if($bookingSetting->business_address && ($bookingSetting->business_city || $bookingSetting->business_country)), @endif
                        @if($bookingSetting->business_city){{ $bookingSetting->business_city }}@endif
                        @if($bookingSetting->business_city && $bookingSetting->business_country), @endif
                        @if($bookingSetting->business_country){{ $bookingSetting->business_country }}@endif
                    </span>
                </div>
            @endif
        </div>

        {{-- Progress Steps (only show after step 1) --}}
        @if(!$confirmed && $currentStep > 1)
            <div class="mb-6">
                <div class="bg-white rounded-xl shadow-sm p-4">
                    <div class="flex items-center justify-between max-w-2xl mx-auto">
                        @php
                            $steps = ['Select Service', 'Choose Time', 'Your Info'];
                        @endphp
                        @foreach($steps as $index => $label)
                            <div class="flex items-center {{ $index < count($steps) - 1 ? 'flex-1' : '' }}">
                                <div class="flex items-center">
                                    <div class="
                                        flex items-center justify-center w-8 h-8 rounded-full font-semibold text-sm transition-all
                                        {{ $currentStep > ($index + 1) ? 'bg-green-500 text-white' : ($currentStep === ($index + 1) ? 'text-white shadow-lg scale-110' : 'bg-gray-200 text-gray-600') }}
                                    " style="{{ $currentStep === ($index + 1) ? 'background-color: ' . ($bookingSetting->brand_color ?? '#3B82F6') : '' }}">
                                        @if($currentStep > ($index + 1))
                                            ✓
                                        @else
                                            {{ $index + 1 }}
                                        @endif
                                    </div>
                                    <span class="ml-2 text-xs sm:text-sm font-medium {{ $currentStep === ($index + 1) ? 'text-gray-900' : 'text-gray-500' }} hidden sm:inline">
                                        {{ $label }}
                                    </span>
                                </div>
                                @if($index < count($steps) - 1)
                                    <div class="flex-1 h-0.5 mx-2 sm:mx-4 transition-all {{ $currentStep > ($index + 1) ? 'bg-green-500' : 'bg-gray-200' }}"></div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        {{-- Main Content Card --}}
        <div class="bg-white rounded-xl shadow-sm mb-6 overflow-hidden">
            @if(session()->has('error'))
                <div class="m-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                    {{ session('error') }}
                </div>
            @endif

            <div class="p-6 sm:p-8">
                @if($confirmed)
                    {{-- Step 4: Confirmation --}}
                    <div class="text-center py-12 max-w-2xl mx-auto">
                        {{-- Success Animation --}}
                        <div class="mx-auto flex items-center justify-center h-20 w-20 rounded-full mb-6 animate-bounce" style="background-color: {{ $bookingSetting->brand_color ?? '#3B82F6' }}15">
                            <svg class="h-12 w-12" style="color: {{ $bookingSetting->brand_color ?? '#3B82F6' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>

                        <h2 class="text-3xl font-bold text-gray-900 mb-3">
                            @if($bookingSetting->require_approval)
                                Request Submitted!
                            @else
                                You're All Set!
                            @endif
                        </h2>

                        <p class="text-gray-600 text-lg mb-8 max-w-lg mx-auto">
                            @if($bookingSetting->require_approval)
                                Your booking request has been submitted and is pending approval. You'll receive a confirmation email once it's approved.
                            @else
                                Your appointment has been confirmed! A confirmation email with all details will be sent to <strong>{{ $attendeeEmail }}</strong> within the next few minutes.
                            @endif
                        </p>

                        {{-- Appointment Details Card --}}
                        <div class="bg-gradient-to-br from-gray-50 to-gray-100 rounded-xl p-8 text-left shadow-inner border border-gray-200">
                            <div class="flex items-center gap-3 mb-6">
                                <div class="w-10 h-10 rounded-lg flex items-center justify-center" style="background-color: {{ $bookingSetting->brand_color ?? '#3B82F6' }}">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                                <h3 class="text-xl font-bold text-gray-900">Appointment Details</h3>
                            </div>

                            <dl class="space-y-4">
                                <div class="flex items-start gap-4">
                                    <dt class="text-gray-500 font-medium min-w-[100px]">Service</dt>
                                    <dd class="text-gray-900 font-semibold flex-1">{{ $selectedType->name }}</dd>
                                </div>
                                <div class="h-px bg-gray-300"></div>
                                <div class="flex items-start gap-4">
                                    <dt class="text-gray-500 font-medium min-w-[100px]">Date</dt>
                                    <dd class="text-gray-900 font-semibold flex-1">
                                        {{ \Carbon\Carbon::parse($selectedDate)->format('l, F j, Y') }}
                                    </dd>
                                </div>
                                <div class="h-px bg-gray-300"></div>
                                <div class="flex items-start gap-4">
                                    <dt class="text-gray-500 font-medium min-w-[100px]">Time</dt>
                                    <dd class="text-gray-900 font-semibold flex-1">
                                        {{ \Carbon\Carbon::createFromFormat('H:i', $selectedTime)->format('g:i A') }}
                                    </dd>
                                </div>
                                <div class="h-px bg-gray-300"></div>
                                <div class="flex items-start gap-4">
                                    <dt class="text-gray-500 font-medium min-w-[100px]">Duration</dt>
                                    <dd class="text-gray-900 font-semibold flex-1">{{ $selectedType->duration_minutes }} minutes</dd>
                                </div>
                                <div class="h-px bg-gray-300"></div>
                                <div class="flex items-start gap-4">
                                    <dt class="text-gray-500 font-medium min-w-[100px]">Attendee</dt>
                                    <dd class="text-gray-900 font-semibold flex-1">
                                        {{ $attendeeName }}<br>
                                        <span class="text-sm font-normal text-gray-600">{{ $attendeeEmail }}</span>
                                    </dd>
                                </div>

                                @if($createdAppointment && ($createdAppointment->venue || $createdAppointment->location))
                                    <div class="h-px bg-gray-300"></div>
                                    <div class="flex items-start gap-4">
                                        <dt class="text-gray-500 font-medium min-w-[100px]">Location</dt>
                                        <dd class="text-gray-900 flex-1">
                                            @if($createdAppointment->venue)
                                                @php
                                                    $venue = $createdAppointment->venue;
                                                @endphp
                                                <div class="font-semibold mb-1">{{ $venue->name }}</div>
                                                @if($venue->full_address)
                                                    <div class="text-sm text-gray-600 mb-2">{{ $venue->full_address }}</div>
                                                @endif
                                                @if($createdAppointment->room_name)
                                                    <div class="text-sm text-gray-600 mb-2">
                                                        <span class="font-medium">Room:</span> {{ $createdAppointment->room_name }}
                                                    </div>
                                                @endif
                                                @if($venue->google_maps_url)
                                                    <a href="{{ $venue->google_maps_url }}" target="_blank"
                                                       class="inline-flex items-center gap-1 text-sm text-blue-600 hover:text-blue-700 font-medium mt-1">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                                        </svg>
                                                        View on Map
                                                    </a>
                                                @endif
                                            @elseif($createdAppointment->location)
                                                <div class="flex items-start gap-2">
                                                    <svg class="w-5 h-5 text-gray-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                    </svg>
                                                    <div class="font-semibold">{{ $createdAppointment->location }}</div>
                                                </div>
                                            @endif
                                        </dd>
                                    </div>
                                @endif

                                @if($createdAppointment && $createdAppointment->meeting_url)
                                    <div class="h-px bg-gray-300"></div>
                                    <div class="flex items-start gap-4">
                                        <dt class="text-gray-500 font-medium min-w-[100px]">Meeting</dt>
                                        <dd class="text-gray-900 flex-1">
                                @elseif($createdAppointment && $createdAppointment->appointment_format === 'online')
                                    <div class="h-px bg-gray-300"></div>
                                    <div class="flex items-start gap-4">
                                        <dt class="text-gray-500 font-medium min-w-[100px]">Meeting</dt>
                                        <dd class="text-gray-900 flex-1">
                                            <div class="flex items-center gap-2 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                                                <svg class="w-5 h-5 text-blue-600 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                </svg>
                                                <span class="text-sm text-blue-800">
                                                    <strong>Meeting link is being generated...</strong><br>
                                                    <span class="text-xs">You'll receive it via email shortly</span>
                                                </span>
                                            </div>
                                        </dd>
                                    </div>
                                @endif

                                @if($createdAppointment && $createdAppointment->meeting_url)
                                    <div style="display:none"></div> {{-- Close the previous dd tag --}}
                                    <div class="flex items-start gap-4" style="display:none">
                                        <dt></dt>
                                        <dd>
                                            <div class="flex items-center gap-2 mb-2">
                                                @if($createdAppointment->meeting_platform === 'zoom')
                                                    <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                                                        <path d="M7.5 9V15L14.5 12L7.5 9M2 3H22C23.1 3 24 3.9 24 5V19C24 20.1 23.1 21 22 21H2C0.9 21 0 20.1 0 19V5C0 3.9 0.9 3 2 3M9 6C7.9 6 7 6.9 7 8V16C7 17.1 7.9 18 9 18H19C20.1 18 21 17.1 21 16V8C21 6.9 20.1 6 19 6H9Z" />
                                                    </svg>
                                                    <span class="font-semibold text-blue-600">Zoom Meeting</span>
                                                @elseif($createdAppointment->meeting_platform === 'google_meet')
                                                    <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 24 24">
                                                        <path d="M15,6H9v6H15M20,5V19A1,1 0 0,1 19,20H5A1,1 0 0,1 4,19V5A1,1 0 0,1 5,4H19A1,1 0 0,1 20,5M18,14H16V12H18M18,11H16V9H18V11Z" />
                                                    </svg>
                                                    <span class="font-semibold text-green-600">Google Meet</span>
                                                @endif
                                            </div>
                                            <a href="{{ $createdAppointment->meeting_url }}" target="_blank"
                                               class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium transition-colors">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                                </svg>
                                                Join Meeting
                                            </a>
                                            @if($createdAppointment->meeting_password)
                                                <div class="mt-2 text-sm text-gray-600">
                                                    <span class="font-medium">Password:</span> <code class="bg-gray-100 px-2 py-1 rounded">{{ $createdAppointment->meeting_password }}</code>
                                                </div>
                                            @endif
                                            @if($createdAppointment->meeting_id)
                                                <div class="mt-1 text-sm text-gray-600">
                                                    <span class="font-medium">Meeting ID:</span> <code class="bg-gray-100 px-2 py-1 rounded">{{ $createdAppointment->meeting_id }}</code>
                                                </div>
                                            @endif
                                        </dd>
                                    </div>
                                @endif
                            </dl>
                        </div>

                        {{-- Action Buttons --}}
                        <div class="mt-8 flex flex-col sm:flex-row gap-4 justify-center">
                            <a
                                href="{{ url('/') }}"
                                class="px-6 py-3 bg-white border-2 border-gray-300 text-gray-700 font-semibold rounded-lg hover:bg-gray-50 transition-all"
                            >
                                Return Home
                            </a>
                        </div>

                        {{-- Additional Info --}}
                        <div class="mt-8 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                            <p class="text-sm text-blue-800">
                                <strong>What's next?</strong> Check your email inbox within the next few minutes for:
                            </p>
                            <ul class="text-sm text-blue-800 mt-2 ml-4 list-disc space-y-1">
                                <li>Full appointment details and confirmation</li>
                                @if($createdAppointment && $createdAppointment->appointment_format === 'online')
                                <li>Meeting link (Zoom/Google Meet) to join the appointment</li>
                                @endif
                                <li>Calendar invitation to add to your schedule</li>
                            </ul>
                            <p class="text-sm text-blue-800 mt-3">
                                If you need to make changes, please contact us directly.
                            </p>
                        </div>
                    </div>

                @elseif($currentStep === 1)
                    {{-- Step 1: Select Appointment Type --}}

                    {{-- Hero Section --}}
                    <div class="text-center mb-10 pb-8 border-b border-gray-200">
                        <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-3">Choose Your Service</h2>
                        <p class="text-gray-600 text-lg mb-6 max-w-2xl mx-auto">Select from our range of professional services tailored to meet your needs</p>

                        {{-- Trust Elements --}}
                        <div class="flex flex-wrap items-center justify-center gap-6 text-sm text-gray-500 mt-6">
                            <div class="flex items-center gap-2">
                                <svg class="w-5 h-5" style="color: {{ $bookingSetting->brand_color ?? '#3B82F6' }}" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <span class="font-medium text-gray-700">Instant Confirmation</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <svg class="w-5 h-5" style="color: {{ $bookingSetting->brand_color ?? '#3B82F6' }}" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <span class="font-medium text-gray-700">Secure Booking</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <svg class="w-5 h-5" style="color: {{ $bookingSetting->brand_color ?? '#3B82F6' }}" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <span class="font-medium text-gray-700">Email Reminders</span>
                            </div>
                        </div>
                    </div>

                    {{-- Services Grid --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mt-10">
                        @forelse($appointmentTypes as $type)
                            <a
                                href="{{ route('booking.public', ['slug' => $bookingSetting->booking_slug, 'service' => $type->id]) }}"
                                wire:key="booking-service-{{ $type->id }}"
                                wire:click.prevent="selectType({{ $type->id }})"
                                wire:target="selectType"
                                class="group relative block w-full text-left rounded-2xl overflow-hidden hover:shadow-xl transition-all duration-300 bg-white border border-gray-200 cursor-pointer"
                            >
                                {{-- Service Image --}}
                                <div class="relative h-48 bg-gradient-to-br from-gray-100 to-gray-200 overflow-hidden">
                                    @if($type->image_url)
                                        <img src="{{ Storage::url($type->image_url) }}" alt="{{ $type->name }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center" style="background: linear-gradient(135deg, {{ $type->color }}15 0%, {{ $type->color }}30 100%)">
                                            <svg class="w-16 h-16 opacity-30" style="color: {{ $type->color }}" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                                            </svg>
                                        </div>
                                    @endif

                                    {{-- Price Tag --}}
                                    @if($type->price > 0)
                                        <div class="absolute bottom-3 right-3 bg-white px-3 py-1.5 rounded-full shadow-md">
                                            <span class="text-sm font-bold" style="color: {{ $bookingSetting->brand_color ?? '#3B82F6' }}">${{ number_format($type->price, 2) }}</span>
                                        </div>
                                    @else
                                        <div class="absolute bottom-3 right-3 bg-green-500 px-3 py-1.5 rounded-full shadow-md">
                                            <span class="text-sm font-bold text-white">Free</span>
                                        </div>
                                    @endif
                                </div>

                                {{-- Service Details --}}
                                <div class="p-5">
                                    <div class="flex items-center gap-2 mb-2">
                                        <div class="w-2 h-2 rounded-full flex-shrink-0" style="background-color: {{ $type->color }}"></div>
                                        <h3 class="text-lg font-bold text-gray-900 line-clamp-1">{{ $type->name }}</h3>
                                    </div>

                                    @if($type->description)
                                        <p class="text-gray-600 text-sm mb-4 line-clamp-2">{{ $type->description }}</p>
                                    @endif

                                    @if($type->review_count > 0)
                                        <div class="mb-4 flex items-center gap-2 text-xs text-gray-600">
                                            <span class="font-semibold text-gray-900">{{ number_format($type->approved_reviews_avg_rating, 1) }}</span>
                                            <span class="tracking-wide text-yellow-500">{{ str_repeat('*', (int) round($type->approved_reviews_avg_rating)) }}</span>
                                            <span>({{ $type->review_count }})</span>
                                        </div>
                                    @endif

                                    <div class="flex items-center justify-between pt-3 border-t border-gray-100">
                                    <div class="flex items-center gap-4 text-sm text-gray-500">
                                        <span class="flex items-center gap-1.5">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            <span class="font-medium">{{ $type->duration_minutes }} min</span>
                                        </span>

                                        @if($type->appointment_format)
                                            <span class="flex items-center gap-1.5">
                                                @if($type->appointment_format === 'online')
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                                    </svg>
                                                    <span class="font-medium">Online</span>
                                                @elseif($type->appointment_format === 'in_person')
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                    </svg>
                                                    <span class="font-medium">In-Person</span>
                                                @elseif($type->appointment_format === 'hybrid')
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"></path>
                                                    </svg>
                                                    <span class="font-medium">Hybrid</span>
                                                @endif
                                            </span>
                                        @endif
                                    </div>
                                    <span class="text-xs text-gray-400 group-hover:text-gray-600 transition-colors flex-shrink-0 ml-2">
                                        Choose →
                                    </span>
                                </div>
                                </div>
                            </a>
                        @empty
                            <div class="col-span-full text-center py-16 text-gray-500">
                                <svg class="w-16 h-16 mx-auto mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                                </svg>
                                <p class="text-lg font-medium">No services available for booking at this time.</p>
                            </div>
                        @endforelse
                    </div>

                    @if($recentReviews->isNotEmpty())
                        <div class="mt-10 border-t border-gray-200 pt-8">
                            <h3 class="text-lg font-bold text-gray-900 mb-4">Recent reviews</h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                @foreach($recentReviews as $review)
                                    <div class="rounded-xl border border-gray-200 bg-gray-50 p-4">
                                        <div class="flex items-center justify-between gap-3">
                                            <div class="font-semibold text-gray-900">{{ $review->display_name }}</div>
                                            <div class="text-sm text-yellow-500">{{ str_repeat('*', $review->rating) }}</div>
                                        </div>
                                        @if($review->appointmentType)
                                            <div class="mt-1 text-xs text-gray-500">{{ $review->appointmentType->name }}</div>
                                        @endif
                                        @if($review->comment)
                                            <p class="mt-3 text-sm text-gray-700">{{ Str::limit($review->comment, 150) }}</p>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                @elseif($currentStep === 2)
                    {{-- Step 2: Select Date and Time --}}
                    <div class="mb-6">
                        <button
                            wire:click="goBack"
                            class="text-gray-600 hover:text-gray-900 flex items-center gap-1 text-sm transition-colors"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                            </svg>
                            Back
                        </button>
                    </div>

                    <h2 class="text-xl font-semibold text-gray-900 mb-2">Choose Date & Time</h2>
                    <p class="text-gray-600 mb-6">{{ $selectedType->name }} - {{ $selectedType->duration_minutes }} minutes</p>

                    <div class="grid md:grid-cols-2 gap-8">
                        {{-- Calendar --}}
                        <div>
                            <h3 class="font-semibold text-gray-900 mb-4 flex items-center gap-2">
                                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                Select Date
                            </h3>

                            @php $calendarDates = array_column($availableDates, 'date'); @endphp
                            <div
                                x-data="bookingCalendar(@js($calendarDates), '{{ $bookingSetting->brand_color ?? '#3B82F6' }}', @js($selectedDate))"
                                class="bg-white border border-gray-200 rounded-xl overflow-hidden relative"
                            >
                                {{-- Loading overlay --}}
                                <div wire:loading wire:target="selectType" class="absolute inset-0 bg-white/80 backdrop-blur-sm z-10 flex items-center justify-center">
                                    <svg class="animate-spin h-7 w-7" style="color: {{ $bookingSetting->brand_color ?? '#3B82F6' }}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </div>

                                {{-- Month navigation --}}
                                <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100">
                                    <button @click="prevMonth()" :disabled="!canPrev" type="button"
                                        class="p-1.5 rounded-lg transition-colors hover:bg-gray-100 disabled:opacity-30 disabled:cursor-not-allowed">
                                        <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                                        </svg>
                                    </button>
                                    <span class="text-sm font-semibold text-gray-900" x-text="monthLabel"></span>
                                    <button @click="nextMonth()" :disabled="!canNext" type="button"
                                        class="p-1.5 rounded-lg transition-colors hover:bg-gray-100 disabled:opacity-30 disabled:cursor-not-allowed">
                                        <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                        </svg>
                                    </button>
                                </div>

                                {{-- Day-of-week headers --}}
                                <div class="grid grid-cols-7 px-3 pt-3">
                                    <template x-for="h in ['Su','Mo','Tu','We','Th','Fr','Sa']">
                                        <div class="text-center text-xs font-medium text-gray-400 pb-2" x-text="h"></div>
                                    </template>
                                </div>

                                {{-- Date grid --}}
                                <div class="grid grid-cols-7 gap-0.5 px-3 pb-4">
                                    <template x-for="(day, idx) in calendarDays" :key="idx">
                                        <div class="flex items-center justify-center">
                                            <template x-if="day.empty"><div class="w-9 h-9"></div></template>
                                            <template x-if="!day.empty">
                                                <button
                                                    @click="if (day.clickable) { selectedDate = day.date; $wire.selectDate(day.date) }"
                                                    :disabled="!day.clickable"
                                                    type="button"
                                                    :style="day.selected ? `background-color:${color};color:white;` : ''"
                                                    :class="{
                                                        'hover:bg-gray-100 text-gray-900 font-medium cursor-pointer': day.clickable && !day.selected,
                                                        'text-white font-bold': day.selected,
                                                        'opacity-25 cursor-not-allowed text-gray-400': day.past,
                                                        'text-gray-300 cursor-not-allowed': !day.available && !day.past,
                                                    }"
                                                    class="w-9 h-9 rounded-full text-sm transition-all flex items-center justify-center"
                                                    x-text="day.num"
                                                ></button>
                                            </template>
                                        </div>
                                    </template>
                                </div>

                                @if(empty($availableDates))
                                    <p class="text-center pb-4 text-sm text-gray-400">No availability found for this service.</p>
                                @endif
                            </div>
                        </div>

                        {{-- Available Time Slots --}}
                        <div>
                            <h3 class="font-semibold text-gray-900 mb-4 flex items-center gap-2">
                                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                @if($selectedDate)
                                    {{ \Carbon\Carbon::parse($selectedDate)->format('l, M j, Y') }}
                                @else
                                    Select Time
                                @endif
                            </h3>

                            @if($selectedDate)
                                <div class="space-y-2 max-h-[400px] overflow-y-auto pr-2 custom-scrollbar relative">
                                    {{-- Loading overlay for time slots --}}
                                    <div wire:loading wire:target="selectDate" class="absolute inset-0 bg-white/80 backdrop-blur-sm rounded-lg z-10 flex items-center justify-center">
                                        <div class="text-center">
                                            <svg class="animate-spin h-8 w-8 mx-auto mb-2" style="color: {{ $bookingSetting->brand_color ?? '#3B82F6' }}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                            <p class="text-sm font-medium text-gray-700">Loading times...</p>
                                        </div>
                                    </div>
                                    @forelse($availableSlots as $slotInfo)
                                        <button
                                            wire:click="selectTime('{{ $slotInfo['time'] }}')"
                                            wire:loading.attr="disabled"
                                            wire:target="selectTime"
                                            type="button"
                                            class="w-full text-center px-4 py-3 border-2 rounded-lg hover:shadow-sm transition-all duration-200 font-medium disabled:opacity-50 disabled:cursor-wait
                                            {{ $selectedTime === $slotInfo['time'] ? 'ring-2 text-white' : 'border-gray-200 text-gray-700 hover:border-gray-300' }}"
                                            style="{{ $selectedTime === $slotInfo['time'] ? 'background-color: ' . ($bookingSetting->brand_color ?? '#3B82F6') . '; border-color: ' . ($bookingSetting->brand_color ?? '#3B82F6') : '' }}"
                                        >
                                            {{ $slotInfo['formatted'] }}
                                        </button>
                                    @empty
                                        <div class="text-center py-12 text-gray-400 border-2 border-dashed border-gray-200 rounded-lg">
                                            <svg class="w-10 h-10 mx-auto mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            <p class="text-sm">No times available for this date</p>
                                        </div>
                                    @endforelse
                                </div>
                            @else
                                <div class="text-center py-16 text-gray-400 border-2 border-dashed border-gray-200 rounded-lg">
                                    <svg class="w-12 h-12 mx-auto mb-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <p class="text-sm font-medium">Please select a date first</p>
                                </div>
                            @endif
                        </div>
                    </div>

                @elseif($currentStep == 2.5)
                    {{-- Step 2.5: Select Venue (for in-person appointments) --}}
                    <div class="mb-6">
                        <button
                            wire:click="goBack"
                            class="text-gray-600 hover:text-gray-900 flex items-center gap-1 text-sm transition-colors"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                            </svg>
                            Back
                        </button>
                    </div>

                    <h2 class="text-xl font-semibold text-gray-900 mb-2">Select Location</h2>
                    <p class="text-gray-600 mb-6">
                        {{ $selectedType->name }} - {{ \Carbon\Carbon::parse($selectedDate . ' ' . $selectedTime)->format('F j, Y \a\t g:i A') }}
                    </p>

                    <div class="space-y-3 relative">
                        {{-- Loading overlay for venues --}}
                        <div wire:loading wire:target="selectTime" class="absolute inset-0 bg-white/80 backdrop-blur-sm rounded-lg z-10 flex items-center justify-center">
                            <div class="text-center">
                                <svg class="animate-spin h-10 w-10 mx-auto mb-2" style="color: {{ $bookingSetting->brand_color ?? '#3B82F6' }}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <p class="text-sm font-medium text-gray-700">Loading venues...</p>
                            </div>
                        </div>

                        @forelse($availableVenues as $venue)
                            <button
                                wire:click="selectVenue({{ $venue->id }})"
                                wire:loading.attr="disabled"
                                wire:target="selectVenue"
                                type="button"
                                class="w-full text-left p-5 border-2 rounded-xl hover:shadow-md transition-all duration-200 disabled:opacity-50 disabled:cursor-wait {{ $selectedVenueId == $venue->id ? 'ring-2' : 'border-gray-200' }}"
                                style="{{ $selectedVenueId == $venue->id ? 'border-color: ' . ($bookingSetting->brand_color ?? '#3B82F6') . '; box-shadow: 0 0 0 1px ' . ($bookingSetting->brand_color ?? '#3B82F6') : '' }}"
                            >
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-3 mb-2">
                                            <svg class="w-5 h-5 text-gray-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            </svg>
                                            <h3 class="text-lg font-semibold text-gray-900">{{ $venue->name }}</h3>
                                        </div>

                                        @if($venue->full_address)
                                            <p class="text-gray-600 mb-2 text-sm">{{ $venue->full_address }}</p>
                                        @endif

                                        @if($venue->description)
                                            <p class="text-gray-600 mb-3 text-sm">{{ Str::limit($venue->description, 100) }}</p>
                                        @endif

                                        @if($venue->google_maps_url)
                                            <a 
                                                href="{{ $venue->google_maps_url }}" 
                                                target="_blank"
                                                onclick="event.stopPropagation();"
                                                class="inline-flex items-center gap-1 text-sm text-blue-600 hover:text-blue-700 font-medium"
                                            >
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                                </svg>
                                                View on Map
                                            </a>
                                        @endif
                                    </div>

                                    <svg class="w-6 h-6 text-gray-400 flex-shrink-0 ml-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </div>
                            </button>
                        @empty
                            <div class="text-center py-12 text-gray-500 border-2 border-dashed border-gray-200 rounded-lg">
                                <svg class="w-12 h-12 mx-auto mb-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                <p class="text-sm font-medium">No venues available for this time slot</p>
                                <p class="text-xs text-gray-400 mt-1">Please select a different time</p>
                                <button
                                    wire:click="goBack"
                                    class="mt-4 px-4 py-2 text-sm text-gray-600 hover:text-gray-900 border border-gray-300 rounded-lg transition-colors"
                                >
                                    Go Back
                                </button>
                            </div>
                        @endforelse
                    </div>

                @elseif($currentStep === 3)
                    {{-- Step 3: Contact Information --}}
                    <div class="mb-6">
                        <button
                            wire:click="goBack"
                            class="text-gray-600 hover:text-gray-900 flex items-center gap-1 text-sm transition-colors"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                            </svg>
                            Back
                        </button>
                    </div>

                    <h2 class="text-xl font-semibold text-gray-900 mb-2">Your Information</h2>
                    <p class="text-gray-600 mb-6">
                        {{ \Carbon\Carbon::parse($selectedDate . ' ' . $selectedTime)->format('F j, Y \a\t g:i A') }}
                    </p>

                    <form wire:submit.prevent="submitBooking" class="space-y-5 relative">
                        {{-- Loading Overlay --}}
                        <div wire:loading wire:target="submitBooking" class="absolute inset-0 bg-white/70 backdrop-blur-sm rounded-lg z-10 flex items-center justify-center">
                            <div class="text-center">
                                <svg class="animate-spin h-12 w-12 mx-auto mb-3" style="color: {{ $bookingSetting->brand_color ?? '#3B82F6' }}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <p class="text-lg font-semibold text-gray-900">Creating your appointment...</p>
                                <p class="text-sm text-gray-600 mt-1">Please wait while we process your booking</p>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Full Name <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="text"
                                wire:model="attendeeName"
                                wire:loading.attr="disabled"
                                wire:target="submitBooking"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all disabled:bg-gray-50 disabled:cursor-not-allowed"
                                placeholder="John Doe"
                                required
                            >
                            @error('attendeeName') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Email <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="email"
                                wire:model="attendeeEmail"
                                wire:loading.attr="disabled"
                                wire:target="submitBooking"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all disabled:bg-gray-50 disabled:cursor-not-allowed"
                                placeholder="john@example.com"
                                required
                            >
                            @error('attendeeEmail') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Phone Number @if($selectedType->require_phone)<span class="text-red-500">*</span>@endif
                            </label>
                            <input
                                type="tel"
                                wire:model="attendeePhone"
                                wire:loading.attr="disabled"
                                wire:target="submitBooking"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all disabled:bg-gray-50 disabled:cursor-not-allowed"
                                placeholder="+1 (555) 123-4567"
                                {{ $selectedType->require_phone ? 'required' : '' }}
                            >
                            @error('attendeePhone') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        @if($selectedType->require_company)
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Company <span class="text-red-500">*</span>
                                </label>
                                <input
                                    type="text"
                                    wire:model="attendeeCompany"
                                    wire:loading.attr="disabled"
                                    wire:target="submitBooking"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all disabled:bg-gray-50 disabled:cursor-not-allowed"
                                    placeholder="Acme Inc."
                                    required
                                >
                                @error('attendeeCompany') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
                            </div>
                        @endif

                        @if(in_array($selectedType->appointment_format ?? 'online', ['in_person', 'hybrid']))
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Location <span class="text-red-500">*</span>
                                </label>
                                <input
                                    type="text"
                                    wire:model="location"
                                    wire:loading.attr="disabled"
                                    wire:target="submitBooking"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all disabled:bg-gray-50 disabled:cursor-not-allowed"
                                    placeholder="Enter your address (e.g., 123 Main St, City, State, ZIP)"
                                    required
                                >
                                <p class="text-sm text-gray-500 mt-1">
                                    <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                    Where should this appointment take place?
                                </p>
                                @error('location') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
                            </div>
                        @endif

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Additional Notes
                            </label>
                            <textarea
                                wire:model="notes"
                                wire:loading.attr="disabled"
                                wire:target="submitBooking"
                                rows="4"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all resize-none disabled:bg-gray-50 disabled:cursor-not-allowed"
                                placeholder="Anything we should know?"
                            ></textarea>
                            @error('notes') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div class="pt-4">
                            <button
                                type="submit"
                                wire:loading.attr="disabled"
                                wire:target="submitBooking"
                                class="w-full px-6 py-4 text-white font-semibold rounded-lg hover:opacity-90 transition-all shadow-md hover:shadow-lg transform hover:scale-[1.02] disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none relative"
                                style="background-color: {{ $bookingSetting->brand_color ?? '#3B82F6' }}"
                            >
                                <span wire:loading.remove wire:target="submitBooking" class="flex items-center justify-center gap-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    Confirm Booking
                                </span>
                                <span wire:loading wire:target="submitBooking" class="flex items-center justify-center gap-2">
                                    <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Processing...
                                </span>
                            </button>
                        </div>
                    </form>
                @endif
            </div>
        </div>

        {{-- Footer --}}
        <div class="text-center text-sm text-gray-500">
            <p>Powered by {{ config('app.name') }}</p>
        </div>
    </div>

    {{-- Loading Spinner - Inline --}}
    <div wire:loading class="fixed top-4 right-4 z-50">
        <div class="bg-white rounded-lg px-4 py-3 shadow-lg flex items-center gap-3">
            <svg class="animate-spin h-5 w-5 text-primary-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span class="text-sm font-medium text-gray-700">Loading...</span>
        </div>
    </div>
</div>
