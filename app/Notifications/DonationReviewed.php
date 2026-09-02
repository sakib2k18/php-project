<?php

namespace App\Notifications;

use App\Models\Donation;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * Stored in the `notifications` table and shown in the supporter dashboard when
 * the administrator approves or rejects a donation record.
 */
class DonationReviewed extends Notification
{
    use Queueable;

    public function __construct(public Donation $donation) {}

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
        $approved = $this->donation->status === Donation::STATUS_APPROVED;

        return [
            'type' => 'donation',
            'tone' => $approved ? 'success' : 'warning',
            'icon' => $approved ? 'check-badge' : 'exclamation',
            'title' => $approved ? 'Donation approved' : 'Donation not approved',
            'message' => $approved
                ? "Thank you! Your donation {$this->donation->reference} of ".money($this->donation->amount).' has been verified.'
                : "Your donation {$this->donation->reference} could not be verified. Please check the details or contact us.",
            'reference' => $this->donation->reference,
            'donation_id' => $this->donation->id,
            'url' => route('donations.show', $this->donation),
        ];
    }
}
