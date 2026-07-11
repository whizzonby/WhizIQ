<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClientPaymentAttempt extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'client_invoice_id',
        'invoice_client_id',
        'contact_id',
        'provider',
        'provider_attempt_id',
        'amount',
        'currency',
        'status',
        'failure_message',
        'failed_at',
        'recovery_reminder_sent_at',
        'recovered_at',
        'metadata',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'failed_at' => 'datetime',
        'recovery_reminder_sent_at' => 'datetime',
        'recovered_at' => 'datetime',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(ClientInvoice::class, 'client_invoice_id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(InvoiceClient::class, 'invoice_client_id');
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopeUnrecovered($query)
    {
        return $query->whereNull('recovered_at');
    }

    public function markRecoveryReminderSent(): void
    {
        $this->forceFill([
            'recovery_reminder_sent_at' => now(),
        ])->save();
    }

    public function markRecovered(): void
    {
        $this->forceFill([
            'status' => 'recovered',
            'recovered_at' => now(),
        ])->save();
    }
}
