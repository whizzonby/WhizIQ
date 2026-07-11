<?php

namespace App\Notifications;

use App\Models\EmailCampaign;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CampaignDraftPreparedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public EmailCampaign $campaign
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('WhizIQ prepared a campaign draft for you')
            ->greeting('Hello!')
            ->line("WhizIQ prepared a campaign draft: {$this->campaign->name}.")
            ->line('It is ready for you to review, edit, send, or schedule.')
            ->action('Review draft', $this->reviewUrl())
            ->line('Nothing has been sent to clients yet.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'email_campaign_id' => $this->campaign->id,
            'campaign_audience_id' => $this->campaign->campaign_audience_id,
            'title' => 'Campaign draft ready',
            'message' => "{$this->campaign->name} is ready to review.",
            'url' => $this->reviewUrl(),
        ];
    }

    protected function reviewUrl(): string
    {
        return route('filament.dashboard.pages.email-composer-page', [
            'draft' => $this->campaign->id,
        ]);
    }
}
