<?php

namespace App\Notifications;

use App\Models\EmailCampaign;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CampaignDraftRescuedNotification extends Notification implements ShouldQueue
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
            ->subject('WhizzIQ improved a campaign draft before it expired')
            ->greeting('Hello!')
            ->line("WhizzIQ noticed this campaign draft was close to expiring: {$this->campaign->name}.")
            ->line('The subject, body, and available segment variants were refreshed so it is easier to review and send.')
            ->action('Review rescued draft', $this->reviewUrl())
            ->line('Nothing has been sent to clients yet.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'email_campaign_id' => $this->campaign->id,
            'campaign_audience_id' => $this->campaign->campaign_audience_id,
            'title' => 'Campaign draft rescued',
            'message' => "{$this->campaign->name} was improved before it expired.",
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
