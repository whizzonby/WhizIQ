<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Leave a Review</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gray-50 text-gray-900">
    <main class="mx-auto flex min-h-screen w-full max-w-2xl items-center px-4 py-10">
        <section class="w-full rounded-lg bg-white p-6 shadow-sm ring-1 ring-gray-200 sm:p-8">
            @if(session('success'))
                <div class="mb-6 rounded-md bg-green-50 px-4 py-3 text-sm font-medium text-green-800">
                    {{ session('success') }}
                </div>
            @endif

            <p class="text-sm font-semibold text-gray-500">
                {{ $appointment->user?->bookingSetting?->display_name ?? $appointment->user?->name }}
            </p>
            <h1 class="mt-2 text-2xl font-bold text-gray-950">How was your appointment?</h1>
            <p class="mt-2 text-sm text-gray-600">
                {{ $appointment->appointmentType?->name ?? $appointment->title }}
                on {{ $appointment->start_datetime->format('F j, Y') }}
            </p>

            @if($existingReview)
                <div class="mt-8 rounded-lg bg-gray-50 p-5">
                    <div class="text-lg font-semibold text-gray-950">
                        {{ str_repeat('*', $existingReview->rating) }}
                    </div>
                    @if($existingReview->comment)
                        <p class="mt-3 text-gray-700">{{ $existingReview->comment }}</p>
                    @endif
                    <p class="mt-4 text-sm text-gray-500">Your review has been received. Thank you.</p>
                </div>
            @else
                <form method="POST" action="{{ route('review.store', ['token' => $appointment->confirmation_token]) }}" class="mt-8 space-y-6">
                    @csrf

                    <div>
                        <label for="rating" class="block text-sm font-medium text-gray-700">Rating</label>
                        <select id="rating" name="rating" required class="mt-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                            <option value="">Select a rating</option>
                            <option value="5" @selected(old('rating') === '5')>5 stars - Excellent</option>
                            <option value="4" @selected(old('rating') === '4')>4 stars - Good</option>
                            <option value="3" @selected(old('rating') === '3')>3 stars - Okay</option>
                            <option value="2" @selected(old('rating') === '2')>2 stars - Poor</option>
                            <option value="1" @selected(old('rating') === '1')>1 star - Very poor</option>
                        </select>
                        @error('rating')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="display_name" class="block text-sm font-medium text-gray-700">Display name</label>
                        <input id="display_name" name="display_name" value="{{ old('display_name', $appointment->attendee_name) }}" maxlength="120" class="mt-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                        @error('display_name')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="comment" class="block text-sm font-medium text-gray-700">Comment</label>
                        <textarea id="comment" name="comment" rows="5" maxlength="2000" class="mt-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">{{ old('comment') }}</textarea>
                        @error('comment')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit" class="inline-flex w-full items-center justify-center rounded-md bg-gray-950 px-4 py-3 text-sm font-semibold text-white shadow-sm hover:bg-gray-800">
                        Submit review
                    </button>
                </form>
            @endif
        </section>
    </main>
</body>
</html>
