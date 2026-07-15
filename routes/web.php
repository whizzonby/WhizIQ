<?php

use App\Http\Controllers\Auth\OAuthController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\CampaignDigestActionController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PaymentProviders\PaddleController;
use App\Http\Controllers\RoadmapController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
| If you want the URL to be added to the sitemap, add a "sitemapped" middleware to the route (it has to GET route)
|
*/

Route::get('/', function () {
    return view('home');
})->name('home')->middleware('sitemapped');

Auth::routes();

Route::get('/email/verify', function () {
    return view('auth.verify');
})->middleware('auth')->name('verification.notice');

Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();

    $user = $request->user();
    if ($user->hasVerifiedEmail()) {
        if (! $user->hasCompletedBusinessSetup()) {
            return redirect()->route('filament.dashboard.pages.onboarding');
        }

        if (! $user->isSubscribed() && ! $user->isTrialing()) {
            return redirect()->to(\App\Filament\Dashboard\Resources\Subscriptions\SubscriptionResource::getUrl('index'));
        }

        return redirect()->route('filament.dashboard.pages.dashboard');
    }

    return redirect('/');
})->middleware(['auth', 'signed'])->name('verification.verify');

Route::get('/phone/verify', function () {
    return view('verify.sms-verification');
})->name('user.phone-verify')
    ->middleware('auth');

Route::get('/phone/verified', function () {
    return view('verify.sms-verification-success');
})->name('user.phone-verified')
    ->middleware('auth');

