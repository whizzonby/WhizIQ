<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class AftercareTemplate extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'description',
        'email_subject',
        'email_body',
        'sms_message',
        'whatsapp_message',
        'send_via_email',
        'send_via_sms',
        'send_via_whatsapp',
        'send_after_minutes',
        'send_after_hours',
        'send_after_days',
        'available_variables',
        'include_rebooking_link',
        'rebooking_cta_text',
        'is_active',
    ];

    protected $casts = [
        'send_via_email' => 'boolean',
        'send_via_sms' => 'boolean',
        'send_via_whatsapp' => 'boolean',
        'send_after_minutes' => 'integer',
        'send_after_hours' => 'integer',
        'send_after_days' => 'integer',
        'available_variables' => 'array',
        'include_rebooking_link' => 'boolean',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function appointmentTypes(): HasMany
    {
        return $this->hasMany(AppointmentType::class, 'aftercare_template_id');
    }

    // Scopes
    public function scopeForUser(Builder $query, $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    // Helper Methods
    public function getTotalDelayInMinutes(): int
    {
        return ($this->send_after_days * 24 * 60)
             + ($this->send_after_hours * 60)
             + $this->send_after_minutes;
    }

    public function getDelayDescription(): string
    {
        $parts = [];

        if ($this->send_after_days > 0) {
            $parts[] = $this->send_after_days . ' ' . str('day')->plural($this->send_after_days);
        }

        if ($this->send_after_hours > 0) {
            $parts[] = $this->send_after_hours . ' ' . str('hour')->plural($this->send_after_hours);
        }

        if ($this->send_after_minutes > 0) {
            $parts[] = $this->send_after_minutes . ' ' . str('minute')->plural($this->send_after_minutes);
        }

        if (empty($parts)) {
            return 'Immediately after appointment';
        }

        return implode(', ', $parts) . ' after appointment';
    }

    public function getActiveChannels(): array
    {
        $channels = [];

        if ($this->send_via_email) {
            $channels[] = 'Email';
        }

        if ($this->send_via_sms) {
            $channels[] = 'SMS';
        }

        if ($this->send_via_whatsapp) {
            $channels[] = 'WhatsApp';
        }

        return $channels;
    }

    public function getActiveChannelsString(): string
    {
        $channels = $this->getActiveChannels();

        if (empty($channels)) {
            return 'No channels';
        }

        return implode(', ', $channels);
    }

    public function replaceVariables(string $content, array $data): string
    {
        foreach ($data as $key => $value) {
            $content = str_replace('{{' . $key . '}}', $value, $content);
        }

        return $content;
    }

    public function getAvailableVariablesList(): array
    {
        return [
            'client_name' => 'Client\'s full name',
            'client_first_name' => 'Client\'s first name',
            'appointment_type' => 'Type of appointment/service',
            'appointment_date' => 'Date of the appointment',
            'appointment_time' => 'Time of the appointment',
            'business_name' => 'Your business name',
            'business_phone' => 'Your business phone',
            'business_email' => 'Your business email',
            'rebooking_link' => 'Link to book next appointment',
        ];
    }

    public function prepareMessageForAppointment(Appointment $appointment, string $channel = 'email'): array
    {
        // Ensure relationships are loaded
        if (!$appointment->relationLoaded('appointmentType')) {
            $appointment->load('appointmentType');
        }
        if (!$appointment->relationLoaded('user')) {
            $appointment->load('user');
        }

        // Safely extract client name
        $clientName = $appointment->attendee_name ?? 'Valued Client';
        $clientFirstName = !empty($clientName) ? explode(' ', $clientName)[0] : 'Valued';

        // Safely extract appointment type name
        $appointmentTypeName = 'Appointment';
        if ($appointment->appointmentType) {
            $appointmentTypeName = $appointment->appointmentType->name ?? 'Appointment';
        }

        // Safely format dates
        $appointmentDate = 'N/A';
        $appointmentTime = 'N/A';
        if ($appointment->start_datetime) {
            try {
                $appointmentDate = $appointment->start_datetime->format('F j, Y');
                $appointmentTime = $appointment->start_datetime->format('g:i A');
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::warning('Failed to format appointment datetime', [
                    'appointment_id' => $appointment->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Safely extract business information
        $businessName = 'Our Business';
        $businessPhone = '';
        $businessEmail = '';
        if ($appointment->user) {
            $businessName = $appointment->user->name ?? 'Our Business';
            $businessPhone = $appointment->user->phone ?? '';
            $businessEmail = $appointment->user->email ?? '';
        }

        // Get rebooking link
        $rebookingLink = url('/book');
        if ($appointment->user && $appointment->user->relationLoaded('bookingSetting')) {
            $rebookingLink = $appointment->user->bookingSetting?->booking_url ?? url('/book');
        } elseif ($appointment->user) {
            $appointment->user->load('bookingSetting');
            $rebookingLink = $appointment->user->bookingSetting?->booking_url ?? url('/book');
        }

        // Gather data for variable replacement
        $data = [
            'client_name' => $clientName,
            'client_first_name' => $clientFirstName,
            'appointment_type' => $appointmentTypeName,
            'appointment_date' => $appointmentDate,
            'appointment_time' => $appointmentTime,
            'business_name' => $businessName,
            'business_phone' => $businessPhone,
            'business_email' => $businessEmail,
            'rebooking_link' => $rebookingLink,
        ];

        $result = [];

        switch ($channel) {
            case 'email':
                $result['subject'] = $this->replaceVariables($this->email_subject ?? 'Aftercare Instructions', $data);
                $result['body'] = $this->replaceVariables($this->email_body ?? '', $data);
                break;

            case 'sms':
                $result['message'] = $this->replaceVariables($this->sms_message ?? '', $data);
                break;

            case 'whatsapp':
                $result['message'] = $this->replaceVariables($this->whatsapp_message ?? '', $data);
                break;

            default:
                \Illuminate\Support\Facades\Log::warning('Unknown channel for aftercare message', [
                    'channel' => $channel,
                    'template_id' => $this->id,
                ]);
                break;
        }

        return $result;
    }
}
