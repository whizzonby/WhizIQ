<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class BookingSetting extends Model
{
    protected $fillable = [
        'user_id',
        'booking_slug',
        'is_booking_enabled',
        'display_name',
        'welcome_message',
        'business_address',
        'business_city',
        'business_country',
        'logo_url',
        'brand_color',
        'timezone',
        'currency',
        'require_approval',
        'min_booking_notice_hours',
        'max_booking_days_ahead',
        'notify_email',
        'notify_sms',
        'send_reminder_hours_before',
        'meeting_platform',
        'meeting_link',
        'meeting_instructions',
        'payment_instructions',
        'payment_link',
        'show_payment_in_email',
        'zoom_client_id',
        'zoom_client_secret',
        'zoom_access_token',
        'zoom_refresh_token',
        'zoom_token_expires_at',
        'google_meet_enabled',
    ];

    protected $casts = [
        'is_booking_enabled' => 'boolean',
        'require_approval' => 'boolean',
        'min_booking_notice_hours' => 'integer',
        'max_booking_days_ahead' => 'integer',
        'send_reminder_hours_before' => 'integer',
        'show_payment_in_email' => 'boolean',
        'google_meet_enabled' => 'boolean',
        'zoom_token_expires_at' => 'datetime',
        'zoom_client_id' => 'encrypted',
        'zoom_client_secret' => 'encrypted',
        'zoom_access_token' => 'encrypted',
        'zoom_refresh_token' => 'encrypted',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(BusinessReview::class, 'user_id', 'user_id');
    }

    public function approvedReviews(): HasMany
    {
        return $this->reviews()->approved();
    }

    // Helper Methods
    public function getBookingUrlAttribute(): string
    {
        return url('book/' . $this->booking_slug);
    }

    public function getReviewCountAttribute(): int
    {
        return $this->approvedReviews()->count();
    }

    public function getAverageRatingAttribute(): float
    {
        return (float) ($this->approvedReviews()->avg('rating') ?? 0);
    }

    public function isWithinBookingWindow(\Carbon\Carbon $date): bool
    {
        $minDate = now()->addHours($this->min_booking_notice_hours);
        $maxDate = now()->addDays($this->max_booking_days_ahead);

        return $date->between($minDate, $maxDate);
    }

    // Boot method
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($settings) {
            if (empty($settings->booking_slug)) {
                $user = $settings->user ?? \App\Models\User::find($settings->user_id);
                $baseSlug = Str::slug($user->name);
                $slug = $baseSlug;
                $counter = 1;

                while (self::where('booking_slug', $slug)->exists()) {
                    $slug = $baseSlug . '-' . $counter;
                    $counter++;
                }

                $settings->booking_slug = $slug;
            }
        });

        static::created(function ($settings) {
            // Automatically create default availability schedule (Mon-Fri, 9 AM - 5 PM)
            // Only if no schedule exists yet
            $hasSchedule = AvailabilitySchedule::where('user_id', $settings->user_id)->exists();
            if (!$hasSchedule) {
                $service = new \App\Services\AvailabilityService();
                $service->createDefaultSchedule($settings->user_id);
            }
        });
    }
}
