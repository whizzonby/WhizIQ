<?php

namespace App\Observers;

use App\Models\AftercareSequence;
use App\Models\BookingSetting;
use App\Models\Contact;
use App\Models\ContactInteraction;
use App\Models\FollowUpReminder;
use App\Models\InvoiceClient;
use App\Services\MessagingService;
use Illuminate\Support\Facades\Log;

class ContactObserver
{
    public function __construct(protected MessagingService $messaging) {}

    public function created(Contact $contact): void
    {
        $this->syncInvoiceClient($contact);
    }

    /**
     * When a contact is tagged VIP, automatically start a personalised
     * nurture sequence (if configured) and alert the owner.
     */
    public function updated(Contact $contact): void
    {
        if ($contact->wasChanged(['name', 'email', 'phone', 'company', 'address', 'city', 'state', 'zip', 'country'])) {
            $this->syncInvoiceClient($contact);
        }

        if ($contact->wasChanged('priority')
            && $contact->priority === 'vip'
            && $contact->getOriginal('priority') !== 'vip'
        ) {
            $this->handleVipTagged($contact);
        }
    }

    protected function syncInvoiceClient(Contact $contact): void
    {
        try {
            InvoiceClient::updateOrCreate(
                ['user_id' => $contact->user_id, 'contact_id' => $contact->id],
                [
                    'name'      => $contact->name,
                    'email'     => $contact->email,
                    'phone'     => $contact->phone,
                    'company'   => $contact->company,
                    'address'   => $contact->address,
                    'city'      => $contact->city,
                    'state'     => $contact->state,
                    'zip'       => $contact->zip,
                    'country'   => $contact->country,
                    'is_active' => true,
                ]
            );
        } catch (\Exception $e) {
            Log::error('Failed to sync contact to invoice client', [
                'contact_id' => $contact->id,
                'error'      => $e->getMessage(),
            ]);
        }
    }

    protected function handleVipTagged(Contact $contact): void
    {
        try {
            $contact->loadMissing('user');
            $user = $contact->user;

            if (! $user) {
                return;
            }

            // Log a CRM interaction marking the VIP upgrade
            ContactInteraction::create([
                'user_id'          => $user->id,
                'contact_id'       => $contact->id,
                'type'             => 'note',
                'subject'          => 'Contact marked as VIP',
                'description'      => "Contact upgraded to VIP — personalised nurture sequence initiated.",
                'interaction_date' => now(),
                'outcome'          => 'positive',
            ]);

            // Look for a user-configured VIP sequence (name contains "vip", case-insensitive)
            $vipSequence = AftercareSequence::where('user_id', $user->id)
                ->where('is_active', true)
                ->whereRaw('LOWER(name) LIKE ?', ['%vip%'])
                ->with(['activeSteps' => fn ($q) => $q->orderBy('step_order')])
                ->first();

            $bookingSlug = BookingSetting::where('user_id', $user->id)->value('booking_slug');
            $rebookUrl   = $bookingSlug
                ? route('booking.public', ['slug' => $bookingSlug])
                : null;

            $businessName = $user->businessProfile?->biz_trading_name
                ?? $user->businessProfile?->biz_registered_name
                ?? $user->name;

            if ($vipSequence && $vipSequence->activeSteps->isNotEmpty()) {
                // Send the first step's message directly to the contact
                $firstStep  = $vipSequence->activeSteps->first();
                $clientName = $contact->name;

                $message = str_replace(
                    ['{{client_name}}', '{{client_first_name}}', '{{business_name}}'],
                    [$clientName, explode(' ', $clientName)[0], $businessName],
                    $firstStep->message_body
                );

                if ($firstStep->include_rebooking_link && $rebookUrl) {
                    $message .= "\n\nBook your next session: {$rebookUrl}";
                }

                if ($contact->phone) {
                    if ($firstStep->send_whatsapp) {
                        $this->messaging->sendWhatsApp($contact->phone, $message, $user);
                    } elseif ($firstStep->send_sms) {
                        $this->messaging->notifyClient($contact->phone, $message);
                    }
                }

                // Queue a follow-up reminder for each remaining step
                foreach ($vipSequence->activeSteps->skip(1) as $step) {
                    $delayDays = $step->delay_days
                        + (int) ceil(($step->delay_hours * 60 + $step->delay_minutes) / 1440);

                    FollowUpReminder::create([
                        'user_id'     => $user->id,
                        'contact_id'  => $contact->id,
                        'title'       => "VIP sequence step {$step->step_order}: {$contact->name}",
                        'description' => $step->message_body,
                        'remind_at'   => now()->addDays(max(1, $delayDays)),
                        'status'      => 'pending',
                        'priority'    => 'high',
                    ]);
                }

                Log::info('VIP sequence started for contact', [
                    'contact_id'  => $contact->id,
                    'sequence_id' => $vipSequence->id,
                ]);

            } else {
                // No VIP sequence configured — create a high-priority reminder
                // so the owner can manually run their personalised outreach.
                FollowUpReminder::create([
                    'user_id'     => $user->id,
                    'contact_id'  => $contact->id,
                    'title'       => "New VIP contact — start personalised sequence: {$contact->name}",
                    'description' => 'This contact has been tagged VIP. Send a personalised welcome message and book a discovery meeting.'
                        . ($rebookUrl ? " Booking link: {$rebookUrl}" : ''),
                    'remind_at'   => now()->addHour(),
                    'status'      => 'pending',
                    'priority'    => 'high',
                ]);

                Log::info('No VIP sequence configured — created high-priority reminder', [
                    'contact_id' => $contact->id,
                    'user_id'    => $user->id,
                ]);
            }

            // Alert the owner immediately
            $this->messaging->notifyOwner(
                $user,
                "VIP contact: {$contact->name} has been tagged VIP. A personalised sequence has been queued."
            );

        } catch (\Exception $e) {
            Log::error('Failed to handle VIP contact tagging', [
                'contact_id' => $contact->id,
                'error'      => $e->getMessage(),
            ]);
        }
    }
}
