<?php

namespace Database\Seeders;

use App\Models\Event;
use Illuminate\Database\Seeder;

class EventSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->events() as $data) {
            Event::updateOrCreate(['slug' => $data['slug']], $data);
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function events(): array
    {
        $p = fn (array $paragraphs) => collect($paragraphs)->map(fn ($t) => "<p>{$t}</p>")->implode("\n");

        return [
            [
                'title' => 'Volunteer Orientation & Field Safety Briefing',
                'slug' => 'volunteer-orientation-and-field-safety-briefing',
                'description' => $p([
                    'A half-day session for everyone joining the volunteer team this term. We cover how KUET TRY selects beneficiaries, how distributions are documented, and the safety rules that apply on every field trip.',
                    'The second half is practical: basic first aid, how to work safely around water during flood response, and how to handle photography and consent respectfully.',
                    'Bring your student ID. Lunch and refreshments are provided.',
                ]),
                'event_date' => now()->addDays(9)->toDateString(),
                'start_time' => '09:30',
                'end_time' => '14:00',
                'location' => 'Seminar Room, Civil Engineering Building, KUET',
                'latitude' => 22.899310,
                'longitude' => 89.502289,
                'organizer' => 'KUET TRY Volunteer Desk',
                'status' => Event::STATUS_PUBLISHED,
                'capacity' => 120,
            ],
            [
                'title' => 'Free Medical Camp — Phultala',
                'slug' => 'free-medical-camp-phultala',
                'description' => $p([
                    'A one-day camp offering free consultation, blood pressure and blood sugar screening, eye checks and a one-month supply of prescribed medicine.',
                    'Eight volunteer physicians and twenty final-year medical students will be attending. We expect to see around 450 patients.',
                    'No registration is needed — come early, tokens are handed out from 8:00 AM.',
                ]),
                'event_date' => now()->addDays(21)->toDateString(),
                'start_time' => '08:00',
                'end_time' => '17:00',
                'location' => 'Phultala Union Parishad Ground, Khulna',
                'latitude' => 22.941700,
                'longitude' => 89.438900,
                'organizer' => 'KUET TRY Medical Wing',
                'status' => Event::STATUS_PUBLISHED,
                'capacity' => 500,
            ],
            [
                'title' => 'Winter Blanket Packing Day',
                'slug' => 'winter-blanket-packing-day',
                'description' => $p([
                    'Volunteers are needed to sort, count and pack blankets and sweaters ahead of the northern distribution run. This is indoor work and suitable for anyone.',
                    'We need around 40 people across two shifts. Sign up at the volunteer desk or just turn up — there is always more to do than hands to do it.',
                ]),
                'event_date' => now()->addDays(34)->toDateString(),
                'start_time' => '10:00',
                'end_time' => '18:00',
                'location' => 'KUET TRY Store Room, Central Field, KUET',
                'latitude' => 22.899310,
                'longitude' => 89.502289,
                'organizer' => 'KUET TRY Logistics Team',
                'status' => Event::STATUS_PUBLISHED,
                'capacity' => 60,
            ],
            [
                'title' => 'Annual Fundraising Iftar',
                'slug' => 'annual-fundraising-iftar',
                'description' => $p([
                    'Our largest fundraising evening of the year, bringing together donors, alumni, volunteers and partner organisations.',
                    'The programme includes a short report on the year\'s work, testimony from two families we supported, and a pledge round for the coming year.',
                ]),
                'event_date' => now()->addMonths(2)->toDateString(),
                'start_time' => '17:00',
                'end_time' => '20:30',
                'location' => 'KUET Auditorium, Fulbarigate, Khulna',
                'latitude' => 22.899310,
                'longitude' => 89.502289,
                'organizer' => 'KUET TRY Executive Committee',
                'status' => Event::STATUS_PUBLISHED,
                'capacity' => 400,
            ],
            [
                'title' => 'Blood Donation Drive',
                'slug' => 'blood-donation-drive',
                'description' => $p([
                    'Held with the Khulna Medical College blood bank, this drive collected 186 units of blood over a single day — our best result so far.',
                    'Donors received a free haemoglobin and blood group test, and 62 people registered as standby donors for emergency calls.',
                ]),
                'event_date' => now()->subDays(28)->toDateString(),
                'start_time' => '09:00',
                'end_time' => '16:00',
                'location' => 'KUET Central Field',
                'latitude' => 22.899310,
                'longitude' => 89.502289,
                'organizer' => 'KUET TRY Medical Wing',
                'status' => Event::STATUS_PUBLISHED,
                'capacity' => 250,
            ],
            [
                'title' => 'Eid Package Distribution Day',
                'slug' => 'eid-package-distribution-day',
                'description' => $p([
                    '450 festival food packages were packed and delivered to homes across Khulna and Jashore by 70 volunteers working in twelve teams.',
                    'Deliveries were made to homes rather than from a central point, which kept the process dignified and avoided crowding.',
                ]),
                'event_date' => now()->subMonths(8)->toDateString(),
                'start_time' => '07:00',
                'end_time' => '19:00',
                'location' => 'Khulna & Jashore districts',
                'latitude' => 23.166700,
                'longitude' => 89.208900,
                'organizer' => 'KUET TRY Field Operations',
                'status' => Event::STATUS_PUBLISHED,
                'capacity' => 80,
            ],
            [
                'title' => 'Disaster Preparedness Workshop',
                'slug' => 'disaster-preparedness-workshop',
                'description' => $p([
                    'A community workshop on cyclone preparedness delivered in three coastal unions: early warning signals, evacuation routes, and how to protect drinking water sources before a surge.',
                ]),
                'event_date' => now()->subMonths(3)->toDateString(),
                'start_time' => '10:00',
                'end_time' => '15:00',
                'location' => 'Koyra Upazila Complex, Khulna',
                'latitude' => 22.339000,
                'longitude' => 89.291000,
                'organizer' => 'KUET TRY Field Operations',
                'status' => Event::STATUS_PUBLISHED,
                'capacity' => 200,
            ],
            [
                'title' => 'Alumni Networking Evening',
                'slug' => 'alumni-networking-evening',
                'description' => $p([
                    'A planning event for alumni interested in sponsoring a campaign or mentoring the volunteer team. Details are being finalised.',
                ]),
                'event_date' => now()->addMonths(3)->toDateString(),
                'start_time' => '18:00',
                'end_time' => '21:00',
                'location' => 'To be confirmed, Khulna',
                'latitude' => null,
                'longitude' => null,
                'organizer' => 'KUET TRY Executive Committee',
                'status' => Event::STATUS_DRAFT,
                'capacity' => 150,
            ],
        ];
    }
}
