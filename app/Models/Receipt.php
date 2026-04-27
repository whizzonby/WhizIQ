<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Receipt extends Model
{
    protected $fillable = [
        'user_id',
        'receipt_number',
        'receipt_date',
        'invoice_client_id',
        'customer_name',
        'customer_email',
        'customer_phone',
        'payment_method',
        'transaction_reference',
        'amount_tendered',
        'change_due',
        'currency',
        'subtotal',
        'tax_rate',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'items',
        'linked_invoice_id',
        'template',
        'primary_color',
        'notes',
    ];

    protected $casts = [
        'receipt_date'     => 'date',
        'items'            => 'array',
        'subtotal'         => 'decimal:2',
        'tax_rate'         => 'decimal:2',
        'tax_amount'       => 'decimal:2',
        'discount_amount'  => 'decimal:2',
        'total_amount'     => 'decimal:2',
        'amount_tendered'  => 'decimal:2',
        'change_due'       => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(InvoiceClient::class, 'invoice_client_id');
    }

    public function linkedInvoice(): BelongsTo
    {
        return $this->belongsTo(ClientInvoice::class, 'linked_invoice_id');
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public static function generateReceiptNumber(int $userId): string
    {
        $last = static::where('user_id', $userId)
            ->orderByDesc('id')
            ->value('receipt_number');

        if ($last && preg_match('/REC-(\d+)$/', $last, $m)) {
            return 'REC-' . str_pad((int) $m[1] + 1, 5, '0', STR_PAD_LEFT);
        }

        return 'REC-00001';
    }

    public function getPaymentMethodLabelAttribute(): string
    {
        return match ($this->payment_method) {
            'cash'          => 'Cash',
            'credit_card'   => 'Credit Card',
            'debit_card'    => 'Debit Card',
            'bank_transfer' => 'Bank Transfer',
            'check'         => 'Check',
            'paypal'        => 'PayPal',
            'stripe'        => 'Stripe',
            'other'         => 'Other',
            default         => ucfirst(str_replace('_', ' ', $this->payment_method)),
        };
    }
}
