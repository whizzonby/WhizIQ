<?php

namespace Database\Seeders;

use App\Models\BlogPost;
use App\Models\BlogPostCategory;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BlogPostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get or create a category
        $category = BlogPostCategory::firstOrCreate(
            ['slug' => 'crm-tips'],
            [
                'name' => 'CRM Tips',
            ]
        );

        // Get first user (admin)
        $user = User::first();

        if (!$user) {
            $this->command->error('No users found. Please create a user first.');
            return;
        }

        // Temporarily login as this user for the seeder
        auth()->login($user);

        $posts = [
            [
                'title' => 'How to Choose the Right CRM for Your Business',
                'description' => 'A comprehensive guide to selecting the perfect CRM solution that fits your business needs and budget.',
                'body' => '<p>Choosing the right Customer Relationship Management (CRM) system is crucial for your business growth. In this guide, we\'ll walk through the key factors to consider when evaluating CRM solutions.</p>

<h2>Key Considerations</h2>
<ul>
<li><strong>Business Size:</strong> Ensure the CRM scales with your company</li>
<li><strong>Features:</strong> Look for contact management, sales pipeline, and reporting</li>
<li><strong>Integration:</strong> Check compatibility with your existing tools</li>
<li><strong>Pricing:</strong> Consider both upfront and ongoing costs</li>
<li><strong>Support:</strong> Evaluate customer service and training resources</li>
</ul>

<p>WhizzIQ offers all these features and more, making it the perfect choice for growing businesses.</p>',
            ],
            [
                'title' => '5 Ways CRM Automation Saves Your Team Time',
                'description' => 'Discover how automating your customer relationship workflows can free up hours each week for more strategic work.',
                'body' => '<p>Manual data entry and repetitive tasks eat up valuable time. Here are five ways CRM automation transforms your workflow:</p>

<h2>1. Automatic Lead Assignment</h2>
<p>Never manually distribute leads again. Automation ensures leads reach the right sales rep instantly based on territory, industry, or other criteria.</p>

<h2>2. Follow-up Reminders</h2>
<p>Set automated reminders so no opportunity falls through the cracks. Your CRM remembers so you don\'t have to.</p>

<h2>3. Email Sequences</h2>
<p>Create email campaigns that nurture leads automatically based on their behavior and stage in the sales funnel.</p>

<h2>4. Data Entry Reduction</h2>
<p>Automatic contact enrichment and form fills reduce manual data entry by up to 70%.</p>

<h2>5. Reporting & Analytics</h2>
<p>Get real-time dashboards without spending hours compiling spreadsheets.</p>

<p>Start automating with WhizzIQ today and reclaim hours each week!</p>',
            ],
            [
                'title' => 'Maximizing ROI: Getting the Most from Your CRM Investment',
                'description' => 'Learn proven strategies to increase your return on investment and ensure your CRM delivers measurable business value.',
                'body' => '<p>A CRM is only valuable if your team actually uses it. Here\'s how to maximize your ROI:</p>

<h2>Ensure Team Adoption</h2>
<p>The #1 reason CRMs fail is poor user adoption. Make sure your team understands the benefits and receives proper training.</p>

<h2>Clean Your Data</h2>
<p>Garbage in, garbage out. Regular data cleaning ensures accurate reporting and better decision-making.</p>

<h2>Measure What Matters</h2>
<p>Track KPIs like conversion rates, sales cycle length, and customer lifetime value to prove ROI to stakeholders.</p>

<h2>Integrate with Other Tools</h2>
<p>Connect your CRM with email, calendar, accounting software, and other business tools for a complete view of your operations.</p>

<p>WhizzIQ makes all of this easy with intuitive design, built-in training resources, and seamless integrations.</p>',
            ],
            [
                'title' => 'Mobile CRM: Why Your Sales Team Needs It',
                'description' => 'Explore the benefits of mobile CRM access and how it empowers your sales team to close deals from anywhere.',
                'body' => '<p>In today\'s fast-paced business environment, your sales team can\'t afford to be tied to their desks. Mobile CRM access is essential:</p>

<h2>Update Records On-the-Go</h2>
<p>Log meeting notes, update deal stages, and add contacts immediately after customer interactions—no waiting until you\'re back at the office.</p>

<h2>Access Customer Info Anywhere</h2>
<p>Pull up complete customer history before meetings, even when you\'re traveling or working remotely.</p>

<h2>Faster Response Times</h2>
<p>Respond to leads and customer inquiries instantly from your phone, improving satisfaction and conversion rates.</p>

<h2>Real-time Collaboration</h2>
<p>Stay synced with your team no matter where you are. See updates in real-time and coordinate seamlessly.</p>

<p>WhizzIQ\'s mobile app puts powerful CRM features in your pocket, helping you close more deals wherever you are.</p>',
            ],
        ];

        foreach ($posts as $index => $postData) {
            BlogPost::create([
                'user_id' => $user->id,
                'author_id' => $user->id,
                'blog_post_category_id' => $category->id,
                'title' => $postData['title'],
                'slug' => Str::slug($postData['title']),
                'description' => $postData['description'],
                'body' => $postData['body'],
                'is_published' => true,
                'published_at' => now()->subDays(10 - ($index * 2)), // Stagger publication dates
            ]);
        }

        $this->command->info('Created 4 sample blog posts!');
    }
}
