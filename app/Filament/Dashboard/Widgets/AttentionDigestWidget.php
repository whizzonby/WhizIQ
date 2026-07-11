<?php

namespace App\Filament\Dashboard\Widgets;

use App\Models\Appointment;
use App\Models\AppointmentType;
use App\Models\BookingSetting;
use App\Models\CampaignAudience;
use App\Models\ClientInvoice;
use App\Models\ClientPaymentAttempt;
use App\Models\Deal;
use App\Models\EmailCampaign;
use App\Models\FollowUpReminder;
use App\Models\Task;
use App\Services\AvailabilityService;
use App\Services\CampaignAudienceService;
use App\Services\ClientPaymentRecoveryService;
use App\Services\InvoiceReminderService;
use Filament\Notifications\Notification;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class AttentionDigestWidget extends Widget
{
    protected static ?int $sort = -2;

    protected static bool $isLazy = true;

    protected int|string|array $columnSpan = 'full';

    protected string $view = 'filament.dashboard.widgets.attention-digest-widget';

    public function getDigestData(): array
    {
        $userId   = Auth::id();
        $cacheKey = "attention_digest_{$userId}_" . now()->format('Y-m-d-H');

        return Cache::remember($cacheKey, 900, function () use ($userId) {
            $items = collect();

            // Overdue invoices — show up to 3, highest amount first
            ClientInvoice::with('client')
                ->where('user_id', $userId)
                ->where('status', 'overdue')
                ->orderByRaw('(total_amount - amount_paid) DESC')
                ->limit(3)
                ->get()
                ->each(function ($inv) use ($items) {
                    $owed = $inv->total_amount - $inv->amount_paid;
                    $days = max(0, (int) now()->diffInDays($inv->due_date));
                    $name = optional($inv->client)->name ?? 'Client';
                    $items->push([
                        'type'         => 'overdue_invoice',
                        'border_color' => '#ef4444',
                        'title'        => $name . ' invoice overdue',
                        'subtitle'     => '$' . number_format($owed, 0) . ' · ' . $days . ' ' . ($days === 1 ? 'day' : 'days') . ' past due',
                        'action_label' => 'Send pay link',
                        'action_color' => '#fef2f2',
                        'action_text'  => '#dc2626',
                        'action_url'   => route('filament.dashboard.resources.client-invoices.index'),
                        'action_method' => 'sendPaymentLink',
                        'action_id' => $inv->id,
                    ]);
                });

            // Stale deals — show up to 2, longest stale first
            FollowUpReminder::with('contact')
                ->where('user_id', $userId)
                ->where('status', 'pending')
                ->where('remind_at', '<=', now()->addDay())
                ->orderByRaw("CASE priority WHEN 'high' THEN 0 WHEN 'medium' THEN 1 ELSE 2 END")
                ->orderBy('remind_at')
                ->limit(3)
                ->get()
                ->each(function ($reminder) use ($items) {
                    $contactName = optional($reminder->contact)->name ?? 'Contact';
                    $items->push([
                        'type'         => 'follow_up',
                        'border_color' => $reminder->priority === 'high' ? '#f97316' : '#f59e0b',
                        'title'        => $reminder->title,
                        'subtitle'     => $contactName . ' - ' . ($reminder->remind_at->isPast() ? 'due now' : 'due tomorrow'),
                        'action_label' => 'Complete',
                        'action_color' => '#fff7ed',
                        'action_text'  => '#c2410c',
                        'action_url'   => $reminder->contact
                            ? route('filament.dashboard.resources.contacts.edit', ['record' => $reminder->contact])
                            : route('filament.dashboard.resources.contacts.index'),
                        'action_method' => 'completeFollowUp',
                        'action_id' => $reminder->id,
                    ]);
                });

            Deal::where('user_id', $userId)
                ->whereIn('stage', ['lead', 'qualified', 'proposal', 'negotiation'])
                ->where('updated_at', '<', now()->subDays(14))
                ->orderBy('updated_at')
                ->limit(2)
                ->get()
                ->each(function ($deal) use ($items) {
                    $days = (int) now()->diffInDays($deal->updated_at);
                    $items->push([
                        'type'         => 'stale_deal',
                        'border_color' => '#8b5cf6',
                        'title'        => ($deal->title ?? 'Deal') . ' going cold',
                        'subtitle'     => '$' . number_format($deal->value ?? 0, 0) . ' · ' . $days . ' days no contact',
                        'action_label' => 'Follow up',
                        'action_color' => '#f5f3ff',
                        'action_text'  => '#7c3aed',
                        'action_url'   => route('filament.dashboard.resources.deals.index'),
                    ]);
                });

            ClientPaymentAttempt::with(['invoice.client'])
                ->where('user_id', $userId)
                ->failed()
                ->unrecovered()
                ->latest('failed_at')
                ->limit(2)
                ->get()
                ->each(function (ClientPaymentAttempt $attempt) use ($items) {
                    $clientName = $attempt->invoice?->client?->name ?? 'Client';
                    $amount = (float) ($attempt->amount ?: $attempt->invoice?->balance_due ?? 0);

                    $items->push([
                        'type'         => 'failed_payment',
                        'border_color' => '#e11d48',
                        'title'        => $clientName . ' payment failed',
                        'subtitle'     => '$' . number_format($amount, 0) . ' - ' . ($attempt->failure_message ?: 'needs recovery'),
                        'action_label' => 'Recover',
                        'action_color' => '#fff1f2',
                        'action_text'  => '#be123c',
                        'action_url'   => route('filament.dashboard.resources.client-invoices.index'),
                        'action_method' => 'sendFailedPaymentRecovery',
                        'action_id' => $attempt->id,
                    ]);
                });

            // Today's appointments
            Appointment::with('contact')
                ->where('user_id', $userId)
                ->whereIn('status', ['scheduled', 'confirmed'])
                ->whereDate('start_datetime', today())
                ->orderBy('start_datetime')
                ->limit(3)
                ->get()
                ->each(function ($appt) use ($items) {
                    $clientName = optional($appt->contact)->name ?? ($appt->attendee_name ?? 'Client');
                    $time       = $appt->start_datetime->format('g:i A');
                    $label      = ucfirst($appt->status);
                    $items->push([
                        'type'         => 'appointment',
                        'border_color' => '#22c55e',
                        'title'        => ($appt->title ?? 'Appointment') . ' with ' . $clientName,
                        'subtitle'     => 'Today at ' . $time . ' · ' . $label,
                        'action_label' => $label,
                        'action_color' => '#f0fdf4',
                        'action_text'  => '#16a34a',
                        'action_url'   => route('filament.dashboard.pages.appointment-calendar'),
                    ]);
                });

            Appointment::where('user_id', $userId)
                ->where('booked_via', 'like', 'campaign_%')
                ->where('created_at', '>=', now()->subDays(7))
                ->latest()
                ->limit(1)
                ->get()
                ->each(function ($appointment) use ($items) {
                    $campaign = str($appointment->booked_via)->after('campaign_')->replace('_', ' ')->title();
                    $items->push([
                        'type'         => 'campaign_result',
                        'border_color' => '#14b8a6',
                        'title'        => $campaign . ' campaign generated a booking',
                        'subtitle'     => ($appointment->attendee_name ?? 'Client') . ' booked ' . $appointment->created_at->diffForHumans(),
                        'action_label' => 'View calendar',
                        'action_color' => '#ccfbf1',
                        'action_text'  => '#0f766e',
                        'action_url'   => route('filament.dashboard.pages.appointment-calendar'),
                    ]);
                });

            $feedback = app(CampaignAudienceService::class)
                ->pendingOutcomeFeedbackForUser(Auth::user(), 1)[0] ?? null;

            if ($feedback) {
                $items->push([
                    'type'         => 'campaign_feedback',
                    'border_color' => '#0f766e',
                    'title'        => 'How did ' . $feedback['name'] . ' perform?',
                    'subtitle'     => 'Your feedback helps WhizIQ improve future campaign drafts.',
                    'action_label' => 'Worked',
                    'action_color' => '#dcfce7',
                    'action_text'  => '#166534',
                    'action_method' => 'recordCampaignDraftOutcome',
                    'action_id' => $feedback['id'],
                    'action_value' => 'worked',
                    'secondary_actions' => [
                        [
                            'label' => 'Mixed',
                            'method' => 'recordCampaignDraftOutcome',
                            'id' => $feedback['id'],
                            'value' => 'mixed',
                        ],
                        [
                            'label' => 'Needs work',
                            'method' => 'recordCampaignDraftOutcome',
                            'id' => $feedback['id'],
                            'value' => 'needs_work',
                        ],
                    ],
                ]);
            }

            Task::where('user_id', $userId)
                ->where('status', '!=', 'completed')
                ->whereNotNull('due_date')
                ->whereDate('due_date', '<=', today())
                ->orderByRaw("CASE priority WHEN 'urgent' THEN 0 WHEN 'high' THEN 1 WHEN 'medium' THEN 2 ELSE 3 END")
                ->orderBy('due_date')
                ->limit(2)
                ->get()
                ->each(function ($task) use ($items) {
                    $items->push([
                        'type'         => 'task',
                        'border_color' => $task->priority === 'urgent' ? '#dc2626' : '#64748b',
                        'title'        => $task->title,
                        'subtitle'     => $task->isOverdue() ? 'Task overdue' : 'Task due today',
                        'action_label' => 'Complete',
                        'action_color' => '#f8fafc',
                        'action_text'  => '#334155',
                        'action_url'   => route('filament.dashboard.resources.tasks.view', ['record' => $task]),
                        'action_method' => 'completeTask',
                        'action_id' => $task->id,
                    ]);
                });

            if (! $this->addCampaignRecommendation($items)) {
                $this->addCalendarGapSuggestion($items, $userId);
            }

            return [
                'items'       => $items->all(),
                'total_items' => $items->count(),
            ];
        });
    }

    public function sendInvoiceReminder(int $invoiceId): void
    {
        $invoice = ClientInvoice::where('user_id', Auth::id())->find($invoiceId);

        if (! $invoice) {
            Notification::make()
                ->title('Invoice not found')
                ->danger()
                ->send();

            return;
        }

        $sent = app(InvoiceReminderService::class)->sendManualReminder($invoice);

        $notification = Notification::make()
            ->title($sent ? 'Reminder sent' : 'Reminder not sent')
            ->body($sent ? 'The client reminder was sent and a collection follow-up was created if needed.' : 'This invoice cannot receive another reminder yet.');

        if ($sent) {
            $notification->success();
        } else {
            $notification->warning();
        }

        $notification->send();

        $this->clearAttentionCache();
    }

    public function sendPaymentLink(int $invoiceId): void
    {
        $invoice = ClientInvoice::where('user_id', Auth::id())->find($invoiceId);

        if (! $invoice) {
            Notification::make()
                ->title('Invoice not found')
                ->danger()
                ->send();

            return;
        }

        $sent = app(InvoiceReminderService::class)->sendPaymentLink($invoice);

        $notification = Notification::make()
            ->title($sent ? 'Payment link sent' : 'Payment link not sent')
            ->body($sent ? 'The client received a direct payment link.' : 'This invoice may be paid already or missing a client email.');

        if ($sent) {
            $notification->success();
        } else {
            $notification->warning();
        }

        $notification->send();

        $this->clearAttentionCache();
    }

    public function sendFailedPaymentRecovery(int $attemptId): void
    {
        $attempt = ClientPaymentAttempt::where('user_id', Auth::id())->find($attemptId);

        if (! $attempt) {
            Notification::make()
                ->title('Payment attempt not found')
                ->danger()
                ->send();

            return;
        }

        $sent = app(ClientPaymentRecoveryService::class)->recoverAttempt($attempt);

        $notification = Notification::make()
            ->title($sent ? 'Recovery reminder sent' : 'Recovery reminder not sent')
            ->body($sent ? 'The client was reminded and an owner follow-up was created.' : 'This payment may already be recovered or cannot receive a reminder.');

        if ($sent) {
            $notification->success();
        } else {
            $notification->warning();
        }

        $notification->send();

        $this->clearAttentionCache();
    }

    public function completeFollowUp(int $reminderId): void
    {
        $reminder = FollowUpReminder::where('user_id', Auth::id())->find($reminderId);

        if (! $reminder) {
            Notification::make()
                ->title('Follow-up not found')
                ->danger()
                ->send();

            return;
        }

        $reminder->markAsCompleted();

        Notification::make()
            ->title('Follow-up completed')
            ->success()
            ->send();

        $this->clearAttentionCache();
    }

    public function completeTask(int $taskId): void
    {
        $task = Task::where('user_id', Auth::id())->find($taskId);

        if (! $task) {
            Notification::make()
                ->title('Task not found')
                ->danger()
                ->send();

            return;
        }

        $task->markAsCompleted();

        Notification::make()
            ->title('Task completed')
            ->success()
            ->send();

        $this->clearAttentionCache();
    }

    public function snoozeCampaignRecommendation(int $audienceId): void
    {
        $audience = CampaignAudience::where('user_id', Auth::id())->find($audienceId);

        if (! $audience) {
            Notification::make()
                ->title('Campaign audience not found')
                ->danger()
                ->send();

            return;
        }

        app(CampaignAudienceService::class)->snoozeRecommendation($audience);

        Notification::make()
            ->title('Campaign recommendation snoozed')
            ->body('This recommendation will come back in a few days if it is still useful.')
            ->success()
            ->send();

        $this->clearAttentionCache();
    }

    public function dismissCampaignRecommendation(int $audienceId): void
    {
        $audience = CampaignAudience::where('user_id', Auth::id())->find($audienceId);

        if (! $audience) {
            Notification::make()
                ->title('Campaign audience not found')
                ->danger()
                ->send();

            return;
        }

        app(CampaignAudienceService::class)->dismissRecommendation($audience);

        Notification::make()
            ->title('Campaign recommendation dismissed')
            ->body('This recommendation will stay hidden until the audience is launched or reset.')
            ->success()
            ->send();

        $this->clearAttentionCache();
    }

    public function dismissPreparedCampaignDraft(int $draftId): void
    {
        $draft = EmailCampaign::where('user_id', Auth::id())
            ->where('automation_source', 'campaign_recommendation')
            ->where('status', 'draft')
            ->with('campaignAudience')
            ->find($draftId);

        if (! $draft) {
            Notification::make()
                ->title('Campaign draft not found')
                ->danger()
                ->send();

            return;
        }

        app(CampaignAudienceService::class)->dismissPreparedDraft($draft);

        Notification::make()
            ->title('Campaign draft dismissed')
            ->body('WhizIQ will pause this audience for a few days before suggesting it again.')
            ->success()
            ->send();

        $this->clearAttentionCache();
    }

    public function recordCampaignDraftOutcome(int $campaignId, string $outcome): void
    {
        $campaign = EmailCampaign::where('user_id', Auth::id())
            ->where('automation_source', 'campaign_recommendation')
            ->with('campaignAudience')
            ->find($campaignId);

        if (! $campaign) {
            Notification::make()
                ->title('Campaign not found')
                ->danger()
                ->send();

            return;
        }

        app(CampaignAudienceService::class)->recordDraftOutcome($campaign, $outcome);

        Notification::make()
            ->title('Campaign feedback saved')
            ->body('WhizIQ will use this to improve future campaign drafts.')
            ->success()
            ->send();

        $this->clearAttentionCache();
    }

    protected function clearAttentionCache(): void
    {
        Cache::forget("attention_digest_" . Auth::id() . '_' . now()->format('Y-m-d-H'));
    }

    protected function addCalendarGapSuggestion($items, int $userId): void
    {
        $bookingSetting = BookingSetting::where('user_id', $userId)
            ->where('is_booking_enabled', true)
            ->first();

        if (! $bookingSetting) {
            return;
        }

        $appointmentType = AppointmentType::where('user_id', $userId)
            ->where('is_active', true)
            ->orderBy('duration_minutes')
            ->first();

        if (! $appointmentType) {
            return;
        }

        $availability = app(AvailabilityService::class);
        $bestGap = null;

        for ($dayOffset = 1; $dayOffset <= 7; $dayOffset++) {
            $date = now()->copy()->addDays($dayOffset)->startOfDay();
            $slots = $availability->getAvailableSlots(
                $userId,
                $date,
                (int) $appointmentType->total_duration_minutes,
                (int) ($bookingSetting->min_booking_notice_hours ?? 0)
            );

            $appointmentCount = Appointment::where('user_id', $userId)
                ->whereIn('status', ['scheduled', 'confirmed'])
                ->whereDate('start_datetime', $date)
                ->count();

            if (count($slots) < 3 || $appointmentCount > 1) {
                continue;
            }

            if (! $bestGap || count($slots) > $bestGap['slots_count']) {
                $bestGap = [
                    'date' => $date,
                    'slots_count' => count($slots),
                    'appointment_count' => $appointmentCount,
                ];
            }
        }

        if (! $bestGap) {
            return;
        }

        $date = $bestGap['date'];
        $items->push([
            'type'         => 'calendar_gap',
            'border_color' => '#0ea5e9',
            'title'        => 'Open slots on ' . $date->format('l'),
            'subtitle'     => $bestGap['slots_count'] . ' bookable times available' .
                ($bestGap['appointment_count'] === 0 ? ' - no appointments yet' : ' - light day'),
            'action_label' => 'Email clients',
            'action_color' => '#e0f2fe',
            'action_text'  => '#0369a1',
            'action_url'   => route('filament.dashboard.pages.email-composer-page', [
                'campaign' => 'calendar_gap',
                'date' => $date->toDateString(),
            ]),
        ]);
    }

    protected function addCampaignRecommendation($items): bool
    {
        $user = Auth::user();

        if (! $user) {
            return false;
        }

        $recommendation = app(CampaignAudienceService::class)
            ->recommendationsForUser($user)['recommended'] ?? null;

        if (! $recommendation || $recommendation['score'] < 35) {
            return false;
        }

        $preparedDraft = $recommendation['prepared_campaign_draft'] ?? null;

        $items->push([
            'type'         => 'campaign_recommendation',
            'border_color' => '#2563eb',
            'title'        => ($preparedDraft ? 'Review draft for ' : 'Launch ') . $recommendation['name'],
            'subtitle'     => $recommendation['reason'],
            'action_label' => $preparedDraft ? 'Review draft' : 'Launch campaign',
            'action_color' => '#dbeafe',
            'action_text'  => '#1d4ed8',
            'action_url'   => route('filament.dashboard.pages.email-composer-page', $preparedDraft ? [
                'draft' => $preparedDraft['id'],
            ] : [
                'audience' => $recommendation['slug'],
            ]),
            'secondary_actions' => [
                [
                    'label' => 'Snooze',
                    'method' => 'snoozeCampaignRecommendation',
                    'id' => $recommendation['id'],
                ],
                [
                    'label' => $preparedDraft ? 'Dismiss draft' : 'Dismiss',
                    'method' => $preparedDraft ? 'dismissPreparedCampaignDraft' : 'dismissCampaignRecommendation',
                    'id' => $preparedDraft['id'] ?? $recommendation['id'],
                ],
            ],
        ]);

        return true;
    }

    public static function canView(): bool
    {
        return true;
    }
}
