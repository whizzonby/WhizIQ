<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class UpdatePlanFeaturesSeeder extends Seeder
{
    /**
     * Update plan features to accurately reflect what each plan offers.
     * This only updates the 'features' field - no other changes.
     */
    public function run(): void
    {
        $this->command->info('🔄 Updating plan features...');

        // Starter Plan Features
        $starterFeatures = [
            '500 Contacts',
            '25 Deals',
            '100 Tasks',
            '50 Monthly Invoices',
            '50 Passwords',
            '50 Appointments/month',
            '5 Email Templates',
            '1GB Document Storage',
            '20 AI Requests/day',
            'Standard Support',
        ];

        // Pro Plan Features
        $proFeatures = [
            'Unlimited Contacts & Deals',
            'Unlimited Tasks & Invoices',
            '200 Passwords',
            'Contact Segmentation',
            'Tax Management',
            'Goals & OKR Tracking',
            'Calendar & Zoom Integration',
            'Import/Export Data',
            '5GB Document Storage',
            '75 AI Requests/day',
            'Priority Support',
        ];

        // Premium Plan Features
        $premiumFeatures = [
            'Everything in Pro, plus:',
            'Unlimited Storage & Passwords',
            '18 AI-Powered Features',
            'AI Email Generation',
            'AI Document Analysis',
            'AI Task Extraction',
            'SWOT & Risk Analysis',
            'Revenue Forecasting',
            'Tax Optimization AI',
            'QuickBooks/Xero Integration',
            '200 AI Requests/day',
            'Premium Support (4-hour response)',
        ];

        // Update Starter
        $starter = Product::where('slug', 'starter')->first();
        if ($starter) {
            $starter->update(['features' => $starterFeatures]);
            $this->command->info('✓ Updated Starter plan features (' . count($starterFeatures) . ' features)');
        } else {
            $this->command->warn('⚠ Starter plan not found');
        }

        // Update Pro
        $pro = Product::where('slug', 'pro')->first();
        if ($pro) {
            $pro->update(['features' => $proFeatures]);
            $this->command->info('✓ Updated Pro plan features (' . count($proFeatures) . ' features)');
        } else {
            $this->command->warn('⚠ Pro plan not found');
        }

        // Update Premium
        $premium = Product::where('slug', 'premium')->first();
        if ($premium) {
            $premium->update(['features' => $premiumFeatures]);
            $this->command->info('✓ Updated Premium plan features (' . count($premiumFeatures) . ' features)');
        } else {
            $this->command->warn('⚠ Premium plan not found');
        }

        $this->command->info('');
        $this->command->info('✅ Plan features updated successfully!');
        $this->command->info('💡 Features will now display correctly on the homepage pricing section.');
    }
}
