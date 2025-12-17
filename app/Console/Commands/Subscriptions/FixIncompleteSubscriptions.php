<?php

namespace App\Console\Commands\Subscriptions;

use App\Models\PaymentProvider;
use App\Models\Subscription;
use App\Services\PaymentProviders\PaymentService;
use App\Services\SubscriptionService;
use Illuminate\Console\Command;

class FixIncompleteSubscriptions extends Command
{
    public function __construct(
        private SubscriptionService $subscriptionService,
        private PaymentService $paymentService,
    ) {
        parent::__construct();
    }

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscription:fix-incomplete
                          {--user-id= : Fix subscriptions for specific user ID}
                          {--subscription-id= : Fix specific subscription ID}
                          {--force : Convert without confirmation}
                          {--dry-run : Show what would be fixed without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix incomplete LOCAL subscriptions that should be converted to PAYMENT_PROVIDER_MANAGED';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🔍 Finding incomplete subscriptions...');
        $this->newLine();

        $query = Subscription::with(['user', 'plan', 'plan.product'])
            ->where('type', \App\Constants\SubscriptionType::LOCALLY_MANAGED)
            ->whereNull('payment_provider_id')
            ->where('status', \App\Constants\SubscriptionStatus::ACTIVE->value);

        // Filter by user ID if provided
        if ($userId = $this->option('user-id')) {
            $query->where('user_id', $userId);
        }

        // Filter by subscription ID if provided
        if ($subscriptionId = $this->option('subscription-id')) {
            $query->where('id', $subscriptionId);
        }

        $subscriptions = $query->get();

        if ($subscriptions->isEmpty()) {
            $this->info('✅ No incomplete subscriptions found!');
            return self::SUCCESS;
        }

        $this->info("Found {$subscriptions->count()} incomplete subscription(s):");
        $this->newLine();

        // Display subscriptions
        $this->displaySubscriptions($subscriptions);

        if ($this->option('dry-run')) {
            $this->warn('🔍 DRY RUN MODE - No changes will be made');
            return self::SUCCESS;
        }

        // Confirm before proceeding
        if (!$this->option('force')) {
            if (!$this->confirm('Do you want to convert these subscriptions?', true)) {
                $this->info('Cancelled.');
                return self::SUCCESS;
            }
        }

        $this->newLine();
        $this->info('🔄 Converting subscriptions...');
        $this->newLine();

        $converted = 0;
        $failed = 0;

        foreach ($subscriptions as $subscription) {
            if ($this->convertSubscription($subscription)) {
                $converted++;
                $this->info("✅ Converted subscription #{$subscription->id} (User: {$subscription->user->email})");
            } else {
                $failed++;
                $this->error("❌ Failed to convert subscription #{$subscription->id} (User: {$subscription->user->email})");
            }
        }

        $this->newLine();
        $this->line("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
        $this->info("📊 SUMMARY");
        $this->line("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
        $this->info("✅ Converted: {$converted}");
        if ($failed > 0) {
            $this->error("❌ Failed: {$failed}");
        }

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }

    /**
     * Display subscriptions in a table
     */
    private function displaySubscriptions($subscriptions): void
    {
        $data = $subscriptions->map(function ($subscription) {
            return [
                'ID' => $subscription->id,
                'User' => $subscription->user->email ?? "User #{$subscription->user_id}",
                'Plan' => $subscription->plan->name ?? "Plan #{$subscription->plan_id}",
                'Status' => $subscription->status,
                'Created' => $subscription->created_at->format('Y-m-d H:i'),
                'Ends At' => $subscription->ends_at ? $subscription->ends_at->format('Y-m-d H:i') : 'N/A',
            ];
        })->toArray();

        $this->table(
            ['ID', 'User', 'Plan', 'Status', 'Created', 'Ends At'],
            $data
        );
    }

    /**
     * Convert a subscription
     */
    private function convertSubscription(Subscription $subscription): bool
    {
        try {
            // Check if subscription has a plan
            if (!$subscription->plan) {
                $this->error("   Subscription #{$subscription->id} has no plan - skipping");
                return false;
            }

            // Get payment provider from plan's available providers
            $paymentProviders = $this->paymentService->getActivePaymentProvidersForPlan(
                $subscription->plan,
                true, // shouldSupportSkippingTrial
                true  // isNewPayment
            );

            if (empty($paymentProviders)) {
                $this->error("   No payment providers available for plan '{$subscription->plan->slug}' - skipping");
                return false;
            }

            // Get the payment provider model (first available)
            $paymentProviderInterface = $paymentProviders[0];
            $paymentProvider = PaymentProvider::where('slug', $paymentProviderInterface->getSlug())
                ->where('is_active', true)
                ->first();

            if (!$paymentProvider) {
                $this->error("   Payment provider '{$paymentProviderInterface->getSlug()}' not found in database - skipping");
                return false;
            }

            // Convert the subscription
            return $this->subscriptionService->convertLocalSubscriptionToPaymentProvider(
                $subscription,
                $paymentProvider
            );
        } catch (\Exception $e) {
            $this->error("   Error: {$e->getMessage()}");
            \Log::error('Failed to convert subscription in FixIncompleteSubscriptions command', [
                'subscription_id' => $subscription->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return false;
        }
    }
}











