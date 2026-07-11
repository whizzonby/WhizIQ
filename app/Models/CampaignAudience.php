<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CampaignAudience extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
        'slug',
        'campaign_preset',
        'description',
        'filters',
        'last_launched_at',
        'recommendation_dismissed_at',
        'recommendation_snoozed_until',
        'recommendation_history',
        'recommendation_cadence_days',
    ];

    protected $casts = [
        'filters' => 'array',
        'last_launched_at' => 'datetime',
        'recommendation_dismissed_at' => 'datetime',
        'recommendation_snoozed_until' => 'datetime',
        'recommendation_history' => 'array',
        'recommendation_cadence_days' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }
}
