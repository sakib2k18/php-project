<?php

namespace App\Notifications;

use App\Models\Volunteer;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class VolunteerReviewed extends Notification
{
    use Queueable;

    public function __construct(public Volunteer $volunteer) {}

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
        $approved = $this->volunteer->status === Volunteer::STATUS_APPROVED;

        return [
            'type' => 'volunteer',
            'tone' => $approved ? 'success' : 'warning',
            'icon' => $approved ? 'users' : 'exclamation',
            'title' => $approved ? 'Welcome to the volunteer team' : 'Volunteer application update',
            'message' => $approved
                ? 'Your volunteer application has been approved. Our coordinator will contact you shortly.'
                : 'Your volunteer application was not approved this time. You are welcome to apply again.',
            'url' => route('volunteer.index'),
        ];
    }
}
