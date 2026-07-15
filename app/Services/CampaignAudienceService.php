<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\AppointmentType;
use App\Models\BookingSetting;
use App\Models\CampaignAudience;
use App\Models\Contact;
use App\Models\EmailCampaign;
use App\Models\EmailLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class CampaignAudienceService
{
    public function ensureDefaultAudiences(User $user): Collection
    {
        return collect($this->defaultAudienceDefinitions())
            ->map(fn (array $definition): CampaignAudience => CampaignAudience::updateOrCreate(
                ['user_id' => $user->id, 'slug' => $definition['slug']],
                $definition
            ));
    }

    public function findForUser(User $user, string $slug): ?CampaignAudience
    {
        $this->ensureDefaultAudiences($user);

        return CampaignAudience::forUser($user->id)->where('slug', $slug)->first();
    }

    public function resolveContacts(CampaignAudience $audience): Collection
    {
        $filters = $audience->filters ?? [];
        $type = $filters['type'] ?? $audience->campaign_preset;

        $query = Contact::where('user_id', $audience->user_id)
            ->where('status', 'active')
            ->whereNotNull('email');

        if (($filters['contact_type'] ?? 'client') !== 'all') {
            $query->where('type', $filters['contact_type'] ?? 'client');
        }

        if ($type === 'rebooking') {
            $query->whereNotNull('last_appointment_at')
                ->where('last_appointment_at', '<=', now()->subDays((int) ($filters['days_since_last_appointment'] ?? 45)))
                ->whereDoesntHave('appointments', function ($appointmentQuery): void {
                    $appointmentQuery->whereIn('status', ['scheduled', 'confirmed'])
                        ->where('start_datetime', '>=', now());
                })
                ->orderBy('last_appointment_at');
        } else {
            $query->latest('last_appointment_at');
        }

        return $query->limit((int) ($filters['limit'] ?? 50))->get();
    }

    public function prefill(CampaignAudience $audience): array
    {
        $contacts = $this->resolveContacts($audience);

        return [
            'contact_ids' => $contacts->pluck('id')->all(),
            'subject' => $this->subject($audience),
            'body' => $this->body($audience),
            'send_now' => true,
        ];
    }

    public function summaryForUser(User $user, ?string $dateFrom = null, ?string $dateTo = null): array
    {
        $audiences = $this->ensureDefaultAudiences($user);
        $audienceIds = $audiences->pluck('id')->all();
        $start = $dateFrom ? Carbon::parse($dateFrom)->startOfDay() : now()->subDays(30)->startOfDay();
        $end = $dateTo ? Carbon::parse($dateTo)->endOfDay() : now()->endOfDay();
        $preparedDrafts = EmailCampaign::where('user_id', $user->id)
            ->whereIn('campaign_audience_id', $audienceIds)
            ->where('automation_source', 'campaign_recommendation')
            ->activePreparedDraft()
            ->latest('prepared_at')
            ->get()
            ->keyBy('campaign_audience_id');

        $emailLogs = EmailLog::where('user_id', $user->id)
            ->where('status', 'sent')
            ->whereBetween('created_at', [$start, $end])
            ->get(['id', 'contact_id', 'metadata', 'created_at']);

        $appointments = Appointment::where('user_id', $user->id)
            ->whereIn('campaign_audience_id', $audienceIds)
            ->whereBetween('created_at', [$start, $end])
            ->with(['appointmentType:id,name,price', 'clientInvoice:id,total_amount,amount_paid,status'])
            ->get()
            ->groupBy('campaign_audience_id');

        return $audiences
            ->map(function (CampaignAudience $audience) use ($emailLogs, $appointments, $preparedDrafts): array {
                $contacts = $this->resolveContacts($audience);
                $audienceEmailLogs = $emailLogs
                    ->filter(fn (EmailLog $log): bool => (int) ($log->metadata['campaign_audience_id'] ?? 0) === $audience->id)
                    ->values();
                $bookings = $appointments->get($audience->id, collect());
                $projected = $this->projectedRevenue($bookings);
                $collected = $this->collectedRevenue($bookings);
                $preparedDraft = $preparedDrafts->get($audience->id);

                return [
                    'id' => $audience->id,
                    'name' => $audience->name,
                    'slug' => $audience->slug,
                    'campaign_preset' => $audience->campaign_preset,
                    'description' => $audience->description,
                    'contact_count' => $contacts->count(),
                    'last_launched_at' => $audience->last_launched_at,
                    'recommendation_dismissed_at' => $audience->recommendation_dismissed_at,
                    'recommendation_snoozed_until' => $audience->recommendation_snoozed_until,
                    'recommendation_history' => collect($audience->recommendation_history ?? [])->take(5)->all(),
                    'recommendation_cadence_days' => $audience->recommendation_cadence_days ?: 7,
                    'prepared_campaign_draft' => $preparedDraft ? [
                        'id' => $preparedDraft->id,
                        'name' => $preparedDraft->name,
                        'prepared_at' => $preparedDraft->prepared_at,
                        'expires_at' => $preparedDraft->expires_at,
                    ] : null,
                    'sent' => $audienceEmailLogs->count(),
                    'booked' => $bookings->count(),
                    'conversion_rate' => $audienceEmailLogs->count() > 0 ? round(($bookings->count() / $audienceEmailLogs->count()) * 100, 1) : 0,
                    'segment_learning' => $this->segmentLearningForAudience($audienceEmailLogs, $bookings),
                    'projected' => $projected,
                    'collected' => $collected,
                    'formatted_projected' => $this->formatMoney($projected),
                    'formatted_collected' => $this->formatMoney($collected),
                ];
            })
            ->sortByDesc(fn (array $audience): float => $audience['collected'])
            ->values()
            ->all();
    }

    public function recommendationsForUser(User $user, ?string $dateFrom = null, ?string $dateTo = null): array
    {
        $audienceStats = collect($this->summaryForUser($user, $dateFrom, $dateTo));
        $openSlotSignal = $this->openSlotSignal($user);

        $recommendations = $audienceStats
            ->map(function (array $audience) use ($openSlotSignal): array {
                $score = 0;
                $reasons = [];

                if ($audience['contact_count'] > 0) {
                    $score += min(40, $audience['contact_count'] * 2);
                    $reasons[] = "{$audience['contact_count']} ready contacts";
                }

                if ($audience['conversion_rate'] > 0) {
                    $score += min(30, (int) round($audience['conversion_rate']));
                    $reasons[] = "{$audience['conversion_rate']}% recent conversion";
                }

                if ($audience['collected'] > 0) {
                    $score += min(20, (int) floor($audience['collected'] / 25));
                    $reasons[] = $audience['formatted_collected'] . ' recently collected';
                }

                if ($audience['campaign_preset'] === 'calendar_gap' && $openSlotSignal['slot_count'] >= 3) {
                    $score += 35;
                    $reasons[] = $openSlotSignal['slot_count'] . ' open slots on ' . $openSlotSignal['label'];
                }

                if ($audience['campaign_preset'] === 'rebooking' && $audience['contact_count'] >= 5) {
                    $score += 20;
                    $reasons[] = 'enough stale clients for a focused rebooking push';
                }

                $learning = $this->recommendationLearningAdjustment($audience);
                $score += $learning['adjustment'];

                if ($learning['reason']) {
                    $reasons[] = $learning['reason'];
                }

                $cadenceDays = $this->normalizeRecommendationCadence((int) ($audience['recommendation_cadence_days'] ?? 7));
                $cadenceAvailableAt = $audience['last_launched_at']?->copy()->addDays($cadenceDays);

                if ($cadenceAvailableAt && $cadenceAvailableAt->isFuture()) {
                    $score = 0;
                    array_unshift($reasons, 'cadence paused until ' . $cadenceAvailableAt->format('M j'));
                }

                if ($audience['recommendation_snoozed_until'] && $audience['recommendation_snoozed_until']->isFuture()) {
                    $score = 0;
                    array_unshift($reasons, 'snoozed until ' . $audience['recommendation_snoozed_until']->format('M j'));
                }

                if ($audience['recommendation_dismissed_at'] && (! $audience['last_launched_at'] || $audience['recommendation_dismissed_at']->gt($audience['last_launched_at']))) {
                    $score = 0;
                    array_unshift($reasons, 'dismissed');
                }

                return array_merge($audience, [
                    'score' => max(0, $score),
                    'learning_adjustment' => $learning['adjustment'],
                    'learning_signals' => $learning['signals'],
                    'reason' => $reasons ? implode(', ', array_slice($reasons, 0, 3)) : 'Build this audience by collecting more client activity.',
                ]);
            })
            ->sortByDesc('score')
            ->values();

        return [
            'recommended' => $recommendations->first(fn (array $audience): bool => $audience['score'] > 0),
            'audiences' => $recommendations->all(),
            'open_slot_signal' => $openSlotSignal,
        ];
    }

    public function markLaunched(CampaignAudience $audience): void
    {
        $audience->forceFill([
            'last_launched_at' => now(),
            'recommendation_dismissed_at' => null,
            'recommendation_snoozed_until' => null,
            'recommendation_history' => $this->appendRecommendationHistory($audience, 'launched'),
        ])->save();
    }

    public function dismissRecommendation(CampaignAudience $audience): void
    {
        $audience->forceFill([
            'recommendation_dismissed_at' => now(),
            'recommendation_snoozed_until' => null,
            'recommendation_history' => $this->appendRecommendationHistory($audience, 'dismissed'),
        ])->save();
    }

    public function snoozeRecommendation(CampaignAudience $audience, int $days = 3): void
    {
        $snoozedUntil = now()->addDays($days);

        $audience->forceFill([
            'recommendation_dismissed_at' => null,
            'recommendation_snoozed_until' => $snoozedUntil,
            'recommendation_history' => $this->appendRecommendationHistory($audience, 'snoozed', [
                'until' => $snoozedUntil->toDateString(),
            ]),
        ])->save();
    }

    public function resetRecommendation(CampaignAudience $audience): void
    {
        $audience->forceFill([
            'recommendation_dismissed_at' => null,
            'recommendation_snoozed_until' => null,
            'recommendation_history' => $this->appendRecommendationHistory($audience, 'restored'),
        ])->save();
    }

    public function updateRecommendationCadence(CampaignAudience $audience, int $days): void
    {
        $days = $this->normalizeRecommendationCadence($days);

        $audience->forceFill([
            'recommendation_cadence_days' => $days,
            'recommendation_history' => $this->appendRecommendationHistory($audience, 'cadence_updated', ['days' => $days]),
        ])->save();
    }

    public function prepareRecommendationDraft(CampaignAudience $audience): ?EmailCampaign
    {
        $this->expirePreparedDrafts($audience->user_id, $audience->id);

        $contacts = $this->resolveContacts($audience);

        if ($contacts->isEmpty()) {
            return null;
        }

        $existingDraft = EmailCampaign::where('user_id', $audience->user_id)
            ->where('campaign_audience_id', $audience->id)
            ->where('automation_source', 'campaign_recommendation')
            ->activePreparedDraft()
            ->first();

        if ($existingDraft) {
            return $existingDraft;
        }

        $prefill = $this->prefill($audience);
        $draft = EmailCampaign::create([
            'user_id' => $audience->user_id,
            'campaign_audience_id' => $audience->id,
            'name' => 'Suggested: ' . $audience->name,
            'description' => 'Prepared automatically by WhizzIQ from campaign recommendation signals. Review before sending.',
            'subject' => $prefill['subject'],
            'body' => $prefill['body'],
            'recipient_type' => 'individual',
            'recipient_ids' => $contacts->pluck('id')->all(),
            'segment_variants' => $this->segmentVariants($audience, $contacts, $prefill['subject'], $prefill['body']),
            'status' => 'draft',
            'total_recipients' => $contacts->count(),
            'from_name' => $audience->user?->name,
            'from_email' => $audience->user?->email,
            'reply_to' => $audience->user?->email,
            'automation_source' => 'campaign_recommendation',
            'prepared_at' => now(),
            'expires_at' => now()->addDays(14),
        ]);

        $audience->forceFill([
            'recommendation_history' => $this->appendRecommendationHistory($audience, 'draft_prepared', [
                'campaign_id' => $draft->id,
                'contacts' => $contacts->count(),
            ]),
        ])->save();

        return $draft;
    }

    public function dismissPreparedDraft(EmailCampaign $draft, string $reason = 'owner_dismissed'): void
    {
        if ($draft->status !== 'draft') {
            return;
        }

        $draft->forceFill([
            'status' => 'cancelled',
            'dismissed_at' => now(),
            'dismissal_reason' => $reason,
        ])->save();

        if ($draft->campaignAudience) {
            $this->snoozeRecommendation($draft->campaignAudience, 7);
            $draft->campaignAudience->forceFill([
                'recommendation_history' => $this->appendRecommendationHistory($draft->campaignAudience, 'draft_dismissed', [
                    'campaign_id' => $draft->id,
                    'reason' => $reason,
                ]),
            ])->save();
        }
    }

    public function rescueStalePreparedDrafts(?int $userId = null, int $daysBeforeExpiry = 3): Collection
    {
        $query = EmailCampaign::with(['user', 'campaignAudience'])
            ->where('automation_source', 'campaign_recommendation')
            ->where('status', 'draft')
            ->whereNull('reviewed_at')
            ->whereNull('dismissed_at')
            ->whereNull('rescued_at')
            ->whereNotNull('expires_at')
            ->whereBetween('expires_at', [now(), now()->addDays($daysBeforeExpiry)]);

        if ($userId) {
            $query->where('user_id', $userId);
        }

        return $query->get()
            ->filter(fn (EmailCampaign $draft): bool => $this->rescuePreparedDraft($draft));
    }

    public function rescuePreparedDraft(EmailCampaign $draft): bool
    {
        if ($draft->status !== 'draft' || $draft->rescued_at || ! $draft->campaignAudience) {
            return false;
        }

        $rewrite = app(OpenAIService::class)->rewriteCampaignDraft($draft->subject, $draft->body, [
            'tone' => 'clear, timely, and helpful',
            'offer' => 'make the next step feel easy',
            'campaign_preset' => $draft->campaignAudience->campaign_preset,
            'extra_instruction' => 'This prepared campaign draft has not been reviewed and will expire soon. Reposition it with a stronger subject, clearer benefit, and a direct review-ready call to action while preserving merge variables and links.',
        ]);

        if (! $rewrite) {
            return false;
        }

        $variants = collect($draft->segment_variants ?? [])
            ->map(function (array $variant, string $segment) use ($draft): array {
                $segmentRewrite = app(OpenAIService::class)->rewriteCampaignDraft(
                    $variant['subject'] ?? $draft->subject,
                    $variant['body'] ?? $draft->body,
                    [
                        'tone' => 'clear, timely, and segment-aware',
                        'offer' => 'make booking feel easy',
                        'campaign_preset' => $draft->campaignAudience?->campaign_preset ?? 'campaign',
                        'extra_instruction' => 'Rescue this ignored prepared draft for the ' . $this->segmentLabel($segment) . ' segment. Preserve merge variables and links.',
                    ]
                );

                if (! $segmentRewrite) {
                    return $variant;
                }

                $variant['subject'] = $segmentRewrite['subject'];
                $variant['body'] = $segmentRewrite['body'];
                $variant['rescued_at'] = now()->toIso8601String();

                return $variant;
            })
            ->all();

        $draft->forceFill([
            'subject' => $rewrite['subject'],
            'body' => $rewrite['body'],
            'segment_variants' => $variants,
            'rescued_at' => now(),
            'rescue_reason' => 'ignored_before_expiry',
            'expires_at' => now()->addDays(7),
        ])->save();

        $draft->campaignAudience->forceFill([
            'recommendation_history' => $this->appendRecommendationHistory($draft->campaignAudience, 'draft_rescued', [
                'campaign_id' => $draft->id,
                'expires_at' => $draft->expires_at?->toDateString(),
            ]),
        ])->save();

        return true;
    }

    public function expireStaleRecommendationDrafts(?int $userId = null): int
    {
        return $this->expirePreparedDrafts($userId);
    }

    public function pendingOutcomeFeedbackForUser(User $user, int $limit = 3): array
    {
        return EmailCampaign::where('user_id', $user->id)
            ->where('automation_source', 'campaign_recommendation')
            ->whereIn('status', ['sent', 'scheduled'])
            ->whereNull('outcome_feedback_at')
            ->whereNotNull('reviewed_at')
            ->where('reviewed_at', '<=', now()->subDays(2))
            ->latest('reviewed_at')
            ->limit($limit)
            ->get()
            ->map(fn (EmailCampaign $campaign): array => [
                'id' => $campaign->id,
                'name' => $campaign->name,
                'audience' => $campaign->campaignAudience?->name,
                'reviewed_at' => $campaign->reviewed_at,
                'sent_at' => $campaign->sent_at,
                'status' => $campaign->status,
                'total' => $campaign->total_recipients,
            ])
            ->all();
    }

    public function recordDraftOutcome(EmailCampaign $campaign, string $outcome): void
    {
        if (! in_array($outcome, ['worked', 'mixed', 'needs_work'], true)) {
            return;
        }

        $campaign->forceFill([
            'outcome_feedback' => $outcome,
            'outcome_feedback_at' => now(),
        ])->save();

        if ($campaign->campaignAudience) {
            $campaign->campaignAudience->forceFill([
                'recommendation_history' => $this->appendRecommendationHistory($campaign->campaignAudience, 'draft_outcome_' . $outcome, [
                    'campaign_id' => $campaign->id,
                ]),
            ])->save();
        }
    }

    public function segmentForContact(Contact $contact): string
    {
        if ($contact->priority === 'vip' || (float) $contact->lifetime_value >= 500) {
            return 'vip';
        }

        if ($contact->last_appointment_at && $contact->last_appointment_at->lte(now()->subDays(45))) {
            return 'stale';
        }

        if ($contact->last_appointment_at && $contact->last_appointment_at->gte(now()->subDays(30))) {
            return 'recent';
        }

        return 'standard';
    }

    public function segmentVariantsForContacts(CampaignAudience $audience, Collection $contacts, string $subject, string $body): array
    {
        return $this->segmentVariants($audience, $contacts, $subject, $body);
    }

    protected function segmentVariants(CampaignAudience $audience, Collection $contacts, string $subject, string $body): array
    {
        return $contacts
            ->map(fn (Contact $contact): string => $this->segmentForContact($contact))
            ->unique()
            ->values()
            ->mapWithKeys(fn (string $segment): array => [
                $segment => [
                    'label' => $this->segmentLabel($segment),
                    'subject' => $this->segmentSubject($subject, $segment),
                    'body' => $this->segmentBody($body, $segment, $audience),
                ],
            ])
            ->all();
    }

    protected function segmentLabel(string $segment): string
    {
        return match ($segment) {
            'vip' => 'VIP clients',
            'stale' => 'Clients due back',
            'recent' => 'Recent clients',
            default => 'Standard clients',
        };
    }

    protected function segmentSubject(string $subject, string $segment): string
    {
        return match ($segment) {
            'vip' => 'A quick note for our valued clients',
            'recent' => 'Thanks for visiting recently',
            default => $subject,
        };
    }

    protected function segmentBody(string $body, string $segment, CampaignAudience $audience): string
    {
        $line = match ($segment) {
            'vip' => 'As one of our valued clients, we wanted to make sure you saw this first.',
            'stale' => 'It has been a little while, and we would be happy to get you back on the calendar.',
            'recent' => 'Thanks again for visiting recently. If you would like to plan your next appointment, we have made it easy.',
            default => null,
        };

        if (! $line) {
            return $body;
        }

        if (str_contains($body, '</p><p>')) {
            return preg_replace('/<\/p><p>/', '</p><p>' . e($line) . '</p><p>', $body, 1) ?? $body;
        }

        return '<p>' . e($line) . '</p>' . $body;
    }

    protected function expirePreparedDrafts(?int $userId = null, ?int $audienceId = null): int
    {
        $query = EmailCampaign::where('automation_source', 'campaign_recommendation')
            ->where('status', 'draft')
            ->whereNull('reviewed_at')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now());

        if ($userId) {
            $query->where('user_id', $userId);
        }

        if ($audienceId) {
            $query->where('campaign_audience_id', $audienceId);
        }

        $expired = 0;

        $query->with('campaignAudience')->get()->each(function (EmailCampaign $draft) use (&$expired): void {
            $draft->forceFill([
                'status' => 'cancelled',
                'dismissed_at' => now(),
                'dismissal_reason' => 'expired',
            ])->save();

            if ($draft->campaignAudience) {
                $draft->campaignAudience->forceFill([
                    'recommendation_history' => $this->appendRecommendationHistory($draft->campaignAudience, 'draft_expired', [
                        'campaign_id' => $draft->id,
                    ]),
                ])->save();
            }

            $expired++;
        });

        return $expired;
    }

    protected function segmentLearningForAudience(Collection $emailLogs, Collection $bookings): array
    {
        $segments = collect();

        $emailLogs->each(function (EmailLog $log) use ($segments): void {
            $segment = $this->segmentFromEmailLog($log);
            $row = $segments->get($segment, ['segment' => $segment, 'label' => $this->segmentLabel($segment), 'sent' => 0, 'booked' => 0, 'conversion_rate' => 0]);
            $row['sent']++;
            $segments->put($segment, $row);
        });

        $logsByContact = $emailLogs->filter(fn (EmailLog $log): bool => filled($log->contact_id))->sortByDesc('created_at')->groupBy('contact_id');

        $bookings->each(function (Appointment $appointment) use ($segments, $logsByContact): void {
            if (! $appointment->contact_id) {
                return;
            }

            $matchingLog = $logsByContact->get($appointment->contact_id, collect())
                ->first(fn (EmailLog $log): bool => $log->created_at->lte($appointment->created_at));

            if (! $matchingLog) {
                return;
            }

            $segment = $this->segmentFromEmailLog($matchingLog);
            $row = $segments->get($segment, ['segment' => $segment, 'label' => $this->segmentLabel($segment), 'sent' => 0, 'booked' => 0, 'conversion_rate' => 0]);
            $row['booked']++;
            $segments->put($segment, $row);
        });

        $segmentRows = $segments->map(function (array $segment): array {
            $segment['conversion_rate'] = $segment['sent'] > 0 ? round(($segment['booked'] / $segment['sent']) * 100, 1) : 0;

            return $segment;
        })->values();

        $strongest = $segmentRows->filter(fn (array $segment): bool => $segment['sent'] >= 3 || $segment['booked'] > 0)
            ->sortByDesc(fn (array $segment): float => ($segment['conversion_rate'] * 1000) + $segment['booked'])
            ->first();
        $weakest = $segmentRows->filter(fn (array $segment): bool => $segment['sent'] >= 5 && $segment['conversion_rate'] <= 5)
            ->sortBy(fn (array $segment): float => ($segment['conversion_rate'] * 1000) + $segment['booked'])
            ->first();
        $adjustment = 0;
        $signals = [];

        if ($strongest && $strongest['conversion_rate'] >= 20) {
            $adjustment += 8;
            $signals[] = $strongest['label'] . ' are responding well';
        }

        if ($weakest) {
            $adjustment -= 6;
            $signals[] = $weakest['label'] . ' need better campaign copy';
        }

        return [
            'segments' => $segmentRows->all(),
            'strongest_segment' => $strongest['segment'] ?? null,
            'strongest_label' => $strongest['label'] ?? null,
            'weakest_segment' => $weakest['segment'] ?? null,
            'weakest_label' => $weakest['label'] ?? null,
            'adjustment' => $adjustment,
            'signals' => $signals,
        ];
    }

    protected function segmentFromEmailLog(EmailLog $log): string
    {
        $segment = $log->metadata['campaign_segment'] ?? 'standard';

        return in_array($segment, ['vip', 'stale', 'recent', 'standard'], true) ? $segment : 'standard';
    }

    protected function recommendationLearningAdjustment(array $audience): array
    {
        $history = collect($audience['recommendation_history'] ?? []);
        $score = 0;
        $signals = [];
        $dismissedCount = $history->where('event', 'dismissed')->count();
        $snoozedCount = $history->where('event', 'snoozed')->count();
        $launchedCount = $history->where('event', 'launched')->count();
        $positiveOutcomeCount = $history->where('event', 'draft_outcome_worked')->count();
        $negativeOutcomeCount = $history->where('event', 'draft_outcome_needs_work')->count();

        if ($dismissedCount > 0) {
            $score -= min(15, $dismissedCount * 5);
            $signals[] = 'owners dismissed this before';
        }

        if ($snoozedCount > 0) {
            $score -= min(9, $snoozedCount * 3);
            $signals[] = 'recently snoozed';
        }

        if ($launchedCount > 0 && $audience['booked'] > 0) {
            $score += min(15, $audience['booked'] * 5);
            $signals[] = 'previous launches booked clients';
        }

        if ($positiveOutcomeCount > 0) {
            $score += min(12, $positiveOutcomeCount * 6);
            $signals[] = 'owner said prior draft worked';
        }

        if ($negativeOutcomeCount > 0) {
            $score -= min(12, $negativeOutcomeCount * 6);
            $signals[] = 'owner said prior draft needs work';
        }

        if ($launchedCount > 0 && $audience['sent'] >= 10 && $audience['booked'] === 0) {
            $score -= 8;
            $signals[] = 'previous launch underperformed';
        }

        $segmentLearning = $audience['segment_learning'] ?? [];
        $score += (int) ($segmentLearning['adjustment'] ?? 0);

        foreach (($segmentLearning['signals'] ?? []) as $signal) {
            $signals[] = $signal;
        }

        return [
            'adjustment' => $score,
            'signals' => $signals,
            'reason' => $signals ? implode(', ', array_slice($signals, 0, 2)) : null,
        ];
    }

    protected function appendRecommendationHistory(CampaignAudience $audience, string $event, array $metadata = []): array
    {
        return collect($audience->recommendation_history ?? [])
            ->prepend([
                'event' => $event,
                'label' => str($event)->replace('_', ' ')->title()->toString(),
                'at' => now()->toIso8601String(),
                'metadata' => $metadata,
            ])
            ->take(10)
            ->values()
            ->all();
    }

    protected function normalizeRecommendationCadence(int $days): int
    {
        return in_array($days, [3, 7, 14, 30], true) ? $days : 7;
    }

    protected function subject(CampaignAudience $audience): string
    {
        return match ($audience->campaign_preset) {
            'rebooking' => 'Ready to book your next visit?',
            'calendar_gap' => 'Open appointments available this week',
            default => $audience->name,
        };
    }

    protected function body(CampaignAudience $audience): string
    {
        $bookingUrl = $audience->user?->bookingSetting?->booking_url ?? url('/');
        $separator = str_contains($bookingUrl, '?') ? '&' : '?';
        $bookingLink = $bookingUrl . $separator . 'campaign=' . urlencode($audience->campaign_preset) . '&contact={{contact_id}}&audience=' . $audience->id;
        $ownerName = e($audience->user?->name);

        return match ($audience->campaign_preset) {
            'rebooking' => '<p>Hi {{first_name}},</p><p>It has been a little while since your last visit, and we would love to see you again.</p><p>You can book your next appointment here: <a href="' . e($bookingLink) . '">Book your next appointment</a>.</p><p>Warm regards,<br>' . $ownerName . '</p>',
            'calendar_gap' => '<p>Hi {{first_name}},</p><p>We have a few appointment times available this week.</p><p>If you would like to book in, you can choose a time here: <a href="' . e($bookingLink) . '">Book an appointment</a>.</p><p>Warm regards,<br>' . $ownerName . '</p>',
            default => '<p>Hi {{first_name}},</p><p>We wanted to reach out with an update.</p><p>Warm regards,<br>' . $ownerName . '</p>',
        };
    }

    protected function defaultAudienceDefinitions(): array
    {
        return [
            [
                'name' => 'Clients due to rebook',
                'slug' => 'clients-due-to-rebook',
                'campaign_preset' => 'rebooking',
                'description' => 'Active clients whose last appointment was at least 45 days ago and who do not have a future booking.',
                'filters' => [
                    'type' => 'rebooking',
                    'contact_type' => 'client',
                    'days_since_last_appointment' => 45,
                    'limit' => 50,
                ],
            ],
            [
                'name' => 'Recent active clients',
                'slug' => 'recent-active-clients',
                'campaign_preset' => 'calendar_gap',
                'description' => 'Active clients with email addresses, sorted by recent appointment activity.',
                'filters' => [
                    'type' => 'calendar_gap',
                    'contact_type' => 'client',
                    'limit' => 25,
                ],
            ],
        ];
    }

    protected function projectedRevenue(Collection $appointments): float
    {
        return (float) $appointments->sum(function (Appointment $appointment): float {
            if ($appointment->clientInvoice) {
                return (float) $appointment->clientInvoice->total_amount;
            }

            return (float) ($appointment->appointmentType?->price ?? 0);
        });
    }

    protected function collectedRevenue(Collection $appointments): float
    {
        return (float) $appointments->sum(fn (Appointment $appointment): float => (float) ($appointment->clientInvoice?->amount_paid ?? 0));
    }

    protected function formatMoney(float $amount): string
    {
        return '$' . number_format($amount, 2);
    }

    protected function openSlotSignal(User $user): array
    {
        $bookingSetting = BookingSetting::where('user_id', $user->id)
            ->where('is_booking_enabled', true)
            ->first();

        if (! $bookingSetting) {
            return ['date' => null, 'label' => 'no active booking page', 'slot_count' => 0];
        }

        $appointmentType = AppointmentType::where('user_id', $user->id)
            ->where('is_active', true)
            ->orderBy('duration_minutes')
            ->first();

        if (! $appointmentType) {
            return ['date' => null, 'label' => 'no active service', 'slot_count' => 0];
        }

        $availability = app(AvailabilityService::class);
        $best = ['date' => null, 'label' => 'the next 7 days', 'slot_count' => 0];

        for ($dayOffset = 1; $dayOffset <= 7; $dayOffset++) {
            $date = now()->copy()->addDays($dayOffset)->startOfDay();
            $slots = $availability->getAvailableSlots(
                $user->id,
                $date,
                (int) $appointmentType->total_duration_minutes,
                (int) ($bookingSetting->min_booking_notice_hours ?? 0)
            );

            if (count($slots) > $best['slot_count']) {
                $best = [
                    'date' => $date->toDateString(),
                    'label' => $date->format('l'),
                    'slot_count' => count($slots),
                ];
            }
        }

        return $best;
    }
}
