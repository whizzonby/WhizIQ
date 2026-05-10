<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class CompanyProfile extends Model
{
    protected $fillable = [
        'user_id',
        'company_name',
        'tagline',
        'address_line1',
        'address_line2',
        'city',
        'state',
        'zip',
        'country',
        'phone',
        'email',
        'website',
        'logo_path',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getLogoUrlAttribute(): ?string
    {
        if (!$this->logo_path) {
            return null;
        }

        return Storage::disk('public')->url($this->logo_path);
    }

    public function getFormattedAddressAttribute(): string
    {
        $parts = array_filter([
            $this->address_line1,
            $this->address_line2,
            implode(', ', array_filter([$this->city, $this->state, $this->zip])),
            $this->country,
        ]);

        return implode("\n", $parts);
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->company_name ?? $this->user?->name ?? 'Your Company';
    }
}
