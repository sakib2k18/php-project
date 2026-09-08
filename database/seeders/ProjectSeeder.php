<?php

namespace Database\Seeders;

use App\Models\Project;
use Illuminate\Database\Seeder;

class ProjectSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->projects() as $data) {
            Project::updateOrCreate(
                ['slug' => $data['slug']],
                $data + ['image' => $this->images()[$data['slug']] ?? null]
            );
        }
    }

    /**
     * Cover photographs bundled with the site (public/images/projects),
     * keyed by project slug.
     *
     * @return array<string, string>
     */
    protected function images(): array
    {
        return [
            'winter-clothes-distribution-2025' => '/images/projects/project-01.jpg',
            'flood-relief-programme-haor-basin' => '/images/projects/project-02.jpg',
            'educational-support-programme' => '/images/projects/project-03.jpg',
            'monthly-food-distribution' => '/images/projects/project-04.jpg',
            'free-medical-camp-series' => '/images/projects/project-05.jpg',
            'safe-drinking-water-initiative' => '/images/projects/project-06.jpg',
            'eid-food-package-distribution' => '/images/projects/project-07.jpg',
            'tree-plantation-and-embankment-greening' => '/images/projects/project-08.jpg',
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function projects(): array
    {
        $p = fn (array $paragraphs) => collect($paragraphs)->map(fn ($t) => "<p>{$t}</p>")->implode("\n");

        return [
            [
                'title' => 'Winter Clothes Distribution 2025',
                'slug' => 'winter-clothes-distribution-2025',
                'summary' => 'Blankets, sweaters and warm caps distributed across eight unions during the coldest fortnight of the year.',
                'description' => $p([
                    'Over eleven nights in January, 62 volunteers distributed 1,850 blankets and 900 sweaters across eight unions in Kurigram and Khulna districts.',
                    'Distribution happened after 9 PM, when the people who most need warm clothing — rickshaw pullers, night guards, homeless families — are actually on the streets. Union parishad members verified household lists in advance for the village distributions.',
                    'Total expenditure was Tk 1,142,000, of which 94% went directly to goods and transport.',
                ]),
                'category' => 'winter_support',
                'location' => 'Kurigram & Khulna',
                'latitude' => 25.805700,
                'longitude' => 89.636200,
                'start_date' => now()->subMonths(13)->toDateString(),
                'end_date' => now()->subMonths(12)->toDateString(),
                'status' => Project::STATUS_COMPLETED,
                'featured' => true,
                'is_published' => true,
                'beneficiaries_count' => 2750,
            ],
            [
                'title' => 'Flood Relief Programme — Haor Basin',
                'slug' => 'flood-relief-programme-haor-basin',
                'summary' => 'Dry food, drinking water and unconditional cash grants for families displaced by flash flooding.',
                'description' => $p([
                    'Our largest single response to date. Within six days of the water rising, teams delivered 620 dry-food packets by boat to families sheltering on embankments and in school buildings.',
                    'In the second phase, 180 households received an unconditional cash grant of Tk 3,000 each, which recipients used mostly for medicine, cooking fuel and repairing damaged homes.',
                    'The programme was delivered jointly with a partner student organisation in Sylhet, which handled local verification and logistics.',
                ]),
                'category' => 'natural_disaster',
                'location' => 'Sunamganj, Sylhet',
                'latitude' => 25.065600,
                'longitude' => 91.395000,
                'start_date' => now()->subMonths(16)->toDateString(),
                'end_date' => now()->subMonths(14)->toDateString(),
                'status' => Project::STATUS_COMPLETED,
                'featured' => true,
                'is_published' => true,
                'beneficiaries_count' => 3100,
            ],
            [
                'title' => 'Educational Support Programme',
                'slug' => 'educational-support-programme',
                'summary' => 'Year-round supply of books, stationery and coaching support for children at risk of dropping out.',
                'description' => $p([
                    'Running continuously since 2022, this programme supplies exercise books, stationery and school bags to children identified by head teachers as being at risk of dropping out for financial reasons.',
                    'In board-exam years we also fund evening coaching, which in this region is effectively a requirement rather than a luxury.',
                    'Attendance is reviewed each term. Of the 340 children supported so far, 91% were still enrolled twelve months later.',
                ]),
                'category' => 'education',
                'location' => 'Khulna & Bagerhat',
                'latitude' => 22.815600,
                'longitude' => 89.568700,
                'start_date' => now()->subYears(3)->toDateString(),
                'end_date' => null,
                'status' => Project::STATUS_ONGOING,
                'featured' => true,
                'is_published' => true,
                'beneficiaries_count' => 340,
            ],
            [
                'title' => 'Monthly Food Distribution',
                'slug' => 'monthly-food-distribution',
                'summary' => 'A four-week staple food parcel delivered every month to 200 vulnerable households.',
                'description' => $p([
                    'On the first Friday of every month, volunteers pack and deliver 200 food parcels to households headed by widows, elderly people and persons with disabilities.',
                    'Each parcel contains 25 kg rice, 4 kg lentils, 3 litres of oil, sugar, salt, spices and powdered milk — roughly four weeks of staples for a family of five.',
                    'The beneficiary list is re-verified every six months with ward councillors so that households whose situation improves make room for others.',
                ]),
                'category' => 'food_distribution',
                'location' => 'Khulna City Corporation',
                'latitude' => 22.845600,
                'longitude' => 89.540300,
                'start_date' => now()->subYears(2)->toDateString(),
                'end_date' => null,
                'status' => Project::STATUS_ONGOING,
                'featured' => false,
                'is_published' => true,
                'beneficiaries_count' => 1000,
            ],
            [
                'title' => 'Free Medical Camp Series',
                'slug' => 'free-medical-camp-series',
                'summary' => 'Quarterly one-day camps offering consultation, basic diagnostics and a month of free medicine.',
                'description' => $p([
                    'Each camp brings together volunteer physicians from Khulna Medical College and final-year students to see between 350 and 500 patients in a single day.',
                    'Services include general consultation, blood pressure and blood sugar screening, eye checks, and a one-month supply of prescribed medicine free of charge.',
                    'Patients needing surgery or specialist care are referred and, where the family cannot pay, considered for our Emergency Surgery Fund.',
                ]),
                'category' => 'medical',
                'location' => 'Rural Khulna',
                'latitude' => 22.719000,
                'longitude' => 89.482000,
                'start_date' => now()->subMonths(20)->toDateString(),
                'end_date' => null,
                'status' => Project::STATUS_ONGOING,
                'featured' => false,
                'is_published' => true,
                'beneficiaries_count' => 2400,
            ],
            [
                'title' => 'Safe Drinking Water Initiative',
                'slug' => 'safe-drinking-water-initiative',
                'summary' => 'Rainwater harvesting tanks and deep tube wells in salinity-affected coastal villages.',
                'description' => $p([
                    'Thirty-eight household rainwater harvesting tanks and two community deep tube wells were installed across Gabura and Padmapukur unions.',
                    'Each 2,000-litre tank carries a household through most of the dry season, cutting the daily four-kilometre walk for water that fell almost entirely on women and girls.',
                    'A follow-up visit six months after installation found all 38 tanks in working order.',
                ]),
                'category' => 'emergency_relief',
                'location' => 'Shyamnagar, Satkhira',
                'latitude' => 22.325600,
                'longitude' => 89.109700,
                'start_date' => now()->subMonths(11)->toDateString(),
                'end_date' => now()->subMonths(5)->toDateString(),
                'status' => Project::STATUS_COMPLETED,
                'featured' => false,
                'is_published' => true,
                'beneficiaries_count' => 1600,
            ],
            [
                'title' => 'Eid Food Package Distribution',
                'slug' => 'eid-food-package-distribution',
                'summary' => 'Festival food packages so that families in hardship can mark Eid with a proper meal.',
                'description' => $p([
                    'Ahead of Eid-ul-Fitr, 450 families received a festival package containing semai, sugar, powdered milk, dates, rice and meat vouchers redeemable at a local butcher.',
                    'The packages were delivered to homes rather than distributed from a central point, which preserves the dignity of recipients and avoids the crowding that makes public distribution unsafe.',
                ]),
                'category' => 'food_distribution',
                'location' => 'Khulna & Jashore',
                'latitude' => 23.166700,
                'longitude' => 89.208900,
                'start_date' => now()->subMonths(8)->toDateString(),
                'end_date' => now()->subMonths(8)->addDays(6)->toDateString(),
                'status' => Project::STATUS_COMPLETED,
                'featured' => false,
                'is_published' => true,
                'beneficiaries_count' => 2250,
            ],
            [
                'title' => 'Tree Plantation & Embankment Greening',
                'slug' => 'tree-plantation-and-embankment-greening',
                'summary' => 'Planting salt-tolerant species along coastal embankments to slow erosion and provide shade.',
                'description' => $p([
                    'In partnership with two local schools, volunteers planted 3,200 saplings of keora, jhau and neem along four kilometres of embankment.',
                    'Students from the participating schools have taken responsibility for watering during the first dry season, which historically is the single biggest factor in sapling survival.',
                ]),
                'category' => 'other',
                'location' => 'Dacope, Khulna',
                'latitude' => 22.573900,
                'longitude' => 89.510600,
                'start_date' => now()->subMonths(4)->toDateString(),
                'end_date' => null,
                'status' => Project::STATUS_ONGOING,
                'featured' => false,
                'is_published' => true,
                'beneficiaries_count' => 600,
            ],
        ];
    }
}
