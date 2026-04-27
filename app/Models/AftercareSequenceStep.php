<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AftercareSequenceStep extends Model
{
    use HasFactory;

    protected $fillable = [
        'aftercare_sequence_id',
        'step_order',
        'delay_days',
        'delay_hours',
        'delay_minutes',
        'send_whatsapp',
        'send_sms',
        'message_body',
        'include_rebooking_link',
        'is_active',
    ];

    protected $casts = [
        'send_whatsapp'          => 'boolean',
        'send_sms'               => 'boolean',
        'include_rebooking_link' => 'boolean',
        'is_active'              => 'boolean',
        'delay_days'             => 'integer',
        'delay_hours'            => 'integer',
        'delay_minutes'          => 'integer',
        'step_order'             => 'integer',
    ];

    public function sequence(): BelongsTo
    {
        return $this->belongsTo(AftercareSequence::class, 'aftercare_sequence_id');
    }

    public function getTotalDelayInMinutes(): int
    {
        return ($this->delay_days * 1440)
            + ($this->delay_hours * 60)
            + $this->delay_minutes;
    }

    public function getDelayLabelAttribute(): string
    {
        $parts = [];
        if ($this->delay_days)    $parts[] = $this->delay_days    . 'd';
        if ($this->delay_hours)   $parts[] = $this->delay_hours   . 'h';
        if ($this->delay_minutes) $parts[] = $this->delay_minutes . 'm';

        return $parts ? implode(' ', $parts) . ' after appointment' : 'Immediately';
    }

    public function replaceVariables(string $text, Appointment $appointment): string
    {
        $appointmentType = $appointment->appointmentType;
        $user            = $appointment->user;

        $startDt = $appointment->start_datetime instanceof \Carbon\Carbon
            ? $appointment->start_datetime
            : \Carbon\Carbon::parse($appointment->start_datetime);

        $firstName = explode(' ', trim($appointment->attendee_name ?? ''))[0] ?? '';

        $rebookingLink = '';
        if ($this->include_rebooking_link && $user?->username) {
            $rebookingLink = url('/book/' . $user->username);
        }

        $replacements = [
            '{{client_name}}'       => $appointment->attendee_name       ?? '',
            '{{client_first_name}}' => $firstName,
            '{{appointment_type}}'  => $appointmentType?->name           ?? '',
            '{{appointment_date}}'  => $startDt->format('D, d M Y'),
            '{{appointment_time}}'  => $startDt->format('g:i A T'),
            '{{business_name}}'     => $user?->public_name ?? $user?->name ?? config('app.name'),
            '{{business_phone}}'    => $user?->phone                     ?? '',
            '{{business_email}}'    => $user?->email                     ?? '',
            '{{rebooking_link}}'    => $rebookingLink,
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $text);
    }
}
