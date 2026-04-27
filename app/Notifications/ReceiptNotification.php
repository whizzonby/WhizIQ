<?php

namespace App\Notifications;

use App\Models\Receipt;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReceiptNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Receipt $receipt) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $this->receipt->loadMissing('user');

        $businessName = $this->receipt->user?->public_name
            ?? $this->receipt->user?->name
            ?? config('app.name');

        return (new MailMessage)
            ->subject('Receipt ' . $this->receipt->receipt_number . ' from ' . $businessName)
            ->view('emails.receipt', [
                'receipt'      => $this->receipt,
                'businessName' => $businessName,
            ]);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'receipt_id'     => $this->receipt->id,
            'receipt_number' => $this->receipt->receipt_number,
        ];
    }
}
