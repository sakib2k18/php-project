<?php

namespace Database\Seeders;

use App\Models\GalleryItem;
use Illuminate\Database\Seeder;

class GallerySeeder extends Seeder
{
    public function run(): void
    {
        $images = $this->images();

        foreach ($this->items() as $index => $data) {
            GalleryItem::updateOrCreate(
                ['title' => $data['title']],
                $data + ['image' => $images[$index] ?? null, 'sort_order' => $index + 1, 'is_published' => true]
            );
        }
    }

    /**
     * Photographs bundled with the site (public/images/gallery), assigned to
     * the gallery items in order. Items beyond the photograph count keep the
     * designed placeholder.
     *
     * @return array<int, string>
     */
    protected function images(): array
    {
        return [
            '/images/gallery/gallery-01.jpg',
            '/images/gallery/gallery-02.jpg',
            '/images/gallery/gallery-03.jpg',
            '/images/gallery/gallery-04.jpg',
            '/images/gallery/gallery-05.jpg',
            '/images/gallery/gallery-06.jpg',
            '/images/gallery/gallery-07.jpg',
            '/images/gallery/gallery-08.jpg',
            '/images/gallery/gallery-09.jpg',
            '/images/gallery/gallery-10.jpg',
            '/images/gallery/gallery-11.jpg',
            '/images/gallery/gallery-12.jpg',
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function items(): array
    {
        return [
            ['title' => 'Family kits reaching Maheshwaripur', 'caption' => 'The first convoy of the coastal response unloading dry food and tarpaulins.', 'category' => 'relief', 'taken_on' => now()->subDays(18)->toDateString()],
            ['title' => 'Loading the winter truck at midnight', 'caption' => 'Volunteers packing 420 blankets before the overnight run to Kurigram.', 'category' => 'relief', 'taken_on' => now()->subDays(11)->toDateString()],
            ['title' => 'Blanket distribution, Chilmari', 'caption' => 'Night distribution reaches the people daytime queues miss.', 'category' => 'relief', 'taken_on' => now()->subMonths(12)->toDateString()],
            ['title' => 'School bags handed over in Rupsha', 'caption' => 'Class four students receiving a year of exercise books and stationery.', 'category' => 'education', 'taken_on' => now()->subMonths(6)->toDateString()],
            ['title' => 'Evening coaching session', 'caption' => 'Board-exam candidates studying at the community centre we fund.', 'category' => 'education', 'taken_on' => now()->subMonths(5)->toDateString()],
            ['title' => 'Registration desk at Phultala', 'caption' => 'Tokens being handed out before the medical camp opens at 8 AM.', 'category' => 'medical', 'taken_on' => now()->subMonths(3)->toDateString()],
            ['title' => 'Blood pressure screening', 'caption' => 'For 38 patients this was their first ever screening.', 'category' => 'medical', 'taken_on' => now()->subMonths(3)->toDateString()],
            ['title' => 'Pharmacy counter at the camp', 'caption' => 'Every patient leaves with a one-month supply of prescribed medicine.', 'category' => 'medical', 'taken_on' => now()->subMonths(3)->toDateString()],
            ['title' => 'Blood donation drive, KUET Central Field', 'caption' => '186 units collected in a single day — our best result so far.', 'category' => 'events', 'taken_on' => now()->subDays(28)->toDateString()],
            ['title' => 'Annual fundraising iftar', 'caption' => 'Donors, alumni and volunteers together at the KUET Auditorium.', 'category' => 'events', 'taken_on' => now()->subMonths(10)->toDateString()],
            ['title' => 'Volunteer orientation, spring intake', 'caption' => 'New volunteers learning the beneficiary verification process.', 'category' => 'events', 'taken_on' => now()->subMonths(7)->toDateString()],
            ['title' => 'Rainwater tank installation, Gabura', 'caption' => 'One of 38 household tanks installed in the salinity-affected belt.', 'category' => 'community', 'taken_on' => now()->subMonths(9)->toDateString()],
            ['title' => 'Tree planting along the embankment', 'caption' => 'Students from two local schools planting salt-tolerant saplings.', 'category' => 'community', 'taken_on' => now()->subMonths(4)->toDateString()],
            ['title' => 'Monthly food parcel packing', 'caption' => 'Two hundred parcels assembled on the first Friday of the month.', 'category' => 'community', 'taken_on' => now()->subMonths(1)->toDateString()],
            ['title' => 'The executive committee', 'caption' => 'Planning meeting ahead of the winter season.', 'category' => 'team', 'taken_on' => now()->subMonths(2)->toDateString()],
            ['title' => 'Field operations team, Koyra', 'caption' => 'The response team on the embankment road after the second delivery run.', 'category' => 'team', 'taken_on' => now()->subDays(16)->toDateString()],
        ];
    }
}
