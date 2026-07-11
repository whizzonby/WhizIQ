<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationPreference extends Model
{
    protected $fillable = [
        'user_id',
        'owner_phone',
        'preferred_channel',
        'digest_enabled',
        'digest_time',
        'alert_payment_received',
        'alert_overdue_invoice',
        'alert_stale_deals',
        'alert_follow_up_due',
        'alert_new_booking',
        'alert_task_due',
        'alert_campaign_draft_prepared',
        'client_appointment_reminders',
        'client_invoice_reminders',
        'client_aftercare_enabled',
        'client_booking_confirmation',
    ];

    protected $casts = [
        'digest_enabled'               => 'boolean',
        'alert_payment_received'       => 'boolean',
        'alert_overdue_invoice'        => 'boolean',
        'alert_stale_deals'            => 'boolean',
        'alert_follow_up_due'          => 'boolean',
        'alert_new_booking'            => 'boolean',
        'alert_task_due'               => 'boolean',
        'alert_campaign_draft_prepared' => 'boolean',
        'client_appointment_reminders' => 'boolean',
        'client_invoice_reminders'     => 'boolean',
        'client_aftercare_enabled'     => 'boolean',
        'client_booking_confirmation'  => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get or create preferences for a user with sensible defaults.
     */
    public static function forUser(int $userId): self
    {
        return static::firstOrCreate(
            ['user_id' => $userId],
            [
                'preferred_channel'            => 'sms',
                'digest_enabled'               => true,
                'digest_time'                  => '08:00',
                'alert_payment_received'       => true,
                'alert_overdue_invoice'        => true,
                'alert_stale_deals'            => true,
                'alert_follow_up_due'          => true,
                'alert_new_booking'            => true,
                'alert_task_due'               => false,
                'alert_campaign_draft_prepared' => true,
                'client_appointment_reminders' => true,
                'client_invoice_reminders'     => true,
                'client_aftercare_enabled'     => true,
                'client_booking_confirmation'  => true,
            ]
        );
    }

    /**
     * Whether the owner has a phone configured for self-notifications.
     */
    public function hasOwnerPhone(): bool
    {
        return !empty($this->owner_phone);
    }

    /**
     * Whether SMS channel is active for owner messages.
     */
    public function usesSms(): bool
    {
        return in_array($this->preferred_channel, ['sms', 'both']);
    }

    /**
     * Whether WhatsApp channel is active for owner messages.
     */
    public function usesWhatsApp(): bool
    {
        return in_array($this->preferred_channel, ['whatsapp', 'both']);
    }
}
