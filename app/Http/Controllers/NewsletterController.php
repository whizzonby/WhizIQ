<?php

namespace App\Http\Controllers;

use App\Models\NewsletterSubscriber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class NewsletterController extends Controller
{
    public function subscribe(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|max:255',
        ]);

        if ($validator->fails()) {
            return back()->with('error', 'Please enter a valid email address.');
        }

        $email = $request->input('email');

        // Check if email already exists
        $existing = NewsletterSubscriber::where('email', $email)->first();

        if ($existing) {
            if ($existing->is_active) {
                return back()->with('info', 'You are already subscribed to our newsletter!');
            } else {
                // Reactivate subscription
                $existing->resubscribe();
                return back()->with('success', 'Welcome back! Your newsletter subscription has been reactivated.');
            }
        }

        // Create new subscription
        NewsletterSubscriber::create([
            'email' => $email,
            'is_active' => true,
            'subscribed_at' => now(),
        ]);

        return back()->with('success', 'Thank you for subscribing to our newsletter!');
    }

    public function unsubscribe(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return back()->with('error', 'Please enter a valid email address.');
        }

        $subscriber = NewsletterSubscriber::where('email', $request->input('email'))->first();

        if (!$subscriber) {
            return back()->with('error', 'Email not found in our newsletter list.');
        }

        if (!$subscriber->is_active) {
            return back()->with('info', 'You are already unsubscribed from our newsletter.');
        }

        $subscriber->unsubscribe();

        return back()->with('success', 'You have been successfully unsubscribed from our newsletter.');
    }
}
