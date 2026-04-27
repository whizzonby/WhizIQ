<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AftercareLog extends Model
{
    use HasFactory;

    const STATUS_PENDING = 'pending';
    const STATUS_SENT    = 'sent';
    const STATUS_FAILED  = 'failed';
    const STATUS_SKIPPED = 'skipped';

    protected $fillable = [
        'appointment_id',
        'user_id',
        'contact_id',
        'aftercare_template_id',
        'aftercare_sequence_step_id',
        'channel',
        'status',
        'scheduled_at',
        'sent_at',
        'error_message',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'sent_at'      => 'datetime',
    ];

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(AftercareTemplate::class, 'aftercare_template_id');
    }

    public function sequenceStep(): BelongsTo
    {
        return $this->belongsTo(AftercareSequenceStep::class, 'aftercare_sequence_step_id');
    }

    public function markSent(): void
    {
        $this->update(['status' => self::STATUS_SENT, 'sent_at' => now()]);
    }

    public function markFailed(string $error): void
    {
        $this->update(['status' => self::STATUS_FAILED, 'error_message' => $error]);
    }

    public function markSkipped(string $reason): void
    {
        $this->update(['status' => self::STATUS_SKIPPED, 'error_message' => $reason]);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }
}
