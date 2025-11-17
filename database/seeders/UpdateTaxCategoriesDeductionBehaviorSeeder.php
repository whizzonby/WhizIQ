<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UpdateTaxCategoriesDeductionBehaviorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Maps existing tax categories to their deduction behavior based on
     * universal tax rules (always, requires_confirmation, never)
     */
    public function run(): void
    {
        // Map categories to their deduction behavior
        $categoryBehaviors = [
            // TABLE 1 - Always Deductible (100% in almost every country)
            'advertising' => 'always', // Advertising & Marketing
            'office_supplies' => 'always', // Office Supplies
            'software' => 'always', // Software & Subscriptions (business software)
            'rent_utilities' => 'always', // Rent & Utilities
            'professional_services' => 'always', // Professional Services
            'insurance' => 'always', // Business Insurance
            'salaries' => 'always', // Salaries & Wages
            'contract_labor' => 'always', // Contract Labor / Contractor Payments
            'education' => 'always', // Education & Training
            'bank_fees' => 'always', // Bank Fees & Interest
            'depreciation' => 'always', // Depreciation (repairs & maintenance)
            'other' => 'always', // Other Business Expenses (assuming business-related)
            
            // TABLE 2 - Requires Confirmation (user must confirm business use)
            'meals' => 'requires_confirmation', // Meals & Entertainment
            'travel' => 'requires_confirmation', // Travel (local or international)
            'vehicle' => 'requires_confirmation', // Vehicle & Transportation (fuel, maintenance, insurance)
        ];

        // Update each category
        foreach ($categoryBehaviors as $slug => $behavior) {
            DB::table('tax_categories')
                ->where('slug', $slug)
                ->update(['deduction_behavior' => $behavior]);
        }

        // Set default for any categories not in the map (safety fallback)
        DB::table('tax_categories')
            ->whereNull('deduction_behavior')
            ->update(['deduction_behavior' => 'requires_confirmation']);

        $this->command->info('Tax categories deduction behavior updated successfully.');
    }
}
