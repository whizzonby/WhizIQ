<?php

namespace App\Jobs;

use App\Models\Appointment;
use App\Models\BookingSetting;
use App\Models\ClientInvoice;
use App\Models\ClientInvoiceItem;
use App\Models\InvoiceClient;
use App\Models\User;
use App\Notifications\AppointmentConfirmedNotification;
use App\Notifications\NewAppointmentBookedNotification;
use App\Services\InvoicePDFService;
use App\Services\MeetingPlatform\MeetingPlatformService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class ProcessNewAppointment implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Appointment $appointment,
        public BookingSetting $bookingSetting
    ) {}

    public function handle(): void
    {
        try {
            $meetingService = new MeetingPlatformService();
            $this->appointment = $meetingService->createMeetingForAppointment(
                $this->appointment,
                $this->bookingSetting
            );

            $appointmentType = $this->appointment->appointmentType;
            $paymentType = $appointmentType?->payment_type ?? 'none';
            $appointmentPrice = (float) ($appointmentType?->price ?? 0);

            if ($appointmentPrice > 0 && $paymentType === 'invoice') {
                $this->createInvoiceForAppointment();
            }

            if ($this->appointment->attendee_email) {
                Notification::route('mail', $this->appointment->attendee_email)
                    ->notify(new AppointmentConfirmedNotification($this->appointment));
            }

            $owner = User::find($this->bookingSetting->user_id);
            if ($owner) {
                $owner->notify(new NewAppointmentBookedNotification($this->appointment));
            }

            Log::info('Appointment processed successfully', [
                'appointment_id' => $this->appointment->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to process appointment', [
                'appointment_id' => $this->appointment->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    protected function createInvoiceForAppointment(): void
    {
        try {
            if (! $this->appointment->relationLoaded('appointmentType')) {
                $this->appointment->load('appointmentType');
            }

            $appointmentType = $this->appointment->appointmentType;
            if (! $appointmentType) {
                Log::warning('Cannot create invoice because appointment type was not found', [
                    'appointment_id' => $this->appointment->id,
                ]);

                return;
            }

            $invoiceClientLookup = $this->appointment->contact_id
                ? [
                    'user_id' => $this->appointment->user_id,
                    'contact_id' => $this->appointment->contact_id,
                ]
                : [
                    'user_id' => $this->appointment->user_id,
                    'email' => $this->appointment->attendee_email,
                ];

            $invoiceClient = InvoiceClient::firstOrCreate(
                $invoiceClientLookup,
                [
                    'name' => $this->appointment->attendee_name,
                    'email' => $this->appointment->attendee_email,
                    'phone' => $this->appointment->attendee_phone,
                    'company' => $this->appointment->attendee_company,
                    'is_active' => true,
                ]
            );

            $lastInvoice = ClientInvoice::where('user_id', $this->appointment->user_id)
                ->orderBy('id', 'desc')
                ->first();

            $invoiceNumber = $lastInvoice
                ? 'INV-' . str_pad((int) substr($lastInvoice->invoice_number, 4) + 1, 5, '0', STR_PAD_LEFT)
                : 'INV-00001';

            $invoice = ClientInvoice::create([
                'user_id' => $this->appointment->user_id,
                'invoice_client_id' => $invoiceClient->id,
                'invoice_number' => $invoiceNumber,
                'status' => 'sent',
                'invoice_date' => now(),
                'due_date' => now()->addDays(7),
                'subtotal' => $appointmentType->price,
                'tax_rate' => 0,
                'tax_amount' => 0,
                'discount_amount' => 0,
                'total_amount' => $appointmentType->price,
                'amount_paid' => 0,
                'balance_due' => $appointmentType->price,
                'currency' => BookingSetting::where('user_id', $this->appointment->user_id)->value('currency') ?? 'USD',
                'notes' => "Invoice for appointment: {$appointmentType->name}",
            ]);

            ClientInvoiceItem::create([
                'client_invoice_id' => $invoice->id,
                'description' => $appointmentType->name,
                'quantity' => 1,
                'unit_price' => $appointmentType->price,
                'amount' => $appointmentType->price,
                'sort_order' => 0,
            ]);

            Log::info('Invoice created for appointment', [
                'appointment_id' => $this->appointment->id,
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoiceNumber,
            ]);

            $this->appointment->forceFill([
                'client_invoice_id' => $invoice->id,
            ])->save();

            try {
                $invoice->loadMissing('client');
                app(InvoicePDFService::class)->emailToClient($invoice);
            } catch (\Exception $emailException) {
                Log::warning('Failed to email invoice to client after appointment booking', [
                    'invoice_id' => $invoice->id,
                    'appointment_id' => $this->appointment->id,
                    'error' => $emailException->getMessage(),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to create invoice for appointment', [
                'appointment_id' => $this->appointment->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}
