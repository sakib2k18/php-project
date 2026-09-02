<?php

namespace Database\Seeders;

use App\Models\Campaign;
use App\Models\CampaignUpdate;
use App\Models\User;
use Illuminate\Database\Seeder;

class CampaignSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::query()->admins()->first();

        foreach ($this->campaigns() as $data) {
            $updates = $data['updates'] ?? [];
            unset($data['updates']);

            $campaign = Campaign::updateOrCreate(
                ['slug' => $data['slug']],
                $data + ['created_by' => $admin?->id]
            );

            foreach ($updates as $index => $update) {
                CampaignUpdate::updateOrCreate(
                    ['campaign_id' => $campaign->id, 'title' => $update['title']],
                    [
                        'body' => $update['body'],
                        'published_on' => $update['published_on'],
                        'created_by' => $admin?->id,
                    ]
                );
            }
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function campaigns(): array
    {
        $p = fn (array $paragraphs) => collect($paragraphs)->map(fn ($t) => "<p>{$t}</p>")->implode("\n");

        return [
            [
                'title' => 'Cyclone Response Fund — Coastal Khulna',
                'slug' => 'cyclone-response-fund-coastal-khulna',
                'short_description' => 'Emergency shelter, dry food and clean water for families whose homes were destroyed along the Khulna coastline.',
                'description' => $p([
                    'When a cyclone crosses the Bay of Bengal, the embankment villages of Koyra and Dacope are the first to lose everything. Salt water floods the paddy fields, tube wells go under, and mud-and-tin homes collapse within minutes.',
                    'Our response team reaches the affected unions within 48 hours of landfall with a standard family kit: 10 kg rice, 2 kg lentils, cooking oil, oral saline, water purification tablets, candles, and a tarpaulin large enough to shelter a family of five.',
                    'Beyond the first week we help households rebuild: bamboo and corrugated sheets for roofing, seed packages so the next planting season is not lost, and repair of the shallow tube wells that supply drinking water to entire neighbourhoods.',
                    'Every kit is handed over against a signed list, and the full distribution record is published in our field reports so donors can see exactly where their contribution went.',
                ]),
                'category' => 'natural_disaster',
                'target_amount' => 850000,
                'start_date' => now()->subDays(24)->toDateString(),
                'end_date' => now()->addDays(38)->toDateString(),
                'location' => 'Koyra & Dacope, Khulna',
                'latitude' => 22.339000,
                'longitude' => 89.291000,
                'status' => Campaign::STATUS_ACTIVE,
                'featured' => true,
                'is_emergency' => true,
                'beneficiaries_count' => 1240,
                'updates' => [
                    [
                        'title' => 'First 300 family kits delivered in Koyra',
                        'body' => 'Our first convoy reached Maheshwaripur union on Friday morning. 300 families received dry food, tarpaulins and water purification tablets. Two shallow tube wells have been flushed and are producing safe water again.',
                        'published_on' => now()->subDays(18)->toDateString(),
                    ],
                    [
                        'title' => 'Roofing materials on the way to Dacope',
                        'body' => 'Thanks to donations received this week we have ordered corrugated sheets and bamboo for 85 households in Kamarkhola. Distribution begins on Saturday with the union council keeping the beneficiary list.',
                        'published_on' => now()->subDays(6)->toDateString(),
                    ],
                ],
            ],
            [
                'title' => 'Winter Warmth 2026 — Blankets for the North',
                'slug' => 'winter-warmth-2026-blankets-for-the-north',
                'short_description' => 'Thick blankets and warm clothing for elderly residents, rickshaw pullers and street families through the coldest weeks of the year.',
                'description' => $p([
                    'Between late December and early February, night temperatures in northern Bangladesh drop close to 5°C. For families sleeping on verandas, in unfinished buildings or on railway platforms, a single blanket is the difference between a bearable night and a dangerous one.',
                    'KUET TRY buys blankets in bulk directly from mills, which brings the cost per blanket down to roughly Tk 550 including transport. Volunteers distribute at night, when the people who need them most are easiest to reach.',
                    'Priority goes to elderly people living alone, families with infants, rickshaw and van pullers who work through the night, and residents of char lands where relief rarely arrives.',
                ]),
                'category' => 'winter_support',
                'target_amount' => 450000,
                'start_date' => now()->subDays(52)->toDateString(),
                'end_date' => now()->addDays(20)->toDateString(),
                'location' => 'Kurigram, Nilphamari & Khulna',
                'latitude' => 25.805700,
                'longitude' => 89.636200,
                'status' => Campaign::STATUS_ACTIVE,
                'featured' => true,
                'is_emergency' => false,
                'beneficiaries_count' => 780,
                'updates' => [
                    [
                        'title' => '420 blankets distributed in Kurigram',
                        'body' => 'Over three nights our volunteers distributed 420 blankets across four unions of Chilmari upazila. The local union parishad helped identify elderly residents living alone.',
                        'published_on' => now()->subDays(11)->toDateString(),
                    ],
                ],
            ],
            [
                'title' => 'School Bags & Books for 500 Children',
                'slug' => 'school-bags-and-books-for-500-children',
                'short_description' => 'A full year of exercise books, stationery and a sturdy bag for children whose families cannot afford school supplies.',
                'description' => $p([
                    'Tuition is free in government primary schools, but exercise books, pens, geometry sets and a bag are not. For a family earning under Tk 9,000 a month, that gap is often the reason a child stops attending after class three.',
                    'Our education package costs Tk 1,150 per child and covers a school bag, twelve exercise books, pens and pencils, a geometry box, and a raincoat for the monsoon months.',
                    'We work through head teachers, who know exactly which children are at risk of dropping out. Attendance is reviewed after six months so we can measure whether the support actually kept children in class.',
                ]),
                'category' => 'education',
                'target_amount' => 575000,
                'start_date' => now()->subDays(70)->toDateString(),
                'end_date' => now()->addDays(45)->toDateString(),
                'location' => 'Khulna & Bagerhat',
                'latitude' => 22.815600,
                'longitude' => 89.568700,
                'status' => Campaign::STATUS_ACTIVE,
                'featured' => true,
                'is_emergency' => false,
                'beneficiaries_count' => 500,
                'updates' => [],
            ],
            [
                'title' => 'Emergency Surgery Fund',
                'slug' => 'emergency-surgery-fund',
                'short_description' => 'Covering operation costs for patients referred to Khulna Medical College Hospital who cannot pay for treatment.',
                'description' => $p([
                    'A hernia repair, a caesarean section or the fixation of a broken femur can cost between Tk 18,000 and Tk 60,000 at a district hospital. For a day labourer, that is several months of income and the operation simply does not happen.',
                    'This fund pays hospital bills directly — never in cash to the patient — after a case is verified by our medical volunteers and the treating physician. Priority is given to emergency and life-limiting cases.',
                    'Every disbursement is recorded against the hospital invoice number, and a short anonymised summary is published in our reports.',
                ]),
                'category' => 'medical',
                'target_amount' => 600000,
                'start_date' => now()->subDays(120)->toDateString(),
                'end_date' => now()->addDays(90)->toDateString(),
                'location' => 'Khulna Medical College Hospital',
                'latitude' => 22.815300,
                'longitude' => 89.545900,
                'status' => Campaign::STATUS_ACTIVE,
                'featured' => false,
                'is_emergency' => false,
                'beneficiaries_count' => 64,
                'updates' => [],
            ],
            [
                'title' => 'Monthly Food Parcels for 200 Families',
                'slug' => 'monthly-food-parcels-for-200-families',
                'short_description' => 'A month of staple food for households headed by widows, elderly people and persons with disabilities.',
                'description' => $p([
                    'Our food parcel is designed with a nutritionist and lasts a family of five roughly four weeks: 25 kg rice, 4 kg lentils, 3 litres of soybean oil, 2 kg sugar, salt, spices and 1 kg powdered milk.',
                    'The 200 households on our list were selected with local ward councillors and are re-verified every six months. Most are headed by widows, elderly people without pensions, or people living with a disability that prevents daily labour.',
                    'One parcel costs Tk 2,850. A monthly gift of that amount keeps one household fed for a year.',
                ]),
                'category' => 'food_distribution',
                'target_amount' => 570000,
                'start_date' => now()->subDays(200)->toDateString(),
                'end_date' => now()->addDays(160)->toDateString(),
                'location' => 'Khulna City Corporation',
                'latitude' => 22.845600,
                'longitude' => 89.540300,
                'status' => Campaign::STATUS_ACTIVE,
                'featured' => false,
                'is_emergency' => false,
                'beneficiaries_count' => 1000,
                'updates' => [],
            ],
            [
                'title' => 'Safe Water for Sundarban Villages',
                'slug' => 'safe-water-for-sundarban-villages',
                'short_description' => 'Rainwater harvesting tanks and deep tube wells for villages where the groundwater has turned saline.',
                'description' => $p([
                    'In the villages bordering the Sundarbans, shallow groundwater is now too saline to drink. Women walk up to four kilometres twice a day to collect water, and skin and stomach illnesses are common.',
                    'A 2,000-litre household rainwater harvesting tank costs about Tk 12,000 and carries a family through most of the dry season. A community deep tube well serves around 40 households.',
                    'This campaign funded 38 household tanks and two community tube wells across Shyamnagar and Koyra, and reached its target in November.',
                ]),
                'category' => 'emergency_relief',
                'target_amount' => 480000,
                'start_date' => now()->subMonths(11)->toDateString(),
                'end_date' => now()->subMonths(5)->toDateString(),
                'location' => 'Shyamnagar, Satkhira',
                'latitude' => 22.325600,
                'longitude' => 89.109700,
                'status' => Campaign::STATUS_COMPLETED,
                'featured' => false,
                'is_emergency' => false,
                'beneficiaries_count' => 1600,
                'updates' => [
                    [
                        'title' => 'Target reached — 38 tanks installed',
                        'body' => 'The campaign closed fully funded. 38 household rainwater tanks and two community deep tube wells are now in use in Gabura and Padmapukur unions.',
                        'published_on' => now()->subMonths(5)->toDateString(),
                    ],
                ],
            ],
            [
                'title' => 'Flood Relief — Sylhet & Sunamganj',
                'slug' => 'flood-relief-sylhet-and-sunamganj',
                'short_description' => 'Completed appeal that delivered dry food and cash grants to families displaced by the haor flooding.',
                'description' => $p([
                    'Flash floods across the haor basin left tens of thousands of families sheltering on embankments and in school buildings with no dry food and no clean water.',
                    'Working with a partner student organisation in Sylhet, we delivered 620 dry-food packets and unconditional cash grants of Tk 3,000 to 180 of the worst-affected households.',
                    'The appeal closed after eight weeks, fully funded. Our complete distribution list and expenditure statement are available on request.',
                ]),
                'category' => 'natural_disaster',
                'target_amount' => 700000,
                'start_date' => now()->subMonths(16)->toDateString(),
                'end_date' => now()->subMonths(14)->toDateString(),
                'location' => 'Sunamganj, Sylhet',
                'latitude' => 25.065600,
                'longitude' => 91.395000,
                'status' => Campaign::STATUS_COMPLETED,
                'featured' => false,
                'is_emergency' => false,
                'beneficiaries_count' => 3100,
                'updates' => [],
            ],
            [
                'title' => 'Orphan Education Sponsorship',
                'slug' => 'orphan-education-sponsorship',
                'short_description' => 'Year-long sponsorship covering school fees, uniforms, private tuition and a monthly food allowance for orphaned children.',
                'description' => $p([
                    'Forty-two children on our sponsorship list have lost one or both parents. Sponsorship covers school and exam fees, two uniforms, shoes, books, coaching for board-exam years, and a Tk 1,500 monthly food allowance paid to the guardian.',
                    'A full year of sponsorship for one child costs Tk 26,000. Sponsors receive a short progress note each term with the child\'s attendance and exam results.',
                ]),
                'category' => 'orphan_support',
                'target_amount' => 1092000,
                'start_date' => now()->subDays(35)->toDateString(),
                'end_date' => now()->addMonths(4)->toDateString(),
                'location' => 'Khulna Division',
                'latitude' => 22.845600,
                'longitude' => 89.540300,
                'status' => Campaign::STATUS_ACTIVE,
                'featured' => false,
                'is_emergency' => false,
                'beneficiaries_count' => 42,
                'updates' => [],
            ],
            [
                'title' => 'Skills Training for Young Women',
                'slug' => 'skills-training-for-young-women',
                'short_description' => 'A six-month tailoring and computer literacy course with a sewing machine on graduation.',
                'description' => $p([
                    'This programme is being prepared for the next financial year. Thirty young women from low-income households will take a six-month course in tailoring and basic computer literacy, followed by a sewing machine and a small starting stock of materials on graduation.',
                    'The curriculum is being designed with a local training institute. Detailed costing will be published when the campaign opens.',
                ]),
                'category' => 'poverty_relief',
                'target_amount' => 900000,
                'start_date' => now()->addDays(30)->toDateString(),
                'end_date' => now()->addMonths(9)->toDateString(),
                'location' => 'Khulna',
                'latitude' => 22.845600,
                'longitude' => 89.540300,
                'status' => Campaign::STATUS_DRAFT,
                'featured' => false,
                'is_emergency' => false,
                'beneficiaries_count' => 30,
                'updates' => [],
            ],
        ];
    }
}
