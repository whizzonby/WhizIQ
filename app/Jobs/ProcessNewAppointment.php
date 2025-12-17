<?php

namespace App\Jobs;

use App\Models\Appointment;
use App\Models\BookingSetting;
use App\Models\User;
use App\Services\MeetingPlatform\MeetingPlatformService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Notification;
use App\Notifications\AppointmentConfirmedNotification;
use App\Notifications\NewAppointmentBookedNotification;
use Illuminate\Support\Facades\Log;

class ProcessNewAppointment implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Appointment $appointment,
        public BookingSetting $bookingSetting
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Create meeting link if platform is configured (slow operation)
            $meetingService = new MeetingPlatformService();
            $this->appointment = $meetingService->createMeetingForAppointment(
                $this->appointment,
                $this->bookingSetting
            );

            // Create invoice if payment type is invoice
            if ($this->appointment->payment_required &&
                $this->appointment->appointmentType &&
                $this->appointment->appointmentType->payment_type === 'invoice') {
                $this->createInvoiceForAppointment();
            }

            // Send confirmation email to attendee (slow operation)
            if ($this->appointment->attendee_email) {
                Notification::route('mail', $this->appointment->attendee_email)
                    ->notify(new AppointmentConfirmedNotification($this->appointment));
            }

            // Send notification to appointment owner (slow operation)
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

            // Don't fail the job - appointment is already created
            // Just log the error so admin can follow up
        }
    }

    /**
     * Create invoice for appointments with invoice payment type
     */
    protected function createInvoiceForAppointment(): void
    {
        try {
            // Load appointment type if not loaded
            if (!$this->appointment->relationLoaded('appointmentType')) {
                $this->appointment->load('appointmentType');
            }

            $appointmentType = $this->appointment->appointmentType;
            if (!$appointmentType) {
                Log::warning('Cannot create invoice - appointment type not found', [
                    'appointment_id' => $this->appointment->id,
                ]);
                return;
            }

            // Get or create invoice client from appointment attendee
            $invoiceClient = \App\Models\InvoiceClient::firstOrCreate(
                [
                    'user_id' => $this->appointment->user_id,
                    'email' => $this->appointment->attendee_email,
                ],
                [
                    'name' => $this->appointment->attendee_name,
                    'company_name' => $this->appointment->attendee_company,
                    'phone' => $this->appointment->attendee_phone,
                ]
            );

            // Generate unique invoice number
            $lastInvoice = \App\Models\ClientInvoice::where('user_id', $this->appointment->user_id)
                ->orderBy('id', 'desc')
                ->first();

            $invoiceNumber = $lastInvoice
                ? 'INV-' . str_pad((int)substr($lastInvoice->invoice_number, 4) + 1, 5, '0', STR_PAD_LEFT)
                : 'INV-00001';

            // Create the invoice
            $invoice = \App\Models\ClientInvoice::create([
                'user_id' => $this->appointment->user_id,
                'invoice_client_id' => $invoiceClient->id,
                'invoice_number' => $invoiceNumber,
                'status' => 'sent',
                'invoice_date' => now(),
                'due_date' => now()->addDays(7), // 7 days payment term
                'subtotal' => $appointmentType->price,
                'tax_rate' => 0,
                'tax_amount' => 0,
                'discount_amount' => 0,
                'total_amount' => $appointmentType->price,
                'amount_paid' => 0,
                'balance_due' => $appointmentType->price,
                'currency' => 'USD',
                'notes' => 'Invoice for appointment: ' . $appointmentType->name,
            ]);

            // Create invoice item
            \App\Models\ClientInvoiceItem::create([
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

            // TODO: Send invoice email to client
            // This would be implemented with an InvoiceCreatedNotification

        } catch (\Exception $e) {
            Log::error('Failed to create invoice for appointment', [
                'appointment_id' => $this->appointment->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}
