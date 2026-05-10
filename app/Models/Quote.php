<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Quote extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'invoice_client_id',
        'quote_number',
        'status',
        'quote_date',
        'valid_until',
        'currency',
        'subtotal',
        'tax_rate',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'notes',
        'terms',
        'footer',
        'template',
        'primary_color',
        'accent_color',
        'pdf_path',
    ];

    protected $casts = [
        'quote_date'      => 'date',
        'valid_until'     => 'date',
        'subtotal'        => 'decimal:2',
        'tax_rate'        => 'decimal:2',
        'tax_amount'      => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount'    => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(InvoiceClient::class, 'invoice_client_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(QuoteItem::class)->orderBy('sort_order');
    }

    public static function generateQuoteNumber(): string
    {
        $year   = now()->year;
        $userId = auth()->id();

        $last = static::where('user_id', $userId)
            ->whereYear('created_at', $year)
            ->orderByDesc('id')
            ->value('quote_number');

        if ($last && preg_match('/QUO-' . $year . '-(\d+)$/', $last, $m)) {
            $next = intval($m[1]) + 1;
        } else {
            $next = 1;
        }

        return 'QUO-' . $year . '-' . str_pad($next, 5, '0', STR_PAD_LEFT);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'draft'    => 'Draft',
            'sent'     => 'Sent',
            'accepted' => 'Accepted',
            'rejected' => 'Rejected',
            'expired'  => 'Expired',
            default    => ucfirst($this->status),
        };
    }

    public function isExpired(): bool
    {
        return $this->valid_until && $this->valid_until->isPast() && $this->status === 'sent';
    }
}
