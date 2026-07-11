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
    protected $signature = 'contacts:cold-alerts
        {--days=45 : Days since last appointment before inviting a client to rebook}
        {--contact-days=30 : Days without contact before treating non-client relationships as cold}
        {--cooldown=7 : Minimum days before sending another automated re-engagement email}';

    protected $description = 'Send rebooking prompts to past clients and re-engagement emails to cold contacts';

    public function handle(
        ContactReminderService $reminderService,
        EmailCampaignService $emailService
    ): int {
        $days = (int) $this->option('days');
        $contactDays = (int) $this->option('contact-days');
        $cooldownDays = (int) $this->option('cooldown');

        $this->info("Scanning for clients {$days}+ days after their last appointment...");

        $users = User::whereHas('bookingSetting', fn ($query) => $query->where('is_booking_enabled', true))
            ->get();

        $totalContacts = 0;
        $totalEmailed = 0;
        $totalRebooking = 0;

        foreach ($users as $user) {
            $rebookingContacts = $reminderService->getClientsDueToRebook($user, $days, $cooldownDays);
            $coldContacts = $reminderService->alertColdContacts($user, $contactDays);
            $contacts = $rebookingContacts
                ->merge($coldContacts)
                ->unique('id')
                ->values();

            if ($contacts->isEmpty()) {
                continue;
            }

            $bookingSlug = BookingSetting::where('user_id', $user->id)->value('booking_slug');
            $rebookUrl = $bookingSlug ? route('booking.public', ['slug' => $bookingSlug]) : null;

            $businessName = $user->businessProfile?->biz_trading_name
                ?? $user->businessProfile?->biz_registered_name
                ?? $user->name;

            foreach ($contacts as $contact) {
                if (empty($contact->email)) {
                    continue;
                }

                $totalContacts++;

                $firstName = $this->firstName($contact);
                $isRebookingCandidate = $rebookingContacts->contains('id', $contact->id);
                $subject = $isRebookingCandidate
                    ? "Ready to book your next visit, {$firstName}?"
                    : "We miss you, {$firstName} - let's reconnect";
                $body = $isRebookingCandidate
                    ? $this->buildRebookingBody($contact, $businessName, $rebookUrl)
                    : $this->buildReEngagementBody($contact, $businessName, $rebookUrl);

                try {
                    $emailService->sendToContact($user, $contact, $subject, $body, [
                        'email_type' => 'campaign',
                        'metadata' => [
                            'trigger' => $isRebookingCandidate
                                ? 'client_auto_rebooking'
                                : 'cold_contact_auto_reengagement',
                            'days_since_last_appointment' => $contact->last_appointment_at
                                ? $contact->last_appointment_at->diffInDays(now())
                                : null,
                        ],
                    ]);

                    $contact->last_contact_date = now()->toDateString();
                    $contact->saveQuietly();

                    FollowUpReminder::create([
                        'user_id' => $user->id,
                        'contact_id' => $contact->id,
                        'title' => $isRebookingCandidate
                            ? "Rebooking invite sent to {$contact->name}"
                            : "Re-engagement sent to {$contact->name} - check for reply",
                        'description' => $isRebookingCandidate
                            ? "Auto rebooking email sent {$days}+ days after their last appointment."
                            : "Auto re-engagement email sent after {$contactDays}+ days of no contact.",
                        'remind_at' => now()->addDays(3),
                        'status' => 'pending',
                        'priority' => 'medium',
                    ]);

                    $totalEmailed++;
                    $totalRebooking += $isRebookingCandidate ? 1 : 0;

                    Log::info('Automated rebooking/re-engagement email sent', [
                        'user_id' => $user->id,
                        'contact_id' => $contact->id,
                        'trigger' => $isRebookingCandidate
                            ? 'client_auto_rebooking'
                            : 'cold_contact_auto_reengagement',
                    ]);
                } catch (\Exception $e) {
                    Log::warning('Failed to send automated rebooking/re-engagement email', [
                        'user_id' => $user->id,
                        'contact_id' => $contact->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }

        $this->info("Done. Processed {$totalContacts} contacts, emailed {$totalEmailed}, rebooking prompts {$totalRebooking}.");

        return self::SUCCESS;
    }

    private function buildRebookingBody(Contact $contact, string $businessName, ?string $rebookUrl): string
    {
        $firstName = $this->firstName($contact);
        $lastVisit = $contact->last_appointment_at?->format('F j');

        $body = "<p>Hi {$firstName},</p>";
        $body .= $lastVisit
            ? "<p>It has been a little while since your last visit on {$lastVisit}, and we would love to see you again.</p>"
            : "<p>It has been a little while since your last visit, and we would love to see you again.</p>";
        $body .= '<p>If now is a good time, you can choose a slot that works for you.</p>';

        if ($rebookUrl) {
            $body .= "<p><a href=\"{$rebookUrl}\">Book your next appointment</a></p>";
        }

        $body .= "<p>Warm regards,<br>{$businessName}</p>";

        return $body;
    }

    private function buildReEngagementBody(Contact $contact, string $businessName, ?string $rebookUrl): string
    {
        $firstName = $this->firstName($contact);

        $body = "<p>Hi {$firstName},</p>";
        $body .= "<p>It's been a while since we last connected, and we wanted to check in and say hello.</p>";
        $body .= "<p>We'd love to see you again, whether you have a question, need our services, or just want to catch up.</p>";

        if ($rebookUrl) {
            $body .= "<p><a href=\"{$rebookUrl}\">Click here to book an appointment</a> at a time that suits you.</p>";
        }

        $body .= "<p>Warm regards,<br>{$businessName}</p>";

        return $body;
    }

    private function firstName(Contact $contact): string
    {
        return explode(' ', trim($contact->name))[0] ?: 'there';
    }
}
