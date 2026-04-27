<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContactFollowUpRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'contact_id',
        'aftercare_template_id',
        'aftercare_sequence_id',
        'preferred_channel',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

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

    public function sequence(): BelongsTo
    {
        return $this->belongsTo(AftercareSequence::class, 'aftercare_sequence_id');
    }

    public function usesSequence(): bool
    {
        return !is_null($this->aftercare_sequence_id);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
