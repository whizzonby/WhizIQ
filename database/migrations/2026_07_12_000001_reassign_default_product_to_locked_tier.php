<?php

use App\Models\Product;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * The paid Starter product was wrongly flagged as the "default" product
     * (the fallback metadata handed to any user with zero active
     * subscriptions). That meant anyone who never subscribed, or whose
     * card-less trial lapsed, kept full paid-tier access forever for free.
     * Reassign "default" to a genuinely locked, non-purchasable product.
     */
    public function up(): void
    {
        $jsonPath = base_path('PLAN_METADATA_CONFIGURATIONS.json');

        if (! file_exists($jsonPath)) {
            return;
        }

        $config = json_decode(file_get_contents($jsonPath), true);
        $lockedMetadata = $config['LOCKED_TIER_METADATA'] ?? [];

        if (empty($lockedMetadata)) {
            return;
        }

        DB::transaction(function () use ($lockedMetadata) {
            Product::where('is_default', true)->update(['is_default' => false]);

            Product::updateOrCreate(
                ['slug' => 'no-active-plan'],
                [
                    'name' => 'No Active Plan',
                    'description' => 'Fallback tier for users with no active subscription. Read-only access until a plan is chosen. This product intentionally has no purchasable plan attached.',
                    'features' => [],
                    'is_popular' => false,
                    'is_default' => true,
                    'metadata' => $lockedMetadata,
                ]
            );
        });
    }

    public function down(): void
    {
        DB::transaction(function () {
            Product::where('slug', 'no-active-plan')->update(['is_default' => false]);
            Product::where('slug', 'starter')->update(['is_default' => true]);
        });
    }
};
