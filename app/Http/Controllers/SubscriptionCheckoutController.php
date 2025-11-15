<?php

namespace App\Http\Controllers;

use App\Constants\SubscriptionType;
use App\Exceptions\SubscriptionCreationNotAllowedException;
use App\Models\PaymentProvider;
use App\Models\Plan;
use App\Services\CalculationService;
use App\Services\DiscountService;
use App\Services\PaymentProviders\PaymentService;
use App\Services\SessionService;
use App\Services\SubscriptionService;

class SubscriptionCheckoutController extends Controller
{
    public function __construct(
        private DiscountService $discountService,
        private CalculationService $calculationService,
        private SubscriptionService $subscriptionService,
        private SessionService $sessionService,
        private PaymentService $paymentService,
    ) {}

    public function subscriptionCheckout(string $planSlug)
    {
        $plan = Plan::where('slug', $planSlug)->where('is_active', true)->firstOrFail();
        $checkoutDto = $this->sessionService->getSubscriptionCheckoutDto();

        $user = auth()->user();

        if ($user && ! $this->subscriptionService->canCreateSubscription($user->id)) {
            throw new SubscriptionCreationNotAllowedException(__('You already have subscription.'));
        }

        if ($checkoutDto->planSlug !== $planSlug) {
            $checkoutDto = $this->sessionService->resetSubscriptionCheckoutDto();
        }

        $checkoutDto->planSlug = $planSlug;

        $this->sessionService->saveSubscriptionCheckoutDto($checkoutDto);

        if ($plan->has_trial &&
            config('app.trial_without_payment.enabled') &&
            $this->subscriptionService->canUserHaveSubscriptionTrial($user)
        ) {
            return view('checkout.local-subscription');
        }

        return view('checkout.subscription');
    }

    public function convertLocalSubscriptionCheckout(?string $subscriptionUuid = null)
    {
        $subscription = $this->subscriptionService->findByUuidOrFail($subscriptionUuid);

        if (! $this->subscriptionService->isLocalSubscription($subscription)) {
            return redirect()->route('home');
        }

        $planSlug = $subscription->plan->slug;
        $plan = Plan::where('slug', $planSlug)->where('is_active', true)->firstOrFail();

        $checkoutDto = $this->sessionService->getSubscriptionCheckoutDto();

        if ($checkoutDto->planSlug !== $planSlug) {
            $checkoutDto = $this->sessionService->resetSubscriptionCheckoutDto();
        }

        $checkoutDto->planSlug = $planSlug;
        $checkoutDto->subscriptionId = $subscription->id;

        $this->sessionService->saveSubscriptionCheckoutDto($checkoutDto);

        $totals = $this->calculationService->calculatePlanTotals(
            auth()->user(),
            $planSlug,
            $checkoutDto?->discountCode,
        );

        return view('checkout.convert-local-subscription', [
            'plan' => $plan,
            'totals' => $totals,
            'checkoutDto' => $checkoutDto,
        ]);
    }

    public function subscriptionCheckoutSuccess()
    {
        $result = $this->handleSubscriptionSuccess();

        if (! $result) {
            return redirect()->route('home');
        }

        $checkoutDto = $this->sessionService->getSubscriptionCheckoutDto();
        $subscription = $this->subscriptionService->findById($checkoutDto->subscriptionId);

        $this->sessionService->resetSubscriptionCheckoutDto();

        if ($subscription && $subscription->type === SubscriptionType::LOCALLY_MANAGED) {
            return view('checkout.local-subscription-thank-you');
        }

        return view('checkout.subscription-thank-you');
    }

    public function convertLocalSubscriptionCheckoutSuccess()
    {
        $result = $this->handleSubscriptionSuccess();

        if (! $result) {
            return redirect()->route('home');
        }

        $this->sessionService->resetSubscriptionCheckoutDto();

        return view('checkout.convert-local-subscription-thank-you');
    }

    private function handleSubscriptionSuccess(): bool
    {
        $checkoutDto = $this->sessionService->getSubscriptionCheckoutDto();

        if ($checkoutDto->subscriptionId === null) {
            return false;
        }

        $subscription = $this->subscriptionService->findById($checkoutDto->subscriptionId);

        if (! $subscription) {
            \Log::error('Subscription not found in handleSubscriptionSuccess', [
                'subscription_id' => $checkoutDto->subscriptionId,
            ]);
            return false;
        }

        // Handle LOCAL subscription conversion (trial subscriptions)
        if ($this->subscriptionService->isLocalSubscription($subscription)) {
            // Get payment provider from plan's available providers
            if (! $subscription->plan) {
                \Log::error('Cannot convert subscription: missing plan', [
                    'subscription_id' => $subscription->id,
                ]);
                return false;
            }

            $paymentProviders = $this->paymentService->getActivePaymentProvidersForPlan(
                $subscription->plan,
                true, // shouldSupportSkippingTrial
                true  // isNewPayment
            );

            if (empty($paymentProviders)) {
                \Log::error('No payment providers available for subscription conversion', [
                    'subscription_id' => $subscription->id,
                    'plan_id' => $subscription->plan->id,
                ]);
                return false;
            }

            // Get the payment provider model (first available)
            $paymentProviderInterface = $paymentProviders[0];
            $paymentProvider = PaymentProvider::where('slug', $paymentProviderInterface->getSlug())
                ->where('is_active', true)
                ->first();

            if (! $paymentProvider) {
                \Log::error('Payment provider not found in database', [
                    'slug' => $paymentProviderInterface->getSlug(),
                ]);
                return false;
            }

            // Convert LOCAL subscription to PAYMENT_PROVIDER_MANAGED
            $this->subscriptionService->convertLocalSubscriptionToPaymentProvider($subscription, $paymentProvider);
        } else {
            // For NEW subscriptions that are already PAYMENT_PROVIDER_MANAGED, set as PENDING
            $this->subscriptionService->setAsPending($checkoutDto->subscriptionId);
        }

        $this->subscriptionService->updateUserSubscriptionTrials($checkoutDto->subscriptionId);

        if ($checkoutDto->discountCode !== null) {
            $this->discountService->redeemCodeForSubscription($checkoutDto->discountCode, auth()->user(), $checkoutDto->subscriptionId);
        }

        return true;
    }
}