Route::post('/email/verification-notification', function (\Illuminate\Http\Request $request) {
    $request->user()->sendEmailVerificationNotification();

    return back()->with('sent');
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');

Route::get('/registration/thank-you', function () {
    return view('auth.thank-you');
})->middleware(['auth'])->name('registration.thank-you');

Route::middleware(['auth', 'signed', 'throttle:10,1'])
    ->prefix('/campaign-digest')
    ->name('campaign-digest.')
    ->group(function () {
        Route::get('/draft/{campaign}/review', [CampaignDigestActionController::class, 'reviewDraft'])
            ->name('draft.review');
        Route::get('/draft/{campaign}/segments/{segment}/improve', [CampaignDigestActionController::class, 'improveWeakSegment'])
            ->name('segment.improve');
        Route::get('/audiences/{audience}/snooze', [CampaignDigestActionController::class, 'snoozeRecommendation'])
            ->name('audience.snooze');
    });

Route::get('/auth/{provider}/redirect', [OAuthController::class, 'redirect'])
    ->where('provider', 'google|github|facebook|twitter-oauth-2|linkedin-openid|bitbucket|gitlab')
    ->name('auth.oauth.redirect');

Route::get('/auth/{provider}/callback', [OAuthController::class, 'callback'])
    ->where('provider', 'google|github|facebook|twitter-oauth-2|linkedin-openid|bitbucket|gitlab')
    ->name('auth.oauth.callback');

Route::get('/checkout/plan/{planSlug}', [
    App\Http\Controllers\SubscriptionCheckoutController::class,
    'subscriptionCheckout',
])->name('checkout.subscription');

Route::get('/checkout/convert-subscription/{subscriptionUuid}', [
    App\Http\Controllers\SubscriptionCheckoutController::class,
    'convertLocalSubscriptionCheckout',
])->name('checkout.convert-local-subscription');

Route::get('/already-subscribed', function () {
    return view('checkout.already-subscribed');
})->name('checkout.subscription.already-subscribed');

Route::get('/checkout/subscription/success', [
    App\Http\Controllers\SubscriptionCheckoutController::class,
    'subscriptionCheckoutSuccess',
])->name('checkout.subscription.success')->middleware('auth');

Route::get('/checkout/convert-subscription-success', [
    App\Http\Controllers\SubscriptionCheckoutController::class,
    'convertLocalSubscriptionCheckoutSuccess',
])->name('checkout.convert-local-subscription.success')->middleware('auth');

Route::get('/payment-provider/paddle/payment-link', [
    PaddleController::class,
    'paymentLink',
])->name('payment-link.paddle');

Route::get('/subscription/{subscriptionUuid}/change-plan/{planSlug}', [
    App\Http\Controllers\SubscriptionController::class,
    'changePlan',
])->name('subscription.change-plan')->middleware('auth');

Route::post('/subscription/{subscriptionUuid}/change-plan/{planSlug}', [
    App\Http\Controllers\SubscriptionController::class,
    'changePlan',
])->name('subscription.change-plan.post')->middleware('auth');

Route::get('/subscription/change-plan-thank-you', [
    App\Http\Controllers\SubscriptionController::class,
    'success',
])->name('subscription.change-plan.thank-you')->middleware('auth');

// blog
Route::controller(BlogController::class)
    ->prefix('/blog')
    ->group(function () {
        Route::get('/', 'all')->name('blog')->middleware('sitemapped');
        Route::get('/category/{slug}', 'category')->name('blog.category');
        Route::get('/{slug}', 'view')->name('blog.view');
    });

// Newsletter subscription
Route::post('/newsletter/subscribe', [App\Http\Controllers\NewsletterController::class, 'subscribe'])->name('newsletter.subscribe');
Route::post('/newsletter/unsubscribe', [App\Http\Controllers\NewsletterController::class, 'unsubscribe'])->name('newsletter.unsubscribe');

// Dynamic page routes (Policy pages, etc.)
// Old static routes kept as comments for reference:
// Route::get('/terms-of-service', function () { return view('pages.terms-of-service'); });
// Route::get('/privacy-policy', function () { return view('pages.privacy-policy'); });

Route::get('/terms-of-service', [PageController::class, 'show'])
    ->defaults('slug', 'terms-of-service')
    ->name('terms-of-service')
    ->middleware('sitemapped');

Route::get('/privacy-policy', [PageController::class, 'show'])
    ->defaults('slug', 'privacy-policy')
    ->name('privacy-policy')
    ->middleware('sitemapped');

// Product checkout routes

Route::get('/buy/product/{productSlug}/{quantity?}', [
    App\Http\Controllers\ProductCheckoutController::class,
    'addToCart',
])->name('buy.product');

Route::get('/cart/clear', [
    App\Http\Controllers\ProductCheckoutController::class,
    'clearCart',
])->name('cart.clear');

Route::get('/checkout/product', [
    App\Http\Controllers\ProductCheckoutController::class,
    'productCheckout',
])->name('checkout.product');

Route::get('/checkout/product/success', [
    App\Http\Controllers\ProductCheckoutController::class,
    'productCheckoutSuccess',
])->name('checkout.product.success')->middleware('auth');

// roadmap

Route::controller(RoadmapController::class)
    ->prefix('/roadmap')
    ->group(function () {
        Route::get('/', 'index')->name('roadmap');
        Route::get('/i/{itemSlug}', 'viewItem')->name('roadmap.viewItem');
        Route::get('/suggest', 'suggest')->name('roadmap.suggest')->middleware('auth');
    });

// Invoice

Route::controller(InvoiceController::class)
    ->prefix('/invoice')
    ->group(function () {
        Route::get('/generate/{transactionUuid}', 'generate')->name('invoice.generate');
        Route::get('/preview', 'preview')->name('invoice.preview');
        Route::get('/{invoice}/pay', 'payClientInvoice')->name('invoice.client.pay')->middleware('signed');
        Route::get('/{invoice}/payment/{status}', 'clientInvoicePaymentStatus')
            ->whereIn('status', ['success', 'cancelled', 'paid', 'unavailable'])
            ->name('invoice.client.payment-status')
            ->middleware('signed');
    });

// Public Booking Page

// Service Detail Page (must come before general booking route)
Route::get('/book/{slug}/service/{serviceId}', \App\Livewire\ServiceDetail::class)->name('booking.service.detail');

Route::get('/book/{slug}', \App\Livewire\PublicBooking::class)->name('booking.public');
Route::post('/book/{slug}', \App\Http\Controllers\PublicBookingSubmissionController::class)->name('booking.public.submit');


// Appointment Calendar Download

Route::get('/appointment/{token}/calendar.ics', [
    App\Http\Controllers\AppointmentCalendarController::class,
    'downloadICS',
])->name('appointment.calendar.download');

Route::get('/appointment/{token}', [
    App\Http\Controllers\PublicAppointmentController::class,
    'manage',
])->name('appointment.manage');

Route::post('/appointment/{token}/cancel', [
    App\Http\Controllers\PublicAppointmentController::class,
    'cancel',
])->name('appointment.cancel');

Route::post('/appointment/{token}/reschedule', [
    App\Http\Controllers\PublicAppointmentController::class,
    'reschedule',
])->name('appointment.reschedule');

Route::get('/review/{token}', [
    App\Http\Controllers\PublicReviewController::class,
    'show',
])->name('review.show');

Route::post('/review/{token}', [
    App\Http\Controllers\PublicReviewController::class,
    'store',
])->name('review.store');

// ClientInvoice PDF Download
Route::get('/invoice/{invoice}/download-pdf', [
    App\Http\Controllers\InvoiceController::class,
    'downloadClientInvoicePDF',
])->name('invoice.download-pdf')->middleware('auth');

// Quote PDF Download
Route::get('/quote/{quote}/download-pdf', [
    App\Http\Controllers\QuoteController::class,
    'downloadPDF',
])->name('quote.download-pdf')->middleware('auth');

// Receipt PDF Download
Route::get('/receipt/{receipt}/download-pdf', [
    App\Http\Controllers\ReceiptController::class,
    'downloadPDF',
])->name('receipt.download-pdf')->middleware('auth');

// Expense Export Download
Route::get('/expenses/export', [
    App\Http\Controllers\ExpenseExportController::class,
    'download',
])->name('expenses.export')->middleware('auth');

// Revenue Source Export Download
Route::get('/revenue-sources/export', [
    App\Http\Controllers\RevenueSourceExportController::class,
    'download',
])->name('revenue-sources.export')->middleware('auth');

// Dynamic catch-all route for pages (must be last to avoid conflicts)
Route::get('/{slug}', [PageController::class, 'show'])
    ->name('page.show')
    ->middleware('sitemapped');
