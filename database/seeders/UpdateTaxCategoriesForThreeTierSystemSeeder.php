<?php

namespace Database\Seeders;

use App\Models\TaxCategory;
use Illuminate\Database\Seeder;

class UpdateTaxCategoriesForThreeTierSystemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * This seeder implements the complete 3-tier tax deduction system based on EXPENSES IDEA.md:
     * - TABLE 1: Always Deductible (auto tax-deductible)
     * - TABLE 2: Requires Confirmation (user confirms business use)
     * - TABLE 3: Never Deductible (cannot be tax-deductible)
     */
    public function run(): void
    {
        // ============================================
        // TABLE 1: ALWAYS DEDUCTIBLE (Auto Tax-Deductible)
        // ============================================

        $alwaysDeductible = [
            [
                'name' => 'Salaries & Wages',
                'slug' => 'salaries_wages',
                'description' => 'Employee and contractor payroll - universally deductible',
                'deduction_percentage' => 100.00,
                'deduction_behavior' => 'always',
                'confirmation_prompt' => null,
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Contractor Payments',
                'slug' => 'contractor_payments',
                'description' => 'Payments to independent contractors and freelancers',
                'deduction_percentage' => 100.00,
                'deduction_behavior' => 'always',
                'confirmation_prompt' => null,
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Rent & Lease',
                'slug' => 'rent_lease',
                'description' => 'Office, warehouse, or business premises rent/lease',
                'deduction_percentage' => 100.00,
                'deduction_behavior' => 'always',
                'confirmation_prompt' => null,
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Utilities',
                'slug' => 'utilities',
                'description' => 'Electricity, water, office phone, gas for business premises',
                'deduction_percentage' => 100.00,
                'deduction_behavior' => 'always',
                'confirmation_prompt' => null,
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'name' => 'Internet Services',
                'slug' => 'internet_services',
                'description' => 'Business internet and cloud services',
                'deduction_percentage' => 100.00,
                'deduction_behavior' => 'always',
                'confirmation_prompt' => null,
                'is_active' => true,
                'sort_order' => 5,
            ],
            [
                'name' => 'Office Supplies',
                'slug' => 'office_supplies',
                'description' => 'Pens, paper, printer ink, stationery, etc.',
                'deduction_percentage' => 100.00,
                'deduction_behavior' => 'always',
                'confirmation_prompt' => null,
                'is_active' => true,
                'sort_order' => 6,
            ],
            [
                'name' => 'Software Subscriptions',
                'slug' => 'software_subscriptions',
                'description' => 'SaaS tools, business software, licenses',
                'deduction_percentage' => 100.00,
                'deduction_behavior' => 'always',
                'confirmation_prompt' => null,
                'is_active' => true,
                'sort_order' => 7,
            ],
            [
                'name' => 'Advertising & Marketing',
                'slug' => 'advertising_marketing',
                'description' => 'Ads, social media boosts, SEO, marketing campaigns',
                'deduction_percentage' => 100.00,
                'deduction_behavior' => 'always',
                'confirmation_prompt' => null,
                'is_active' => true,
                'sort_order' => 8,
            ],
            [
                'name' => 'Professional Services',
                'slug' => 'professional_services',
                'description' => 'Legal, accounting, design, consulting fees',
                'deduction_percentage' => 100.00,
                'deduction_behavior' => 'always',
                'confirmation_prompt' => null,
                'is_active' => true,
                'sort_order' => 9,
            ],
            [
                'name' => 'Repairs & Maintenance',
                'slug' => 'repairs_maintenance',
                'description' => 'Fixing business equipment, tools, or premises',
                'deduction_percentage' => 100.00,
                'deduction_behavior' => 'always',
                'confirmation_prompt' => null,
                'is_active' => true,
                'sort_order' => 10,
            ],
            [
                'name' => 'Business Insurance',
                'slug' => 'business_insurance',
                'description' => 'Liability, cyber, equipment, professional indemnity insurance',
                'deduction_percentage' => 100.00,
                'deduction_behavior' => 'always',
                'confirmation_prompt' => null,
                'is_active' => true,
                'sort_order' => 11,
            ],
            [
                'name' => 'Training & Education',
                'slug' => 'training_education',
                'description' => 'Business-related courses, training, seminars',
                'deduction_percentage' => 100.00,
                'deduction_behavior' => 'always',
                'confirmation_prompt' => null,
                'is_active' => true,
                'sort_order' => 12,
            ],
            [
                'name' => 'Bank Fees',
                'slug' => 'bank_fees',
                'description' => 'Account maintenance, wire fees, payment processing fees',
                'deduction_percentage' => 100.00,
                'deduction_behavior' => 'always',
                'confirmation_prompt' => null,
                'is_active' => true,
                'sort_order' => 13,
            ],
            [
                'name' => 'Shipping & Delivery',
                'slug' => 'shipping_delivery',
                'description' => 'Logistics, client delivery, freight costs',
                'deduction_percentage' => 100.00,
                'deduction_behavior' => 'always',
                'confirmation_prompt' => null,
                'is_active' => true,
                'sort_order' => 14,
            ],
        ];

        // ============================================
        // TABLE 2: REQUIRES CONFIRMATION (User Must Confirm)
        // ============================================

        $requiresConfirmation = [
            [
                'name' => 'Fuel',
                'slug' => 'fuel',
                'description' => 'Vehicle fuel - must be for business use',
                'deduction_percentage' => 100.00,
                'deduction_behavior' => 'requires_confirmation',
                'confirmation_prompt' => 'Was this fuel purchase fully for business use?',
                'is_active' => true,
                'sort_order' => 15,
            ],
            [
                'name' => 'Vehicle Maintenance',
                'slug' => 'vehicle_maintenance',
                'description' => 'Vehicle repairs and servicing',
                'deduction_percentage' => 100.00,
                'deduction_behavior' => 'requires_confirmation',
                'confirmation_prompt' => 'Was this maintenance for a business vehicle?',
                'is_active' => true,
                'sort_order' => 16,
            ],
            [
                'name' => 'Vehicle Insurance',
                'slug' => 'vehicle_insurance',
                'description' => 'Auto insurance for vehicles',
                'deduction_percentage' => 100.00,
                'deduction_behavior' => 'requires_confirmation',
                'confirmation_prompt' => 'Is this insurance for a business-only vehicle?',
                'is_active' => true,
                'sort_order' => 17,
            ],
            [
                'name' => 'Meals & Entertainment',
                'slug' => 'meals_entertainment',
                'description' => 'Business meals and entertainment',
                'deduction_percentage' => 50.00, // Many countries limit meals to 50%
                'deduction_behavior' => 'requires_confirmation',
                'confirmation_prompt' => 'Was this a business meeting or work-related meal?',
                'is_active' => true,
                'sort_order' => 18,
            ],
            [
                'name' => 'Home Internet & Mobile',
                'slug' => 'home_internet_mobile',
                'description' => 'Home internet or mobile phone used for business',
                'deduction_percentage' => 100.00,
                'deduction_behavior' => 'requires_confirmation',
                'confirmation_prompt' => 'Was this service used mainly for business this month?',
                'is_active' => true,
                'sort_order' => 19,
            ],
            [
                'name' => 'Travel',
                'slug' => 'travel',
                'description' => 'Local or international travel',
                'deduction_percentage' => 100.00,
                'deduction_behavior' => 'requires_confirmation',
                'confirmation_prompt' => 'Was this trip for business purposes?',
                'is_active' => true,
                'sort_order' => 20,
            ],
            [
                'name' => 'Equipment & Tools',
                'slug' => 'equipment_tools',
                'description' => 'Business equipment and tools',
                'deduction_percentage' => 100.00,
                'deduction_behavior' => 'requires_confirmation',
                'confirmation_prompt' => 'Is this equipment used for your business operations?',
                'is_active' => true,
                'sort_order' => 21,
            ],
            [
                'name' => 'Software & Apps (Mixed Use)',
                'slug' => 'software_mixed',
                'description' => 'Software that might have personal and business use',
                'deduction_percentage' => 100.00,
                'deduction_behavior' => 'requires_confirmation',
                'confirmation_prompt' => 'Is this subscription primarily for business activity?',
                'is_active' => true,
                'sort_order' => 22,
            ],
            [
                'name' => 'Other',
                'slug' => 'other',
                'description' => 'Other business expenses',
                'deduction_percentage' => 100.00,
                'deduction_behavior' => 'requires_confirmation',
                'confirmation_prompt' => 'Is this expense for business activity?',
                'is_active' => true,
                'sort_order' => 23,
            ],
        ];

        // ============================================
        // TABLE 3: NEVER DEDUCTIBLE (Cannot Be Tax-Deductible)
        // ============================================

        $neverDeductible = [
            [
                'name' => 'Personal Expenses',
                'slug' => 'personal_expenses',
                'description' => 'Personal, non-business expenses',
                'deduction_percentage' => 0.00,
                'deduction_behavior' => 'never',
                'confirmation_prompt' => null,
                'is_active' => true,
                'sort_order' => 24,
            ],
            [
                'name' => 'Clothing (Non-Uniform)',
                'slug' => 'personal_clothing',
                'description' => 'Personal clothing - considered personal expense',
                'deduction_percentage' => 0.00,
                'deduction_behavior' => 'never',
                'confirmation_prompt' => null,
                'is_active' => true,
                'sort_order' => 25,
            ],
            [
                'name' => 'Owner Withdrawals',
                'slug' => 'owner_withdrawals',
                'description' => 'Owner drawings - not a business expense',
                'deduction_percentage' => 0.00,
                'deduction_behavior' => 'never',
                'confirmation_prompt' => null,
                'is_active' => true,
                'sort_order' => 26,
            ],
            [
                'name' => 'Owner Salary (Sole Trader)',
                'slug' => 'owner_salary',
                'description' => 'Sole proprietor cannot pay themselves a salary',
                'deduction_percentage' => 0.00,
                'deduction_behavior' => 'never',
                'confirmation_prompt' => null,
                'is_active' => true,
                'sort_order' => 27,
            ],
            [
                'name' => 'Loan Principal Payments',
                'slug' => 'loan_principal',
                'description' => 'Principal payments on loans (interest is deductible)',
                'deduction_percentage' => 0.00,
                'deduction_behavior' => 'never',
                'confirmation_prompt' => null,
                'is_active' => true,
                'sort_order' => 28,
            ],
            [
                'name' => 'Income Tax Payments',
                'slug' => 'income_tax',
                'description' => 'Tax payments themselves are not deductible',
                'deduction_percentage' => 0.00,
                'deduction_behavior' => 'never',
                'confirmation_prompt' => null,
                'is_active' => true,
                'sort_order' => 29,
            ],
            [
                'name' => 'Fines & Penalties',
                'slug' => 'fines_penalties',
                'description' => 'Legal fines and penalties - not deductible',
                'deduction_percentage' => 0.00,
                'deduction_behavior' => 'never',
                'confirmation_prompt' => null,
                'is_active' => true,
                'sort_order' => 30,
            ],
            [
                'name' => 'Donations (Non-Registered)',
                'slug' => 'unregistered_donations',
                'description' => 'Donations to non-registered charities',
                'deduction_percentage' => 0.00,
                'deduction_behavior' => 'never',
                'confirmation_prompt' => null,
                'is_active' => true,
                'sort_order' => 31,
            ],
            [
                'name' => 'Personal Insurance',
                'slug' => 'personal_insurance',
                'description' => 'Life, health, personal insurance (unless staff benefit)',
                'deduction_percentage' => 0.00,
                'deduction_behavior' => 'never',
                'confirmation_prompt' => null,
                'is_active' => true,
                'sort_order' => 32,
            ],
        ];

        // Merge all categories
        $allCategories = array_merge($alwaysDeductible, $requiresConfirmation, $neverDeductible);

        // Insert or update each category
        foreach ($allCategories as $category) {
            TaxCategory::updateOrCreate(
                ['slug' => $category['slug']],
                $category
            );
        }

        $alwaysCount = count($alwaysDeductible);
        $requiresCount = count($requiresConfirmation);
        $neverCount = count($neverDeductible);
        $totalCount = count($allCategories);

        $this->command->info('✅ Tax categories updated successfully!');
        $this->command->info("   - {$alwaysCount} Always Deductible categories");
        $this->command->info("   - {$requiresCount} Requires Confirmation categories");
        $this->command->info("   - {$neverCount} Never Deductible categories");
        $this->command->info("   Total: {$totalCount} categories");
    }
}
