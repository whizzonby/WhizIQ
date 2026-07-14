<div
    class="wiq-landing min-h-screen"
    wire:loading.class="opacity-50"
    style="
        --wiq-bg: #F7F6F3;
        --wiq-ink: #211F1C;
        --wiq-muted: #6B6559;
        --wiq-border: #E4E0D8;
        background: var(--wiq-bg);
        color: var(--wiq-ink);
    "
>
    <style>
        .wiq-landing .wiq-serif { font-family: 'Fraunces', ui-serif, Georgia, serif; }

        .wiq-landing .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .wiq-landing .custom-scrollbar::-webkit-scrollbar-track { background: var(--wiq-border); border-radius: 10px; }
        .wiq-landing .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbb; border-radius: 10px; }

        .wiq-landing .wiq-btn-primary {
            background-color: var(--wiq-accent);
            color: #fff;
        }
        .wiq-landing .wiq-btn-primary:hover { opacity: .92; }
        .wiq-landing .wiq-accent-text { color: var(--wiq-accent); }
        .wiq-landing .wiq-accent-ring { box-shadow: 0 0 0 2px var(--wiq-accent); border-color: var(--wiq-accent); }

        .wiq-landing .wiq-gallery-item { break-inside: avoid; }
        .wiq-landing .wiq-gallery-grid { columns: 1; column-gap: 1rem; }
        @media (min-width: 640px) { .wiq-landing .wiq-gallery-grid { columns: 2; } }
        @media (min-width: 1024px) { .wiq-landing .wiq-gallery-grid { columns: 3; } }
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

            Alpine.data('wiqLightbox', () => ({
                open: false,
                src: null,
                show(src) { this.src = src; this.open = true; },
                close() { this.open = false; },
            }));
        });
    </script>

    @if($confirmed)
        {{-- Confirmation --}}
        <div class="max-w-2xl mx-auto px-4 py-16">
            <div class="text-center">
                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full mb-6" style="background-color: color-mix(in srgb, var(--wiq-accent) 15%, transparent)">
                    <svg class="h-9 w-9 wiq-accent-text" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>

                <h1 class="wiq-serif text-3xl sm:text-4xl font-medium mb-3">
                    @if($bookingSetting->require_approval)
                        Request submitted
                    @else
                        You're all set
                    @endif
                </h1>

                <p class="text-[var(--wiq-muted)] text-base sm:text-lg mb-10 max-w-lg mx-auto">
                    @if($bookingSetting->require_approval)
                        Your booking request has been submitted and is pending approval. You'll receive a confirmation email once it's approved.
                    @else
                        Your appointment is confirmed. A confirmation email with all the details will be sent to <strong>{{ $attendeeEmail }}</strong> shortly.
                    @endif
                </p>
            </div>

            <div class="bg-white rounded-2xl p-6 sm:p-8 text-left border border-[var(--wiq-border)]">
                <h2 class="wiq-serif text-lg font-medium mb-6">Appointment details</h2>

                <dl class="space-y-4 text-sm">
                    <div class="flex items-start gap-4">
                        <dt class="text-[var(--wiq-muted)] min-w-[110px]">Service</dt>
                        <dd class="font-medium flex-1">{{ $selectedType->name }}</dd>
                    </div>
                    <div class="h-px bg-[var(--wiq-border)]"></div>
                    <div class="flex items-start gap-4">
                        <dt class="text-[var(--wiq-muted)] min-w-[110px]">Date</dt>
                        <dd class="font-medium flex-1">{{ \Carbon\Carbon::parse($selectedDate)->format('l, F j, Y') }}</dd>
                    </div>
                    <div class="h-px bg-[var(--wiq-border)]"></div>
                    <div class="flex items-start gap-4">
                        <dt class="text-[var(--wiq-muted)] min-w-[110px]">Time</dt>
                        <dd class="font-medium flex-1">{{ \Carbon\Carbon::createFromFormat('H:i', $selectedTime)->format('g:i A') }}</dd>
                    </div>
                    <div class="h-px bg-[var(--wiq-border)]"></div>
                    <div class="flex items-start gap-4">
                        <dt class="text-[var(--wiq-muted)] min-w-[110px]">Duration</dt>
                        <dd class="font-medium flex-1">{{ $selectedType->duration_minutes }} minutes</dd>
                    </div>
                    <div class="h-px bg-[var(--wiq-border)]"></div>
                    <div class="flex items-start gap-4">
                        <dt class="text-[var(--wiq-muted)] min-w-[110px]">Attendee</dt>
                        <dd class="font-medium flex-1">
                            {{ $attendeeName }}<br>
                            <span class="text-[var(--wiq-muted)] font-normal">{{ $attendeeEmail }}</span>
                        </dd>
                    </div>

                    @if($createdAppointment && ($createdAppointment->venue || $createdAppointment->location))
                        <div class="h-px bg-[var(--wiq-border)]"></div>
                        <div class="flex items-start gap-4">
                            <dt class="text-[var(--wiq-muted)] min-w-[110px]">Location</dt>
                            <dd class="flex-1">
                                @if($createdAppointment->venue)
                                    @php($venue = $createdAppointment->venue)
                                    <div class="font-medium mb-1">{{ $venue->name }}</div>
                                    @if($venue->full_address)
                                        <div class="text-[var(--wiq-muted)] mb-2">{{ $venue->full_address }}</div>
                                    @endif
                                    @if($createdAppointment->room_name)
                                        <div class="text-[var(--wiq-muted)] mb-2">Room: {{ $createdAppointment->room_name }}</div>
                                    @endif
                                    @if($venue->google_maps_url)
                                        <a href="{{ $venue->google_maps_url }}" target="_blank" class="inline-flex items-center gap-1 wiq-accent-text font-medium">
                                            View on map →
                                        </a>
                                    @endif
                                @elseif($createdAppointment->location)
                                    {{ $createdAppointment->location }}
                                @endif
                            </dd>
                        </div>
                    @endif

                    @if($createdAppointment && $createdAppointment->meeting_url)
                        <div class="h-px bg-[var(--wiq-border)]"></div>
                        <div class="flex items-start gap-4">
                            <dt class="text-[var(--wiq-muted)] min-w-[110px]">Meeting</dt>
                            <dd class="flex-1">
                                <div class="flex items-center gap-2 mb-2 text-[var(--wiq-muted)]">
                                    @if($createdAppointment->meeting_platform === 'zoom')
                                        Zoom Meeting
                                    @elseif($createdAppointment->meeting_platform === 'google_meet')
                                        Google Meet
                                    @endif
                                </div>
                                <a href="{{ $createdAppointment->meeting_url }}" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 wiq-btn-primary rounded-lg text-sm font-medium">
                                    Join meeting
                                </a>
                                @if($createdAppointment->meeting_password)
                                    <div class="mt-2 text-[var(--wiq-muted)]">Password: <code class="bg-[var(--wiq-bg)] px-2 py-0.5 rounded">{{ $createdAppointment->meeting_password }}</code></div>
                                @endif
                                @if($createdAppointment->meeting_id)
                                    <div class="mt-1 text-[var(--wiq-muted)]">Meeting ID: <code class="bg-[var(--wiq-bg)] px-2 py-0.5 rounded">{{ $createdAppointment->meeting_id }}</code></div>
                                @endif
                            </dd>
                        </div>
                    @elseif($createdAppointment && $createdAppointment->appointment_format === 'online')
                        <div class="h-px bg-[var(--wiq-border)]"></div>
                        <div class="flex items-start gap-4">
                            <dt class="text-[var(--wiq-muted)] min-w-[110px]">Meeting</dt>
                            <dd class="flex-1 text-[var(--wiq-muted)]">Meeting link is being generated — you'll receive it by email shortly.</dd>
                        </div>
                    @endif
                </dl>
            </div>

            <div class="mt-8 rounded-xl border border-[var(--wiq-border)] bg-white p-5 text-sm text-[var(--wiq-muted)]">
                <p class="font-medium text-[var(--wiq-ink)] mb-2">What's next?</p>
                <ul class="space-y-1 list-disc list-inside">
                    <li>Full appointment confirmation by email</li>
                    @if($createdAppointment && $createdAppointment->appointment_format === 'online')
                        <li>Meeting link to join at the scheduled time</li>
                    @endif
                    <li>A calendar invite you can add to your schedule</li>
                </ul>
                <p class="mt-3">Need to make changes? Just contact us directly.</p>
            </div>

            <div class="mt-8 text-center">
                <a href="{{ route('booking.public', ['slug' => $bookingSetting->booking_slug]) }}" class="inline-flex px-6 py-3 border border-[var(--wiq-border)] rounded-lg font-medium hover:bg-white transition-colors">
                    Back to {{ $bookingSetting->display_name }}
                </a>
            </div>
        </div>

    @elseif($currentStep === 1)
        {{-- ============ LANDING PAGE ============ --}}

        {{-- Hero --}}
        <section class="relative overflow-hidden">
            <div class="absolute inset-0">
                @if($bookingSetting->cover_image_url)
                    <img src="{{ Storage::url($bookingSetting->cover_image_url) }}" alt="" class="w-full h-full object-cover">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/30 to-black/10"></div>
                @else
                    <div class="w-full h-full" style="background: linear-gradient(160deg, var(--wiq-accent) 0%, color-mix(in srgb, var(--wiq-accent) 60%, #211F1C) 100%)"></div>
                @endif
            </div>

            @php($onDark = (bool) $bookingSetting->cover_image_url)

            <div class="relative max-w-5xl mx-auto px-6 pt-16 pb-24 sm:pt-24 sm:pb-32 text-center {{ $onDark ? 'text-white' : 'text-white' }}">
                @if($bookingSetting->logo_url)
                    <img src="{{ Storage::url($bookingSetting->logo_url) }}" alt="{{ $bookingSetting->display_name }}" class="h-14 w-14 rounded-full mx-auto mb-6 object-cover ring-2 ring-white/40">
                @endif

                <h1 class="wiq-serif text-4xl sm:text-6xl font-medium tracking-tight">
                    {{ $bookingSetting->display_name }}
                </h1>

                @if($bookingSetting->welcome_message)
                    <p class="mt-4 text-base sm:text-xl max-w-2xl mx-auto text-white/85">{{ $bookingSetting->welcome_message }}</p>
                @endif

                <div class="mt-6 flex flex-wrap items-center justify-center gap-x-6 gap-y-2 text-sm text-white/85">
                    @if($bookingSetting->review_count > 0)
                        <div class="flex items-center gap-2">
                            @include('livewire.partials.star-rating', ['rating' => $bookingSetting->average_rating, 'color' => 'text-amber-300'])
                            <span>{{ number_format($bookingSetting->average_rating, 1) }} · {{ $bookingSetting->review_count }} {{ Str::plural('review', $bookingSetting->review_count) }}</span>
                        </div>
                    @endif

                    @if($bookingSetting->business_city || $bookingSetting->business_country)
                        <div class="flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <span>{{ collect([$bookingSetting->business_city, $bookingSetting->business_country])->filter()->implode(', ') }}</span>
                        </div>
                    @endif
                </div>

                @if($nextAvailableSlot)
                    <div class="mt-8 inline-flex items-center gap-2.5 rounded-full bg-white/95 backdrop-blur px-5 py-2.5 shadow-lg">
                        <span class="relative flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full opacity-60" style="background-color: var(--wiq-accent)"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2" style="background-color: var(--wiq-accent)"></span>
                        </span>
                        <span class="text-sm font-medium text-[var(--wiq-ink)]">
                            Next available: {{ $nextAvailableSlot['date']->isToday() ? 'Today' : ($nextAvailableSlot['date']->isTomorrow() ? 'Tomorrow' : $nextAvailableSlot['date']->format('D, M j')) }} at {{ $nextAvailableSlot['time'] }}
                        </span>
                    </div>
                @endif

                <div class="mt-10">
                    <a href="#services" class="inline-flex items-center gap-2 px-7 py-3.5 rounded-full font-semibold wiq-btn-primary shadow-lg transition-transform hover:scale-[1.02]">
                        Book an appointment
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
                    </a>
                </div>
            </div>
        </section>

        @if(session()->has('error'))
            <div class="max-w-3xl mx-auto px-6 mt-6">
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm">{{ session('error') }}</div>
            </div>
        @endif

        {{-- Services --}}
        <section id="services" class="max-w-6xl mx-auto px-6 py-16 sm:py-20 scroll-mt-8">
            <div class="text-center mb-12">
                <p class="text-xs font-semibold tracking-widest uppercase wiq-accent-text mb-3">Services</p>
                <h2 class="wiq-serif text-3xl sm:text-4xl font-medium">Choose what you need</h2>
                <p class="mt-3 text-[var(--wiq-muted)] max-w-xl mx-auto">Pick a service to see availability and book instantly.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($appointmentTypes as $type)
                    <div
                        wire:click="selectType({{ $type->id }})"
                        wire:key="service-{{ $type->id }}"
                        class="group relative text-left rounded-2xl overflow-hidden bg-white border border-[var(--wiq-border)] cursor-pointer transition-all duration-300 hover:shadow-xl hover:-translate-y-0.5"
                    >
                        <div class="relative h-44 bg-[var(--wiq-bg)] overflow-hidden">
                            @if($type->image_url)
                                <img src="{{ Storage::url($type->image_url) }}" alt="{{ $type->name }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                            @else
                                <div class="w-full h-full flex items-center justify-center" style="background: linear-gradient(135deg, {{ $type->color }}12 0%, {{ $type->color }}28 100%)">
                                    <svg class="w-14 h-14 opacity-25" style="color: {{ $type->color }}" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                                    </svg>
                                </div>
                            @endif

                            @if($type->price > 0)
                                <div class="absolute bottom-3 right-3 bg-white px-3 py-1 rounded-full shadow-sm">
                                    <span class="text-sm font-semibold wiq-accent-text">${{ number_format($type->price, 2) }}</span>
                                </div>
                            @else
                                <div class="absolute bottom-3 right-3 bg-[var(--wiq-ink)] px-3 py-1 rounded-full shadow-sm">
                                    <span class="text-sm font-semibold text-white">Free</span>
                                </div>
                            @endif
                        </div>

                        <div class="p-5">
                            <div class="flex items-center gap-2 mb-1.5">
                                <span class="w-1.5 h-1.5 rounded-full flex-shrink-0" style="background-color: {{ $type->color }}"></span>
                                <h3 class="wiq-serif text-lg font-medium line-clamp-1">{{ $type->name }}</h3>
                            </div>

                            @if($type->description)
                                <p class="text-[var(--wiq-muted)] text-sm mb-3 line-clamp-2">{{ $type->description }}</p>
                            @endif

                            @if($type->review_count > 0)
                                <div class="mb-3 flex items-center gap-1.5 text-xs text-[var(--wiq-muted)]">
                                    @include('livewire.partials.star-rating', ['rating' => $type->approved_reviews_avg_rating, 'size' => 'w-3.5 h-3.5'])
                                    <span>{{ number_format($type->approved_reviews_avg_rating, 1) }} ({{ $type->review_count }})</span>
                                </div>
                            @endif

                            <div class="flex items-center justify-between pt-3 border-t border-[var(--wiq-border)] text-xs text-[var(--wiq-muted)]">
                                <div class="flex items-center gap-3">
                                    <span class="flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        {{ $type->duration_minutes }} min
                                    </span>
                                    @if($type->appointment_format)
                                        <span class="flex items-center gap-1">
                                            @if($type->appointment_format === 'online')
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                                Online
                                            @elseif($type->appointment_format === 'in_person')
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                                In person
                                            @elseif($type->appointment_format === 'hybrid')
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/></svg>
                                                Hybrid
                                            @endif
                                        </span>
                                    @endif
                                </div>
                                <a
                                    href="{{ route('booking.service.detail', ['slug' => $bookingSetting->booking_slug, 'serviceId' => $type->id]) }}"
                                    onclick="event.stopPropagation()"
                                    class="text-[var(--wiq-muted)] hover:text-[var(--wiq-ink)] transition-colors flex-shrink-0 ml-2"
                                >Details →</a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full text-center py-16 text-[var(--wiq-muted)]">
                        <svg class="w-14 h-14 mx-auto mb-4 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                        <p>No services available for booking right now.</p>
                    </div>
                @endforelse
            </div>
        </section>

        {{-- About --}}
        @if($bookingSetting->about_text || $bookingSetting->about_title)
            @php($aboutHasImage = (bool) ($bookingSetting->cover_image_url || $bookingSetting->logo_url))
            <section class="border-t border-[var(--wiq-border)]">
                <div class="max-w-6xl mx-auto px-6 py-16 sm:py-20 {{ $aboutHasImage ? 'grid md:grid-cols-2 gap-12 items-center' : '' }}">
                    <div class="{{ $aboutHasImage ? 'order-2 md:order-1' : 'max-w-2xl mx-auto' }}">
                        <p class="text-xs font-semibold tracking-widest uppercase wiq-accent-text mb-3 {{ $aboutHasImage ? '' : 'text-center' }}">About</p>
                        <h2 class="wiq-serif text-3xl sm:text-4xl font-medium mb-5 {{ $aboutHasImage ? '' : 'text-center' }}">{{ $bookingSetting->about_title ?: 'About us' }}</h2>
                        @if($bookingSetting->about_text)
                            <p class="text-[var(--wiq-muted)] leading-relaxed whitespace-pre-line">{{ $bookingSetting->about_text }}</p>
                        @endif

                        @php($openDays = $businessHours->filter(fn ($s) => $s->is_available))
                        @if($openDays->isNotEmpty() || $bookingSetting->business_address)
                            <div class="mt-8 grid sm:grid-cols-2 gap-6 text-sm text-left">
                                @if($bookingSetting->business_address || $bookingSetting->business_city)
                                    <div>
                                        <p class="font-medium mb-1.5">Location</p>
                                        <p class="text-[var(--wiq-muted)]">
                                            {{ collect([$bookingSetting->business_address, $bookingSetting->business_city, $bookingSetting->business_country])->filter()->implode(', ') }}
                                        </p>
                                    </div>
                                @endif
                                @if($openDays->isNotEmpty())
                                    @php($dayNames = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'])
                                    <div>
                                        <p class="font-medium mb-1.5">Hours</p>
                                        <ul class="text-[var(--wiq-muted)] space-y-0.5">
                                            @foreach($openDays->sortKeys() as $day => $schedule)
                                                <li>{{ $dayNames[$day] }}: {{ $schedule->start_time->format('g:i A') }} – {{ $schedule->end_time->format('g:i A') }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>

                    @if($bookingSetting->cover_image_url || $bookingSetting->logo_url)
                        <div class="order-1 md:order-2">
                            <img
                                src="{{ Storage::url($bookingSetting->cover_image_url ?: $bookingSetting->logo_url) }}"
                                alt="{{ $bookingSetting->display_name }}"
                                class="w-full aspect-[4/3] object-cover rounded-2xl"
                            >
                        </div>
                    @endif
                </div>
            </section>
        @endif

        {{-- Portfolio / Gallery --}}
        @if(!empty($bookingSetting->gallery_images))
            <section class="border-t border-[var(--wiq-border)] bg-white" x-data="wiqLightbox">
                <div class="max-w-6xl mx-auto px-6 py-16 sm:py-20">
                    <div class="text-center mb-12">
                        <p class="text-xs font-semibold tracking-widest uppercase wiq-accent-text mb-3">Portfolio</p>
                        <h2 class="wiq-serif text-3xl sm:text-4xl font-medium">Our work</h2>
                    </div>

                    <div class="wiq-gallery-grid">
                        @foreach($bookingSetting->gallery_images as $image)
                            <button type="button" @click="show('{{ Storage::url($image) }}')" class="wiq-gallery-item block w-full mb-4 rounded-xl overflow-hidden">
                                <img src="{{ Storage::url($image) }}" alt="" class="w-full h-auto object-cover hover:scale-[1.02] transition-transform duration-300">
                            </button>
                        @endforeach
                    </div>
                </div>

                <div
                    x-show="open"
                    x-cloak
                    @click="close()"
                    @keydown.escape.window="close()"
                    class="fixed inset-0 z-50 bg-black/90 flex items-center justify-center p-6"
                    x-transition.opacity
                >
                    <img :src="src" class="max-w-full max-h-full rounded-lg" @click.stop>
                    <button @click="close()" class="absolute top-5 right-5 text-white/80 hover:text-white">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </section>
        @endif

        {{-- Testimonials --}}
        @if($recentReviews->isNotEmpty())
            <section class="border-t border-[var(--wiq-border)]">
                <div class="max-w-6xl mx-auto px-6 py-16 sm:py-20">
                    <div class="text-center mb-12">
                        <p class="text-xs font-semibold tracking-widest uppercase wiq-accent-text mb-3">Testimonials</p>
                        <h2 class="wiq-serif text-3xl sm:text-4xl font-medium">What clients say</h2>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                        @foreach($recentReviews as $review)
                            <div class="rounded-2xl border border-[var(--wiq-border)] bg-white p-5">
                                @include('livewire.partials.star-rating', ['rating' => $review->rating, 'size' => 'w-3.5 h-3.5'])
                                @if($review->comment)
                                    <p class="mt-3 text-sm leading-relaxed">&ldquo;{{ Str::limit($review->comment, 160) }}&rdquo;</p>
                                @endif
                                <div class="mt-4 flex items-center justify-between text-xs text-[var(--wiq-muted)]">
                                    <span class="font-medium text-[var(--wiq-ink)]">{{ $review->display_name }}</span>
                                    @if($review->appointmentType)
                                        <span>{{ $review->appointmentType->name }}</span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>
        @endif

        {{-- Footer --}}
        <footer class="border-t border-[var(--wiq-border)] bg-white">
            <div class="max-w-6xl mx-auto px-6 py-12 grid sm:grid-cols-3 gap-8 text-sm">
                <div>
                    <p class="wiq-serif text-lg font-medium mb-2">{{ $bookingSetting->display_name }}</p>
                    @if($bookingSetting->welcome_message)
                        <p class="text-[var(--wiq-muted)]">{{ Str::limit($bookingSetting->welcome_message, 100) }}</p>
                    @endif
                </div>
                @if($bookingSetting->business_address || $bookingSetting->business_city)
                    <div>
                        <p class="font-medium mb-2">Location</p>
                        <p class="text-[var(--wiq-muted)]">
                            {{ collect([$bookingSetting->business_address, $bookingSetting->business_city, $bookingSetting->business_country])->filter()->implode(', ') }}
                        </p>
                    </div>
                @endif
                <div>
                    <p class="font-medium mb-2">Book with us</p>
                    <a href="#services" class="wiq-accent-text font-medium">View services →</a>
                </div>
            </div>
        </footer>

        {{-- Sticky mobile CTA --}}
        <div class="sm:hidden fixed bottom-0 inset-x-0 z-40 bg-white/95 backdrop-blur border-t border-[var(--wiq-border)] p-3" style="padding-bottom: max(0.75rem, env(safe-area-inset-bottom));">
            <a href="#services" class="flex items-center justify-center w-full py-3 rounded-full font-semibold wiq-btn-primary">
                Book an appointment
            </a>
        </div>

    @else
        {{-- ============ BOOKING FLOW (steps 2 / 2.5 / 3) ============ --}}
        <div class="max-w-3xl mx-auto px-4 sm:px-6 py-10">
            {{-- Compact header --}}
            <div class="flex items-center gap-3 mb-8">
                @if($bookingSetting->logo_url)
                    <img src="{{ Storage::url($bookingSetting->logo_url) }}" alt="" class="h-9 w-9 rounded-full object-cover">
                @endif
                <a href="{{ route('booking.public', ['slug' => $bookingSetting->booking_slug]) }}" class="wiq-serif font-medium hover:wiq-accent-text transition-colors">
                    {{ $bookingSetting->display_name }}
                </a>
            </div>

            {{-- Progress --}}
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    @php($steps = ['Select Service', 'Choose Time', 'Your Info'])
                    @foreach($steps as $index => $label)
                        <div class="flex items-center {{ $index < count($steps) - 1 ? 'flex-1' : '' }}">
                            <div class="flex items-center">
                                <div
                                    class="flex items-center justify-center w-7 h-7 rounded-full font-semibold text-xs transition-all
                                    {{ $currentStep > ($index + 1) ? 'bg-emerald-500 text-white' : ($currentStep === ($index + 1) ? 'text-white' : 'bg-[var(--wiq-border)] text-[var(--wiq-muted)]') }}"
                                    style="{{ $currentStep === ($index + 1) ? 'background-color: var(--wiq-accent)' : '' }}"
                                >
                                    @if($currentStep > ($index + 1)) ✓ @else {{ $index + 1 }} @endif
                                </div>
                                <span class="ml-2 text-xs font-medium {{ $currentStep === ($index + 1) ? 'text-[var(--wiq-ink)]' : 'text-[var(--wiq-muted)]' }} hidden sm:inline">
                                    {{ $label }}
                                </span>
                            </div>
                            @if($index < count($steps) - 1)
                                <div class="flex-1 h-px mx-3 {{ $currentStep > ($index + 1) ? 'bg-emerald-500' : 'bg-[var(--wiq-border)]' }}"></div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-[var(--wiq-border)] p-6 sm:p-8">
                @if($currentStep === 2)
                    <div class="mb-6">
                        <button wire:click="goBack" class="text-[var(--wiq-muted)] hover:text-[var(--wiq-ink)] flex items-center gap-1 text-sm transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                            Back
                        </button>
                    </div>

                    <h2 class="wiq-serif text-xl font-medium mb-1">Choose date & time</h2>
                    <p class="text-[var(--wiq-muted)] text-sm mb-6">{{ $selectedType->name }} · {{ $selectedType->duration_minutes }} minutes</p>

                    <div class="grid md:grid-cols-2 gap-8">
                        <div>
                            <h3 class="font-medium mb-4 flex items-center gap-2 text-sm">
                                <svg class="w-4 h-4 text-[var(--wiq-muted)]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                Select date
                            </h3>

                            @php($calendarDates = array_column($availableDates, 'date'))
                            <div x-data="bookingCalendar(@js($calendarDates), '{{ $bookingSetting->brand_color ?? '#3B82F6' }}', @js($selectedDate))" class="bg-white border border-[var(--wiq-border)] rounded-xl overflow-hidden relative">
                                <div wire:loading wire:target="selectType" class="absolute inset-0 bg-white/80 backdrop-blur-sm z-10 flex items-center justify-center">
                                    <svg class="animate-spin h-6 w-6 wiq-accent-text" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </div>

                                <div class="flex items-center justify-between px-4 py-3 border-b border-[var(--wiq-border)]">
                                    <button @click="prevMonth()" :disabled="!canPrev" type="button" class="p-1.5 rounded-lg hover:bg-[var(--wiq-bg)] disabled:opacity-30 disabled:cursor-not-allowed">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                                    </button>
                                    <span class="text-sm font-medium" x-text="monthLabel"></span>
                                    <button @click="nextMonth()" :disabled="!canNext" type="button" class="p-1.5 rounded-lg hover:bg-[var(--wiq-bg)] disabled:opacity-30 disabled:cursor-not-allowed">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                    </button>
                                </div>

                                <div class="grid grid-cols-7 px-3 pt-3">
                                    <template x-for="h in ['Su','Mo','Tu','We','Th','Fr','Sa']">
                                        <div class="text-center text-[10px] font-medium text-[var(--wiq-muted)] pb-2" x-text="h"></div>
                                    </template>
                                </div>

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
                                                        'hover:bg-[var(--wiq-bg)] font-medium cursor-pointer': day.clickable && !day.selected,
                                                        'text-white font-semibold': day.selected,
                                                        'opacity-25 cursor-not-allowed': day.past,
                                                        'text-[var(--wiq-border)] cursor-not-allowed': !day.available && !day.past,
                                                    }"
                                                    class="w-9 h-9 rounded-full text-sm transition-all flex items-center justify-center"
                                                    x-text="day.num"
                                                ></button>
                                            </template>
                                        </div>
                                    </template>
                                </div>

                                @if(empty($availableDates))
                                    <p class="text-center pb-4 text-sm text-[var(--wiq-muted)]">No availability found for this service.</p>
                                @endif
                            </div>
                        </div>

                        <div>
                            <h3 class="font-medium mb-4 flex items-center gap-2 text-sm">
                                <svg class="w-4 h-4 text-[var(--wiq-muted)]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                @if($selectedDate)
                                    {{ \Carbon\Carbon::parse($selectedDate)->format('l, M j, Y') }}
                                @else
                                    Select time
                                @endif
                            </h3>

                            @if($selectedDate)
                                <div class="space-y-2 max-h-[400px] overflow-y-auto pr-2 custom-scrollbar relative">
                                    <div wire:loading wire:target="selectDate" class="absolute inset-0 bg-white/80 backdrop-blur-sm rounded-lg z-10 flex items-center justify-center">
                                        <svg class="animate-spin h-6 w-6 wiq-accent-text" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                    </div>
                                    @forelse($availableSlots as $slotInfo)
                                        <button
                                            wire:click="selectTime('{{ $slotInfo['time'] }}')"
                                            wire:loading.attr="disabled"
                                            wire:target="selectTime"
                                            wire:key="slot-{{ $slotInfo['time'] }}"
                                            type="button"
                                            class="w-full text-center px-4 py-2.5 border rounded-lg font-medium text-sm transition-all disabled:opacity-50 disabled:cursor-wait
                                            {{ $selectedTime === $slotInfo['time'] ? 'text-white' : 'border-[var(--wiq-border)] hover:border-[var(--wiq-ink)]' }}"
                                            style="{{ $selectedTime === $slotInfo['time'] ? 'background-color: var(--wiq-accent); border-color: var(--wiq-accent)' : '' }}"
                                        >
                                            {{ $slotInfo['formatted'] }}
                                        </button>
                                    @empty
                                        <div class="text-center py-10 text-[var(--wiq-muted)] border border-dashed border-[var(--wiq-border)] rounded-lg text-sm">
                                            No times available for this date
                                        </div>
                                    @endforelse
                                </div>
                            @else
                                <div class="text-center py-16 text-[var(--wiq-muted)] border border-dashed border-[var(--wiq-border)] rounded-lg text-sm">
                                    Please select a date first
                                </div>
                            @endif
                        </div>
                    </div>

                @elseif($currentStep == 2.5)
                    <div class="mb-6">
                        <button wire:click="goBack" class="text-[var(--wiq-muted)] hover:text-[var(--wiq-ink)] flex items-center gap-1 text-sm transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                            Back
                        </button>
                    </div>

                    <h2 class="wiq-serif text-xl font-medium mb-1">Select location</h2>
                    <p class="text-[var(--wiq-muted)] text-sm mb-6">
                        {{ $selectedType->name }} · {{ \Carbon\Carbon::parse($selectedDate . ' ' . $selectedTime)->format('F j, Y \a\t g:i A') }}
                    </p>

                    <div class="space-y-3 relative">
                        <div wire:loading wire:target="selectTime" class="absolute inset-0 bg-white/80 backdrop-blur-sm rounded-lg z-10 flex items-center justify-center">
                            <svg class="animate-spin h-8 w-8 wiq-accent-text" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </div>

                        @forelse($availableVenues as $venue)
                            <button
                                wire:click="selectVenue({{ $venue->id }})"
                                wire:loading.attr="disabled"
                                wire:target="selectVenue"
                                wire:key="venue-{{ $venue->id }}"
                                type="button"
                                class="w-full text-left p-4 border rounded-xl transition-all disabled:opacity-50 disabled:cursor-wait {{ $selectedVenueId == $venue->id ? '' : 'border-[var(--wiq-border)] hover:shadow-sm' }}"
                                style="{{ $selectedVenueId == $venue->id ? 'border-color: var(--wiq-accent); box-shadow: 0 0 0 1px var(--wiq-accent)' : '' }}"
                            >
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <h3 class="font-medium mb-1">{{ $venue->name }}</h3>
                                        @if($venue->full_address)
                                            <p class="text-[var(--wiq-muted)] text-sm mb-1">{{ $venue->full_address }}</p>
                                        @endif
                                        @if($venue->description)
                                            <p class="text-[var(--wiq-muted)] text-sm">{{ Str::limit($venue->description, 100) }}</p>
                                        @endif
                                        @if($venue->google_maps_url)
                                            <a href="{{ $venue->google_maps_url }}" target="_blank" onclick="event.stopPropagation();" class="inline-flex items-center gap-1 text-sm wiq-accent-text font-medium mt-1">
                                                View on map →
                                            </a>
                                        @endif
                                    </div>
                                    <svg class="w-5 h-5 text-[var(--wiq-muted)] flex-shrink-0 ml-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </div>
                            </button>
                        @empty
                            <div class="text-center py-10 text-[var(--wiq-muted)] border border-dashed border-[var(--wiq-border)] rounded-lg text-sm">
                                <p>No venues available for this time slot</p>
                                <button wire:click="goBack" class="mt-4 px-4 py-2 text-sm border border-[var(--wiq-border)] rounded-lg hover:bg-[var(--wiq-bg)] transition-colors">Go back</button>
                            </div>
                        @endforelse
                    </div>

                @elseif($currentStep === 3)
                    <div class="mb-6">
                        <button wire:click="goBack" class="text-[var(--wiq-muted)] hover:text-[var(--wiq-ink)] flex items-center gap-1 text-sm transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                            Back
                        </button>
                    </div>

                    <h2 class="wiq-serif text-xl font-medium mb-1">Your information</h2>
                    <p class="text-[var(--wiq-muted)] text-sm mb-6">
                        {{ \Carbon\Carbon::parse($selectedDate . ' ' . $selectedTime)->format('F j, Y \a\t g:i A') }}
                    </p>

                    <form wire:submit.prevent="submitBooking" class="space-y-5 relative">
                        <div wire:loading wire:target="submitBooking" class="absolute inset-0 bg-white/80 backdrop-blur-sm rounded-lg z-10 flex items-center justify-center">
                            <div class="text-center">
                                <svg class="animate-spin h-10 w-10 mx-auto mb-3 wiq-accent-text" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <p class="font-medium">Creating your appointment…</p>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2">Full name <span class="text-red-500">*</span></label>
                            <input type="text" wire:model="attendeeName" wire:loading.attr="disabled" wire:target="submitBooking"
                                class="w-full px-4 py-2.5 border border-[var(--wiq-border)] rounded-lg focus:outline-none focus:ring-2 wiq-accent-ring transition-all disabled:bg-[var(--wiq-bg)]"
                                placeholder="Jane Doe" required>
                            @error('attendeeName') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2">Email <span class="text-red-500">*</span></label>
                            <input type="email" wire:model="attendeeEmail" wire:loading.attr="disabled" wire:target="submitBooking"
                                class="w-full px-4 py-2.5 border border-[var(--wiq-border)] rounded-lg focus:outline-none focus:ring-2 wiq-accent-ring transition-all disabled:bg-[var(--wiq-bg)]"
                                placeholder="jane@example.com" required>
                            @error('attendeeEmail') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2">Phone number @if($selectedType->require_phone)<span class="text-red-500">*</span>@endif</label>
                            <input type="tel" wire:model="attendeePhone" wire:loading.attr="disabled" wire:target="submitBooking"
                                class="w-full px-4 py-2.5 border border-[var(--wiq-border)] rounded-lg focus:outline-none focus:ring-2 wiq-accent-ring transition-all disabled:bg-[var(--wiq-bg)]"
                                placeholder="+1 (555) 123-4567" {{ $selectedType->require_phone ? 'required' : '' }}>
                            @error('attendeePhone') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        @if($selectedType->require_company)
                            <div>
                                <label class="block text-sm font-medium mb-2">Company <span class="text-red-500">*</span></label>
                                <input type="text" wire:model="attendeeCompany" wire:loading.attr="disabled" wire:target="submitBooking"
                                    class="w-full px-4 py-2.5 border border-[var(--wiq-border)] rounded-lg focus:outline-none focus:ring-2 wiq-accent-ring transition-all disabled:bg-[var(--wiq-bg)]"
                                    placeholder="Acme Inc." required>
                                @error('attendeeCompany') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
                            </div>
                        @endif

                        @if(in_array($selectedType->appointment_format ?? 'online', ['in_person', 'hybrid']))
                            <div>
                                <label class="block text-sm font-medium mb-2">Location <span class="text-red-500">*</span></label>
                                <input type="text" wire:model="location" wire:loading.attr="disabled" wire:target="submitBooking"
                                    class="w-full px-4 py-2.5 border border-[var(--wiq-border)] rounded-lg focus:outline-none focus:ring-2 wiq-accent-ring transition-all disabled:bg-[var(--wiq-bg)]"
                                    placeholder="123 Main St, City, State, ZIP" required>
                                <p class="text-xs text-[var(--wiq-muted)] mt-1">Where should this appointment take place?</p>
                                @error('location') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
                            </div>
                        @endif

                        <div>
                            <label class="block text-sm font-medium mb-2">Additional notes</label>
                            <textarea wire:model="notes" wire:loading.attr="disabled" wire:target="submitBooking" rows="4"
                                class="w-full px-4 py-2.5 border border-[var(--wiq-border)] rounded-lg focus:outline-none focus:ring-2 wiq-accent-ring transition-all resize-none disabled:bg-[var(--wiq-bg)]"
                                placeholder="Anything we should know?"></textarea>
                            @error('notes') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <button type="submit" wire:loading.attr="disabled" wire:target="submitBooking"
                            class="w-full px-6 py-3.5 rounded-lg font-semibold wiq-btn-primary transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                            <span wire:loading.remove wire:target="submitBooking">Confirm booking</span>
                            <span wire:loading wire:target="submitBooking">Processing…</span>
                        </button>
                    </form>
                @endif
            </div>
        </div>
    @endif

    {{-- Global loading indicator --}}
    <div wire:loading wire:target="selectType,selectDate,selectTime,selectVenue" class="fixed top-4 right-4 z-50">
        <div class="bg-white rounded-lg px-4 py-3 shadow-lg flex items-center gap-3 border border-[var(--wiq-border)]">
            <svg class="animate-spin h-4 w-4 wiq-accent-text" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span class="text-sm font-medium">Loading…</span>
        </div>
    </div>
</div>
