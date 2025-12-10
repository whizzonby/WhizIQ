<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class BlockedClient extends Model
{
    protected $fillable = [
        'user_id',
        'contact_id',
        'email',
        'phone',
        'name',
        'violation_type',
        'violation_details',
        'violation_date',
        'is_active',
        'blocked_until',
        'resolution_notes',
        'resolved_at',
    ];

    protected $casts = [
        'violation_date' => 'datetime',
        'is_active' => 'boolean',
        'blocked_until' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    // Scopes
    public function scopeForUser(Builder $query, $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->where(function($q) {
                $q->whereNull('blocked_until')
                  ->orWhere('blocked_until', '>', now());
            });
    }

    public function scopeResolved(Builder $query): Builder
    {
        return $query->where('is_active', false)
            ->whereNotNull('resolved_at');
    }

    public function scopeByViolationType(Builder $query, string $type): Builder
    {
        return $query->where('violation_type', $type);
    }

    // Helper Methods
    public function isCurrentlyBlocked(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        // Check if temporary block has expired
        if ($this->blocked_until && $this->blocked_until->isPast()) {
            return false;
        }

        return true;
    }

    public function resolve(string $notes): void
    {
        $this->update([
            'is_active' => false,
            'resolution_notes' => $notes,
            'resolved_at' => now(),
        ]);

        // If linked to contact, check if we should unblock the contact
        if ($this->contact_id) {
            $contact = Contact::find($this->contact_id);
            if ($contact && $contact->is_blocked) {
                // Check if there are any other active blocks
                $hasOtherBlocks = self::where('contact_id', $this->contact_id)
                    ->where('id', '!=', $this->id)
                    ->active()
                    ->exists();

                if (!$hasOtherBlocks) {
                    $contact->unblock($notes);
                }
            }
        }
    }

    public function getViolationTypeLabelAttribute(): string
    {
        return match($this->violation_type) {
            'no_show' => 'No Show',
            'late_cancellation' => 'Late Cancellation',
            'repeated_reschedule' => 'Repeated Reschedule',
            'inappropriate_behavior' => 'Inappropriate Behavior',
            'payment_issue' => 'Payment Issue',
            'other' => 'Other',
            default => ucfirst($this->violation_type),
        };
    }

    public function getViolationTypeColorAttribute(): string
    {
        return match($this->violation_type) {
            'no_show' => 'danger',
            'late_cancellation' => 'warning',
            'repeated_reschedule' => 'warning',
            'inappropriate_behavior' => 'danger',
            'payment_issue' => 'danger',
            'other' => 'gray',
            default => 'gray',
        };
    }

    // Static helper to check if email/phone is blocked
    public static function isBlocked(int $userId, ?string $email = null, ?string $phone = null, ?int $contactId = null): bool
    {
        $query = self::where('user_id', $userId)->active();

        // Check contact_id first (most specific)
        if ($contactId) {
            $query->where('contact_id', $contactId);
        } else {
            // Check if email OR phone matches (either one can trigger a block)
            $query->where(function($q) use ($email, $phone) {
                if ($email) {
                    $q->where('email', $email);
                }
                if ($phone) {
                    $q->orWhere('phone', $phone);
                }
            });
        }

        return $query->exists();
    }

    // Boot method to sync with Contact model
    protected static function boot()
    {
        parent::boot();

        // When a BlockedClient is created with is_active=true and contact_id, block the contact
        static::created(function (BlockedClient $blockedClient) {
            if ($blockedClient->is_active && $blockedClient->contact_id) {
                $contact = Contact::find($blockedClient->contact_id);
                if ($contact && !$contact->is_blocked) {
                    $contact->block(
                        $blockedClient->violation_details ?? "Blocked via violation: {$blockedClient->violation_type}",
                        $blockedClient->user_id
                    );
                }
            }
        });

        // When is_active changes to true, block the contact
        static::updated(function (BlockedClient $blockedClient) {
            if ($blockedClient->contact_id) {
                $contact = Contact::find($blockedClient->contact_id);
                if ($contact) {
                    // Check if there are any active blocks for this contact
                    $hasActiveBlocks = self::where('contact_id', $blockedClient->contact_id)
                        ->where('id', '!=', $blockedClient->id)
                        ->active()
                        ->exists();

                    if ($blockedClient->is_active && !$hasActiveBlocks) {
                        // This is now the only active block, so block the contact
                        if (!$contact->is_blocked) {
                            $contact->block(
                                $blockedClient->violation_details ?? "Blocked via violation: {$blockedClient->violation_type}",
                                $blockedClient->user_id
                            );
                        }
                    } elseif (!$blockedClient->is_active) {
                        // This block was deactivated, check if we should unblock
                        $hasOtherActiveBlocks = self::where('contact_id', $blockedClient->contact_id)
                            ->where('id', '!=', $blockedClient->id)
                            ->active()
                            ->exists();

                        if (!$hasOtherActiveBlocks && $contact->is_blocked) {
                            // No other active blocks, unblock the contact
                            $contact->unblock();
                        }
                    }
                }
            }
        });
    }
}
