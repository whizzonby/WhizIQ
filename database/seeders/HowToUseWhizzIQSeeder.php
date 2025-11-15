<?php

namespace Database\Seeders;

use App\Models\Page;
use App\Models\User;
use Illuminate\Database\Seeder;

class HowToUseWhizzIQSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the first admin user as the author
        $admin = User::admin()->first();

        if (! $admin) {
            $this->command->warn('No admin user found. Please create an admin user first.');
            return;
        }

        // How to use WhizzIQ
        Page::updateOrCreate(
            ['slug' => 'how-to-use-whizziq'],
            [
                'title' => 'How to Use WhizzIQ',
                'slug' => 'how-to-use-WhizzIQ',
                'content' => $this->getHowToUseWhizzIQContent(),
                'meta_description' => 'Complete guide on how to use WhizzIQ - your all-in-one business management platform. Learn about features, getting started, and best practices.',
                'meta_keywords' => 'WhizzIQ guide, how to use WhizzIQ, WhizzIQ tutorial, business management, user guide',
                'is_published' => true,
                'published_at' => now(),
                'author_id' => $admin->id,
                'page_type' => 'general',
                'sort_order' => 4,
            ]
        );

        $this->command->info('How to Use WhizzIQ page created/updated successfully!');
    }

    private function getHowToUseWhizzIQContent(): string
    {
        $appName = config('app.name', 'WhizzIQ');
        $appUrl = config('app.url', '');
        $supportEmail = config('app.support_email', 'support@WhizzIQ.com');

        return <<<HTML
<div class="prose prose-lg max-w-none">
    <p class="mb-6 text-lg">
        Welcome to {$appName}! This comprehensive guide will help you get started and make the most of all the powerful features our platform offers. Whether you're managing finances, tracking clients, scheduling appointments, or analyzing your business performance, this guide has you covered.
    </p>

    <h2 class="text-2xl font-bold mt-8 mb-4">Getting Started</h2>
    
    <h3 class="text-xl font-semibold mt-6 mb-3">1. Account Setup</h3>
    <p class="mb-6">
        After creating your account, you'll be guided through an onboarding process. This helps us customize your experience and set up your profile with essential business information.
    </p>
    <ul class="list-disc ml-6 mb-6">
        <li>Complete your business profile with company details</li>
        <li>Set up your tax settings and preferences</li>
        <li>Configure your notification preferences</li>
        <li>Connect your calendar (Google Calendar, Outlook, or Apple Calendar)</li>
    </ul>

    <h3 class="text-xl font-semibold mt-6 mb-3">2. Dashboard Overview</h3>
    <p class="mb-6">
        Your dashboard is your command center. Here you'll find:
    </p>
    <ul class="list-disc ml-6 mb-6">
        <li>Quick access to all major features</li>
        <li>Key business metrics and KPIs</li>
        <li>Recent activity and notifications</li>
        <li>Quick actions for common tasks</li>
    </ul>

    <h2 class="text-2xl font-bold mt-8 mb-4">Core Features</h2>

    <h3 class="text-xl font-semibold mt-6 mb-3">Financial Management</h3>
    <p class="mb-6">
        {$appName} provides comprehensive financial management tools to keep your business finances organized and compliant.
    </p>
    
    <h4 class="text-lg font-semibold mt-4 mb-2">Expense Tracking</h4>
    <ul class="list-disc ml-6 mb-6">
        <li><strong>AI Auto-Categorization:</strong> Upload receipts and let AI automatically categorize your expenses</li>
        <li><strong>Manual Entry:</strong> Add expenses manually with detailed categorization</li>
        <li><strong>Bulk Import:</strong> Import expenses from CSV files or bank statements</li>
        <li><strong>Receipt Storage:</strong> Attach receipts and documents to expenses for easy reference</li>
        <li><strong>Expense Reports:</strong> Generate detailed expense reports by category, date range, or project</li>
    </ul>

    <h4 class="text-lg font-semibold mt-4 mb-2">Invoicing</h4>
    <ul class="list-disc ml-6 mb-6">
        <li><strong>Create Professional Invoices:</strong> Design custom invoices with multiple templates</li>
        <li><strong>Recurring Invoices:</strong> Set up automatic recurring invoices for regular clients</li>
        <li><strong>Payment Tracking:</strong> Track invoice status and payment history</li>
        <li><strong>PDF Generation:</strong> Download or email invoices as professional PDFs</li>
        <li><strong>Payment Reminders:</strong> Automatically send payment reminders to clients</li>
    </ul>

    <h4 class="text-lg font-semibold mt-4 mb-2">Cash Flow Management</h4>
    <ul class="list-disc ml-6 mb-6">
        <li>Monitor your cash flow in real-time</li>
        <li>View income vs expenses trends</li>
        <li>Forecast future cash flow based on scheduled invoices and expenses</li>
        <li>Identify cash flow patterns and optimize your finances</li>
    </ul>

    <h3 class="text-xl font-semibold mt-6 mb-3">Tax Compliance</h3>
    <p class="mb-6">
        Stay compliant with automated tax calculations and reporting tools.
    </p>
    <ul class="list-disc ml-6 mb-6">
        <li><strong>Automated Tax Calculations:</strong> Automatic calculation of taxes based on your settings</li>
        <li><strong>Quarterly Reports:</strong> Generate tax reports for quarterly filings</li>
        <li><strong>Deadline Reminders:</strong> Get notified about upcoming tax deadlines</li>
        <li><strong>Deduction Tracking:</strong> Track deductible expenses by category</li>
        <li><strong>Tax Dashboard:</strong> View your tax liability and estimated payments</li>
    </ul>

    <h3 class="text-xl font-semibold mt-6 mb-3">Customer Relationship Management (CRM)</h3>
    <p class="mb-6">
        Manage all your client relationships in one place with our powerful CRM system.
    </p>
    
    <h4 class="text-lg font-semibold mt-4 mb-2">Contact Management</h4>
    <ul class="list-disc ml-6 mb-6">
        <li>Store comprehensive client information and contact details</li>
        <li>Track communication history and interactions</li>
        <li>Manage client notes and important information</li>
        <li>Segment clients by custom criteria</li>
    </ul>

    <h4 class="text-lg font-semibold mt-4 mb-2">Deal Pipeline</h4>
    <ul class="list-disc ml-6 mb-6">
        <li>Visualize your sales pipeline with drag-and-drop stages</li>
        <li>Track deals from lead to close</li>
        <li>Set deal values and probabilities</li>
        <li>Monitor conversion rates and sales performance</li>
    </ul>

    <h3 class="text-xl font-semibold mt-6 mb-3">Document Vault</h3>
    <p class="mb-6">
        Enterprise-grade document management with AI-powered analysis.
    </p>
    <ul class="list-disc ml-6 mb-6">
        <li><strong>Secure Storage:</strong> Store all your business documents in one secure location</li>
        <li><strong>AI Analysis:</strong> Extract key information from documents using AI</li>
        <li><strong>Version Control:</strong> Track document versions and changes</li>
        <li><strong>Search & Organization:</strong> Quickly find documents with advanced search</li>
        <li><strong>Document Categories:</strong> Organize documents by type, project, or custom categories</li>
    </ul>

    <h3 class="text-xl font-semibold mt-6 mb-3">Task Management</h3>
    <p class="mb-6">
        Stay organized and productive with intelligent task management.
    </p>
    <ul class="list-disc ml-6 mb-6">
        <li><strong>AI Task Extraction:</strong> AI automatically extracts tasks from your notes and emails</li>
        <li><strong>Kanban Board:</strong> Visualize tasks with a drag-and-drop kanban board</li>
        <li><strong>OKR-Style Goals:</strong> Set and track Objectives and Key Results</li>
        <li><strong>Task Prioritization:</strong> Prioritize tasks and set deadlines</li>
        <li><strong>Team Collaboration:</strong> Assign tasks and collaborate with team members</li>
    </ul>

    <h3 class="text-xl font-semibold mt-6 mb-3">Appointment Booking</h3>
    <p class="mb-6">
        Streamline your scheduling with automated appointment management.
    </p>
    <ul class="list-disc ml-6 mb-6">
        <li><strong>Public Booking Pages:</strong> Create shareable booking pages for clients</li>
        <li><strong>Calendar Sync:</strong> Sync with Google Calendar, Outlook, or Apple Calendar</li>
        <li><strong>Automatic Meeting Links:</strong> Automatically generate Zoom or Google Meet links</li>
        <li><strong>Appointment Reminders:</strong> Send automatic reminders to reduce no-shows</li>
        <li><strong>Availability Management:</strong> Set your availability and let clients book accordingly</li>
        <li><strong>Calendar View:</strong> View all appointments in a comprehensive calendar interface</li>
    </ul>

    <h3 class="text-xl font-semibold mt-6 mb-3">Business Analytics</h3>
    <p class="mb-6">
        Make data-driven decisions with powerful analytics and insights.
    </p>
    <ul class="list-disc ml-6 mb-6">
        <li><strong>Automated SWOT Analysis:</strong> Get AI-powered SWOT analysis of your business</li>
        <li><strong>Risk Assessments:</strong> Identify and assess business risks</li>
        <li><strong>Productivity Tracking:</strong> Monitor team productivity and performance</li>
        <li><strong>Revenue Forecasting:</strong> Predict future revenue based on historical data</li>
        <li><strong>Custom Dashboards:</strong> Create custom dashboards with key metrics</li>
        <li><strong>Export Reports:</strong> Export data for external analysis</li>
    </ul>

    <h3 class="text-xl font-semibold mt-6 mb-3">Marketing Management</h3>
    <p class="mb-6">
        Manage your marketing campaigns and track their effectiveness.
    </p>
    <ul class="list-disc ml-6 mb-6">
        <li><strong>Social Media Integration:</strong> Connect Facebook, Instagram, LinkedIn, and Twitter</li>
        <li><strong>Email Campaigns:</strong> Create and send email marketing campaigns</li>
        <li><strong>AI Insights:</strong> Get AI-powered insights on campaign performance</li>
        <li><strong>Campaign Analytics:</strong> Track engagement and ROI</li>
        <li><strong>Content Scheduling:</strong> Schedule posts across multiple platforms</li>
    </ul>

    <h3 class="text-xl font-semibold mt-6 mb-3">Inventory Management</h3>
    <p class="mb-6">
        Track inventory for service-based and retail operations.
    </p>
    <ul class="list-disc ml-6 mb-6">
        <li>Monitor stock levels in real-time</li>
        <li>Calculate cost per item</li>
        <li>Track inventory movements</li>
        <li>Set low stock alerts</li>
        <li>Generate inventory reports</li>
    </ul>

    <h2 class="text-2xl font-bold mt-8 mb-4">Integrations</h2>
    <p class="mb-6">
        {$appName} integrates seamlessly with the tools you already use:
    </p>
    <ul class="list-disc ml-6 mb-6">
        <li><strong>Calendars:</strong> Google Calendar, Outlook, Apple Calendar</li>
        <li><strong>Video Conferencing:</strong> Zoom, Google Meet</li>
        <li><strong>Social Media:</strong> Facebook, Instagram, LinkedIn, Twitter</li>
        <li><strong>AI Services:</strong> OpenAI for intelligent automation and insights</li>
        <li><strong>Payment Processors:</strong> Stripe, Paddle, Lemon Squeezy</li>
    </ul>

    <h2 class="text-2xl font-bold mt-8 mb-4">Best Practices</h2>

    <h3 class="text-xl font-semibold mt-6 mb-3">1. Regular Data Entry</h3>
    <p class="mb-6">
        Keep your data up-to-date by entering expenses and invoices regularly. This ensures accurate reporting and helps you make informed decisions.
    </p>

    <h3 class="text-xl font-semibold mt-6 mb-3">2. Use Categories Effectively</h3>
    <p class="mb-6">
        Properly categorize all transactions to get meaningful insights and accurate tax reporting. Take advantage of AI auto-categorization to save time.
    </p>

    <h3 class="text-xl font-semibold mt-6 mb-3">3. Leverage Automation</h3>
    <p class="mb-6">
        Set up recurring invoices, automatic reminders, and calendar syncs to automate routine tasks and focus on growing your business.
    </p>

    <h3 class="text-xl font-semibold mt-6 mb-3">4. Review Analytics Regularly</h3>
    <p class="mb-6">
        Check your dashboards and reports regularly to identify trends, spot issues early, and make data-driven decisions.
    </p>

    <h3 class="text-xl font-semibold mt-6 mb-3">5. Keep Documents Organized</h3>
    <p class="mb-6">
        Use the Document Vault to store all important business documents. Proper organization makes it easy to find what you need when you need it.
    </p>

    <h2 class="text-2xl font-bold mt-8 mb-4">Getting Help</h2>
    <p class="mb-6">
        If you need assistance or have questions about using {$appName}, we're here to help:
    </p>
    <ul class="list-disc ml-6 mb-6">
        <li><strong>Email Support:</strong> <a href="mailto:{$supportEmail}" class="text-blue-600 hover:underline">{$supportEmail}</a></li>
        <li><strong>In-App Help:</strong> Look for help icons and tooltips throughout the platform</li>
        <li><strong>Documentation:</strong> Check our knowledge base for detailed guides and tutorials</li>
    </ul>

    <h2 class="text-2xl font-bold mt-8 mb-4">Download Complete Guide</h2>
    <p class="mb-6">
        For a comprehensive, detailed guide covering all features, advanced tips, and troubleshooting, download our complete PDF guide:
    </p>
    <div class="mt-6 mb-8 not-prose">
        <a href="/download/WhizzIQ-complete-guide.pdf" 
           class="!inline-flex !items-center !px-6 !py-3 !bg-primary-600 !text-white !font-semibold !rounded-lg hover:!bg-primary-700 !transition-colors !duration-200 !shadow-md hover:!shadow-lg !no-underline">
            <span class="!mr-2" style="display: inline-block; width: 14px; height: 14px; line-height: 1;">
                <svg style="width: 14px; height: 14px; display: block;" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
            </span>
            Download Complete WhizzIQ Guide (PDF)
        </a>
    </div>

    <p class="mb-6 text-sm text-gray-600">
        The PDF guide includes step-by-step instructions, screenshots, advanced features, keyboard shortcuts, and troubleshooting tips to help you master {$appName}.
    </p>
</div>
HTML;
    }
}

