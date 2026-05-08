<?php

namespace App\Console\Commands;

use App\Models\BookingSetting;
use App\Models\Contact;
use App\Models\FollowUpReminder;
use App\Models\User;
use App\Services\ContactReminderService;
use App\Services\EmailCampaignService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendColdContactAlertsCommand extends Command
{
    protected $signature   = 'contacts:cold-alerts {--days=30 : Days without contact before treating as cold}';
    protected $description = 'Send re-engagement emails to cold contacts and alert business owners';

    public function handle(
        ContactReminderService $reminderService,
        EmailCampaignService   $emailService
    ): int {
        $days = (int) $this->option('days');

        $this->info("Scanning for contacts with no interaction in {$days}+ days...");

        $users = User::whereHas('subscriptions', fn ($q) => $q->where('status', 'active'))
            ->get();

        $totalContacts = 0;
        $totalEmailed  = 0;

        foreach ($users as $user) {
            $coldContacts = $reminderService->alertColdContacts($user, $days);

            if ($coldContacts->isEmpty()) {
                continue;
            }

            $bookingSlug = BookingSetting::where('user_id', $user->id)->value('booking_slug');
            $rebookUrl   = $bookingSlug ? route('booking.public', ['slug' => $bookingSlug]) : null;

            $businessName = $user->businessProfile?->biz_trading_name
                ?? $user->businessProfile?->biz_registered_name
                ?? $user->name;

            foreach ($coldContacts as $contact) {
                if (empty($contact->email)) {
                    continue;
                }

                $totalContacts++;

                $firstName = $contact->first_name ?? explode(' ', $contact->name)[0];
                $subject = "We miss you, {$firstName} — let's reconnect";
                $body    = $this->buildReEngagementBody($contact, $businessName, $rebookUrl);

                try {
                    $emailService->sendToContact($user, $contact, $subject, $body, [
                        'email_type' => 'campaign',
                        'metadata'   => ['trigger' => 'cold_contact_auto_reengagement'],
                    ]);

                    // Prevent re-sending next run until a new interaction updates this
                    $contact->last_contact_date = now()->toDateString();
                    $contact->saveQuietly();

                    // Create a follow-up reminder for the owner to check in if no reply
                    FollowUpReminder::create([
                        'user_id'     => $user->id,
                        'contact_id'  => $contact->id,
                        'title'       => "Re-engagement sent to {$contact->name} — check for reply",
                        'description' => "Auto re-engagement email sent after {$days}+ days of no contact.",
                        'remind_at'   => now()->addDays(3),
                        'status'      => 'pending',
                        'priority'    => 'medium',
                    ]);

                    $totalEmailed++;

                    Log::info('Cold contact re-engagement email sent', [
                        'user_id'    => $user->id,
                        'contact_id' => $contact->id,
                    ]);

                } catch (\Exception $e) {
                    Log::warning('Failed to send cold contact re-engagement email', [
                        'user_id'    => $user->id,
                        'contact_id' => $contact->id,
                        'error'      => $e->getMessage(),
                    ]);
                }
            }
        }

        $this->info("Done. Processed {$totalContacts} cold contacts, emailed {$totalEmailed}.");

        return self::SUCCESS;
    }

    private function buildReEngagementBody(Contact $contact, string $businessName, ?string $rebookUrl): string
    {
        $firstName = $contact->first_name ?? explode(' ', $contact->name)[0];

        $body  = "<p>Hi {$firstName},</p>";
        $body .= "<p>It's been a while since we last connected, and we wanted to check in and say hello!</p>";
        $body .= "<p>We'd love to see you again — whether you have a question, need our services, or just want to catch up.</p>";

        if ($rebookUrl) {
            $body .= "<p><a href=\"{$rebookUrl}\">Click here to book an appointment</a> at a time that suits you.</p>";
        }

        $body .= "<p>Looking forward to hearing from you.</p>";
        $body .= "<p>Warm regards,<br>{$businessName}</p>";

        return $body;
    }
}
