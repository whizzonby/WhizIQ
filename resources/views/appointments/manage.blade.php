<x-layouts.focus>
    <x-slot name="left">
        <div class="flex h-full items-center justify-center px-4 py-8">
            <div class="w-full max-w-xl rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                <h1 class="text-2xl font-semibold text-gray-950">
                    Manage appointment
                </h1>

                @if(session('success'))
                    <div class="mt-4 rounded-lg border border-green-200 bg-green-50 p-3 text-sm text-green-800">
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="mt-4 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-800">
                        {{ session('error') }}
                    </div>
                @endif

                <dl class="mt-6 space-y-4 text-sm">
                    <div>
                        <dt class="font-medium text-gray-500">Service</dt>
                        <dd class="mt-1 text-gray-950">{{ $appointment->appointmentType?->name ?? $appointment->title }}</dd>
                    </div>

                    <div>
                        <dt class="font-medium text-gray-500">Date and time</dt>
                        <dd class="mt-1 text-gray-950">
                            {{ $appointment->start_datetime->format('l, F j, Y \a\t g:i A') }}
                        </dd>
                    </div>

                    <div>
                        <dt class="font-medium text-gray-500">Status</dt>
                        <dd class="mt-1 text-gray-950">{{ $appointment->status_label }}</dd>
                    </div>
                </dl>

                <div class="mt-6 flex flex-col gap-3 sm:flex-row">
                    <a href="{{ route('appointment.calendar.download', ['token' => $appointment->confirmation_token]) }}"
                       class="inline-flex items-center justify-center rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                        Add to calendar
                    </a>

                    @if($canCancel && $bookingUrl)
                        <form method="POST" action="{{ route('appointment.reschedule', ['token' => $appointment->confirmation_token]) }}">
                            @csrf

                            <button type="submit" class="inline-flex w-full items-center justify-center rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                                Reschedule
                            </button>
                        </form>
                    @elseif($bookingUrl)
                        <a href="{{ $bookingUrl }}"
                           class="inline-flex items-center justify-center rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                            Book another time
                        </a>
                    @endif

                    @if($appointment->status === 'completed' && ! $appointment->businessReview)
                        <a href="{{ route('review.show', ['token' => $appointment->confirmation_token]) }}"
                           class="inline-flex items-center justify-center rounded-lg bg-gray-950 px-4 py-2 text-sm font-medium text-white hover:bg-gray-800">
                            Leave a review
                        </a>
                    @endif
                </div>

                @if($canCancel)
                    <form method="POST" action="{{ route('appointment.cancel', ['token' => $appointment->confirmation_token]) }}" class="mt-6 border-t border-gray-200 pt-6">
                        @csrf

                        <label for="reason" class="text-sm font-medium text-gray-700">Cancellation reason</label>
                        <textarea
                            id="reason"
                            name="reason"
                            rows="3"
                            class="mt-2 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"
                            placeholder="Optional"
                        ></textarea>

                        <button type="submit" class="mt-3 inline-flex items-center justify-center rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700">
                            Cancel appointment
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </x-slot>

    <x-slot name="right">
        <div class="flex h-full flex-col justify-center px-8 py-12">
            <h2 class="text-3xl font-semibold text-white">
                WhizzIQ
            </h2>
            <p class="mt-4 max-w-md text-white/80">
                Keep your booking details handy, add the event to your calendar, or cancel if you can no longer attend.
            </p>
        </div>
    </x-slot>
</x-layouts.focus>
