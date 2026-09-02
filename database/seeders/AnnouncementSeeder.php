<?php

namespace Database\Seeders;

use App\Models\Announcement;
use Illuminate\Database\Seeder;

class AnnouncementSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->announcements() as $data) {
            Announcement::updateOrCreate(['title' => $data['title']], $data);
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function announcements(): array
    {
        return [
            [
                'title' => 'Emergency appeal: coastal cyclone response is 40% funded',
                'content' => 'Our teams are in Koyra and Dacope now. Phase two — seed packages so families do not lose the coming planting season — starts next week and is still short of its target. Every contribution is verified and published.',
                'priority' => 'urgent',
                'status' => Announcement::STATUS_PUBLISHED,
                'link_url' => '/campaigns/cyclone-response-fund-coastal-khulna',
                'link_label' => 'Support the appeal',
                'published_at' => now()->subDays(5),
                'expires_at' => now()->addDays(30),
            ],
            [
                'title' => 'Volunteer orientation: registration is open',
                'content' => 'The half-day orientation and field safety briefing for new volunteers takes place this month. Anyone joining a distribution team must attend once.',
                'priority' => 'high',
                'status' => Announcement::STATUS_PUBLISHED,
                'link_url' => '/events/volunteer-orientation-and-field-safety-briefing',
                'link_label' => 'See the details',
                'published_at' => now()->subDays(12),
                'expires_at' => now()->addDays(9),
            ],
            [
                'title' => 'Donation verification now takes up to two working days',
                'content' => 'Because of the volume of the emergency appeal, matching donation records against our bank and mobile banking statements is taking a little longer than usual. Your record will stay "pending" until it is verified — nothing is lost.',
                'priority' => 'normal',
                'status' => Announcement::STATUS_PUBLISHED,
                'link_url' => null,
                'link_label' => null,
                'published_at' => now()->subDays(8),
                'expires_at' => now()->addDays(20),
            ],
            [
                'title' => 'Office closed for the public holiday',
                'content' => 'Our office at KUET will be closed for the public holiday. Field operations continue as normal and the emergency hotline stays open.',
                'priority' => 'low',
                'status' => Announcement::STATUS_PUBLISHED,
                'link_url' => null,
                'link_label' => null,
                'published_at' => now()->subDays(30),
                'expires_at' => now()->subDays(20),
            ],
            [
                'title' => 'Annual report publication date',
                'content' => 'The audited annual report will be published once the accounts review is complete. A date will be announced here.',
                'priority' => 'normal',
                'status' => Announcement::STATUS_DRAFT,
                'link_url' => null,
                'link_label' => null,
                'published_at' => null,
                'expires_at' => null,
            ],
        ];
    }
}
