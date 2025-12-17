<?php

namespace App\Livewire\Announcement;

use App\Constants\AnnouncementPlacement;
use App\Constants\SessionConstants;
use App\Services\AnnouncementService;
use Livewire\Component;

class View extends Component
{
    public ?string $placement;

    public function mount(?string $placement = null)
    {
        $this->placement = $placement;
    }

    public function dismiss($id)
    {
        if (session()->exists(SessionConstants::DISMISSED_ANNOUNCEMENTS)) {
            $dismissedAnnouncements = session()->get(SessionConstants::DISMISSED_ANNOUNCEMENTS);
        } else {
            $dismissedAnnouncements = [];
        }

        if (! in_array($id, $dismissedAnnouncements)) {
            $dismissedAnnouncements[] = $id;
        }

        session()->put(SessionConstants::DISMISSED_ANNOUNCEMENTS, $dismissedAnnouncements);

        $this->skipRender();
    }

    public function render(AnnouncementService $announcementService)
    {
        $placement = AnnouncementPlacement::tryFrom($this->placement) ?? AnnouncementPlacement::FRONTEND;
        
        // Use Bootstrap view for home page, Tailwind view for other pages
        $view = request()->routeIs('home') 
            ? 'livewire.announcement.bootstrap-view' 
            : 'livewire.announcement.view';

        return view(
            $view, [
                'announcement' => $announcementService->getAnnouncement($placement),
            ]
        );
    }
}
