<?php

use App\Models\Product;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $metadata = $this->lockedMetadata();

        Product::where('is_default', true)->update(['is_default' => false]);

        Product::updateOrCreate(
            ['slug' => 'no-active-plan'],
            [
                'name' => 'No Active Plan',
                'description' => 'Fallback tier for users with no active subscription. This product has no purchasable plans.',
                'features' => [],
                'is_popular' => false,
                'is_default' => true,
                'metadata' => $metadata,
            ]
        );
    }

    public function down(): void
    {
        Product::where('slug', 'no-active-plan')->update(['is_default' => false]);
        Product::where('slug', 'starter')->update(['is_default' => true]);
    }

    private function lockedMetadata(): array
    {
        $jsonPath = base_path('PLAN_METADATA_CONFIGURATIONS.json');

        if (file_exists($jsonPath)) {
            $config = json_decode(file_get_contents($jsonPath), true);

            if (is_array($config) && is_array($config['LOCKED_TIER_METADATA'] ?? null)) {
                return $config['LOCKED_TIER_METADATA'];
            }
        }

        return [
            'crm_contacts_limit' => '0',
            'crm_deals_limit' => '0',
            'finance_invoices_limit' => '0',
            'finance_templates_limit' => '0',
            'appointments_limit' => '0',
            'appointments_types_limit' => '0',
            'email_templates_limit' => '0',
            'email_campaigns_limit' => '0',
            'documents_storage_gb' => '0',
            'passwords_limit' => '0',
            'tasks_limit' => '0',
            'analytics_widgets_limit' => '0',
            'ai_daily_limit' => '0',
            'support_level' => 'none',
        ];
    }
};
