<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WeeklyMarketingDigestNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public array $digest
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject('Your weekly WhizIQ marketing digest')
            ->greeting('Hello!')
            ->line('Here is what WhizIQ found in your marketing activity this week.');

        if ($this->digest['recommended']) {
            $mail->line('Top recommendation: ' . $this->digest['recommended']['name'] . ' - ' . $this->digest['recommended']['reason']);
        }

        if ($this->digest['draft_count'] > 0) {
            $mail->line($this->digest['draft_count'] . ' campaign draft(s) are ready for review.');
        }

        $mail->line('Recent impact: ' . $this->digest['bookings'] . ' booking(s), ' . $this->digest['formatted_projected'] . ' projected, ' . $this->digest['formatted_collected'] . ' collected.');

        foreach (array_slice($this->digest['strongest_segments'], 0, 2) as $segment) {
            $mail->line('Strong segment: ' . $segment['audience'] . ' - ' . $segment['label'] . '.');
        }

        foreach (array_slice($this->digest['weakest_segments'], 0, 2) as $segment) {
            $mail->line('Needs attention: ' . $segment['audience'] . ' - ' . $segment['label'] . '.');
        }

        if ($this->digest['actions']['review_draft_url'] ?? null) {
            $mail->line('Action: a ready draft is available to review.');

            return $mail
                ->action('Review Ready Draft', $this->digest['actions']['review_draft_url'])
                ->line('WhizIQ will keep watching for the next best campaign to review.');
        }

        if ($this->digest['actions']['improve_weakest_segment_url'] ?? null) {
            $mail->line('Action: improve the weakest segment draft before sending.');

            return $mail
                ->action('Improve Weakest Segment', $this->digest['actions']['improve_weakest_segment_url'])
                ->line('WhizIQ will keep watching for the next best campaign to review.');
        }

        if ($this->digest['actions']['snooze_recommendation_url'] ?? null) {
            $mail->line('Action: snooze this recommendation if it is not useful this week.');

            return $mail
                ->action('Snooze Recommendation', $this->digest['actions']['snooze_recommendation_url'])
                ->line('WhizIQ will keep watching for the next best campaign to review.');
        }

        return $mail
            ->action('Open Campaign Reports', $this->digest['report_url'])
            ->line('WhizIQ will keep watching for the next best campaign to review.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Weekly marketing digest',
            'message' => $this->digest['summary'],
            'recommended_audience_id' => $this->digest['recommended']['id'] ?? null,
            'draft_count' => $this->digest['draft_count'],
            'bookings' => $this->digest['bookings'],
            'projected' => $this->digest['projected'],
            'collected' => $this->digest['collected'],
            'url' => $this->digest['report_url'],
            'actions' => $this->digest['actions'],
        ];
    }
}
