<?php

namespace App\Notifications;

use App\Models\Announcement;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AnnouncementPublished extends Notification
{
    use Queueable;

    public function __construct(public Announcement $announcement) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'announcement',
            'tone' => $this->announcement->is_urgent ? 'warning' : 'info',
            'icon' => 'megaphone',
            'title' => $this->announcement->title,
            'message' => str($this->announcement->content)->stripTags()->limit(140)->toString(),
            'url' => $this->announcement->link_url ?: route('home'),
        ];
    }
}
