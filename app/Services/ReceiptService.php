<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\ClientInvoice;
use App\Models\ClientPayment;
use App\Models\Receipt;
use App\Notifications\ReceiptNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class ReceiptService
{
    /**
     * Create a receipt when a payment is recorded on an invoice.
     * Triggered from ClientInvoice::recordPayment().
     */
    public static function createFromPayment(ClientInvoice $invoice, ClientPayment $payment): ?Receipt
    {
        try {
            $invoice->loadMissing(['client', 'items', 'user']);

            $items = $invoice->items->map(fn ($item) => [
                'description' => $item->description,
                'quantity'    => (float) $item->quantity,
                'unit_price'  => (float) $item->unit_price,
                'amount'      => (float) $item->amount,
            ])->toArray();

            $receipt = Receipt::create([
                'user_id'               => $invoice->user_id,
                'receipt_number'        => Receipt::generateReceiptNumber($invoice->user_id),
                'receipt_date'          => now()->toDateString(),
                'invoice_client_id'     => $invoice->invoice_client_id,
                'customer_name'         => $invoice->client?->name,
                'customer_email'        => $invoice->client?->email,
                'customer_phone'        => $invoice->client?->phone,
                'payment_method'        => $payment->payment_method,
                'transaction_reference' => $payment->transaction_id,
                'currency'              => $invoice->currency ?? 'USD',
                'subtotal'              => $invoice->subtotal,
                'tax_rate'              => $invoice->tax_rate,
                'tax_amount'            => $invoice->tax_amount,
                'discount_amount'       => $invoice->discount_amount,
                'total_amount'          => $payment->amount,
                'amount_tendered'       => $payment->amount,
                'change_due'            => 0,
                'items'                 => $items,
                'linked_invoice_id'     => $invoice->id,
                'notes'                 => 'Payment received for invoice ' . $invoice->invoice_number,
            ]);

            if ($receipt->customer_email) {
                Notification::route('mail', $receipt->customer_email)
                    ->notify(new ReceiptNotification($receipt));
            }

            Log::info('Receipt created from invoice payment', [
                'receipt_id'     => $receipt->id,
                'receipt_number' => $receipt->receipt_number,
                'invoice_id'     => $invoice->id,
                'payment_id'     => $payment->id,
            ]);

            return $receipt;

        } catch (\Exception $e) {
            Log::error('Failed to create receipt from payment', [
                'invoice_id' => $invoice->id,
                'payment_id' => $payment->id,
                'error'      => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Create a receipt when an upfront or deposit booking payment is collected.
     * Triggered from ProcessNewAppointment for payment_type = upfront|deposit.
     */
    public static function createFromAppointment(Appointment $appointment): ?Receipt
    {
        try {
            $appointment->loadMissing(['appointmentType', 'user']);
            $appointmentType = $appointment->appointmentType;

            if (! $appointmentType || $appointmentType->price <= 0) {
                return null;
            }

            // For deposit payments, receipt covers only the deposit amount
            $isDeposit    = $appointmentType->payment_type === 'deposit';
            $paidAmount   = $isDeposit
                ? (float) ($appointmentType->deposit_amount ?? $appointmentType->price)
                : (float) $appointmentType->price;

            $description  = $isDeposit
                ? $appointmentType->name . ' (Deposit)'
                : $appointmentType->name;

            $currency = \App\Models\BookingSetting::where('user_id', $appointment->user_id)
                ->value('currency') ?? 'USD';

            $receipt = Receipt::create([
                'user_id'               => $appointment->user_id,
                'receipt_number'        => Receipt::generateReceiptNumber($appointment->user_id),
                'receipt_date'          => now()->toDateString(),
                'customer_name'         => $appointment->attendee_name,
                'customer_email'        => $appointment->attendee_email,
                'customer_phone'        => $appointment->attendee_phone,
                'payment_method'        => 'stripe',
                'currency'              => $currency,
                'subtotal'              => $paidAmount,
                'tax_rate'              => 0,
                'tax_amount'            => 0,
                'discount_amount'       => 0,
                'total_amount'          => $paidAmount,
                'amount_tendered'       => $paidAmount,
                'change_due'            => 0,
                'items'                 => [[
                    'description' => $description,
                    'quantity'    => 1,
                    'unit_price'  => $paidAmount,
                    'amount'      => $paidAmount,
                ]],
                'notes' => 'Appointment on ' . $appointment->start_datetime->format('D, d M Y \a\t g:i A T'),
            ]);

            if ($receipt->customer_email) {
                Notification::route('mail', $receipt->customer_email)
                    ->notify(new ReceiptNotification($receipt));
            }

            Log::info('Receipt created from appointment booking', [
                'receipt_id'     => $receipt->id,
                'receipt_number' => $receipt->receipt_number,
                'appointment_id' => $appointment->id,
                'payment_type'   => $appointmentType->payment_type,
            ]);

            return $receipt;

        } catch (\Exception $e) {
            Log::error('Failed to create receipt from appointment', [
                'appointment_id' => $appointment->id,
                'error'          => $e->getMessage(),
            ]);

            return null;
        }
    }
}
