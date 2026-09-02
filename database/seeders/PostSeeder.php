<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Seeder;

class PostSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::query()->admins()->first();

        foreach ($this->posts() as $data) {
            Post::updateOrCreate(
                ['slug' => $data['slug']],
                $data + ['user_id' => $admin?->id]
            );
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function posts(): array
    {
        $p = fn (array $paragraphs) => collect($paragraphs)->map(fn ($t) => "<p>{$t}</p>")->implode("\n");

        return [
            [
                'title' => 'Cyclone Response: What Your Donations Delivered in the First Fortnight',
                'slug' => 'cyclone-response-what-your-donations-delivered-in-the-first-fortnight',
                'excerpt' => 'A full account of the first two weeks of our coastal response — what was delivered, to whom, and what it cost.',
                'content' => $p([
                    'Fourteen days after landfall, our teams have completed the first phase of the coastal response. This report sets out exactly what was delivered.',
                    '<strong>Food and water.</strong> 640 family kits were distributed across Maheshwaripur, Kamarkhola and Sutarkhali unions. Each kit contained 10 kg rice, 2 kg lentils, 1 litre of cooking oil, oral saline, water purification tablets and candles. Cost per kit: Tk 1,180.',
                    '<strong>Shelter.</strong> 240 tarpaulins were handed out in the first 72 hours. In the second week we began supplying corrugated sheets and bamboo to 85 households whose roofs were destroyed outright.',
                    '<strong>Water points.</strong> Six shallow tube wells were flushed and disinfected, restoring drinking water to roughly 400 households.',
                    'Total expenditure in phase one was Tk 942,000. The complete distribution list, with signatures, is held at our office and available to any donor who asks to see it.',
                    'Phase two — seed packages for the coming planting season — begins next week and is still short of its target.',
                ]),
                'category' => 'field_report',
                'author' => 'Field Operations Team',
                'status' => Post::STATUS_PUBLISHED,
                'featured' => true,
                'published_at' => now()->subDays(9),
                'views' => 842,
            ],
            [
                'title' => 'Why We Publish Every Taka We Spend',
                'slug' => 'why-we-publish-every-taka-we-spend',
                'excerpt' => 'Transparency is not a marketing choice for us — it is the only reason a stranger will trust a student organisation with their money.',
                'content' => $p([
                    'KUET TRY started with a table outside the library and a cardboard box. The first question anyone asked was the same one people ask us today: how do I know where this money goes?',
                    'Our answer has always been to publish. Every campaign page shows the target, the amount raised and the number of people reached. Every field report lists what was bought, at what price, and for how many households.',
                    'We also publish what did not go to plan. Last winter we over-ordered sweaters in one size and had to redistribute them to a partner organisation. That is in the report too, because a report that only contains successes is advertising, not accounting.',
                    'Donations are verified before they count. When you record a donation on this website it stays "pending" until a member of the team matches it against the bank or mobile banking statement. Only then does it appear in a campaign total.',
                    'It is slower than a payment gateway. It is also the reason our numbers can be trusted.',
                ]),
                'category' => 'news',
                'author' => 'KUET TRY Executive Committee',
                'status' => Post::STATUS_PUBLISHED,
                'featured' => false,
                'published_at' => now()->subDays(23),
                'views' => 517,
            ],
            [
                'title' => 'Winter Distribution Reaches 1,850 Families in Kurigram',
                'slug' => 'winter-distribution-reaches-1850-families-in-kurigram',
                'excerpt' => 'Eleven nights, 62 volunteers and 1,850 blankets across eight unions of the north.',
                'content' => $p([
                    'The 2025 winter drive closed last week having distributed 1,850 blankets and 900 sweaters across eight unions in Kurigram and Khulna.',
                    'Distribution happened almost entirely at night. That is deliberate: the people who most need a blanket — rickshaw pullers, night guards, families sleeping on platforms — are not at home during the day, and daytime distribution tends to reach whoever can queue longest rather than whoever is coldest.',
                    'For the village distributions, union parishad members prepared household lists in advance, prioritising elderly residents living alone and families with infants.',
                    'Total expenditure was Tk 1,142,000. 94% went directly to goods and transport; the remainder covered volunteer meals and fuel.',
                ]),
                'category' => 'field_report',
                'author' => 'Logistics Team',
                'status' => Post::STATUS_PUBLISHED,
                'featured' => false,
                'published_at' => now()->subMonths(12),
                'views' => 690,
            ],
            [
                'title' => 'Medical Camp at Phultala: 462 Patients in One Day',
                'slug' => 'medical-camp-at-phultala-462-patients-in-one-day',
                'excerpt' => 'Eight physicians, twenty students and a queue that started before sunrise.',
                'content' => $p([
                    'Our quarterly medical camp at Phultala saw 462 patients between 8 AM and 6 PM — the highest single-day figure we have recorded.',
                    'The most common presentations were untreated hypertension, uncontrolled diabetes, and skin conditions related to saline water exposure. 38 patients were screened for the first time in their lives.',
                    'Every patient left with a one-month supply of prescribed medicine. Nineteen were referred for specialist care and four have since been supported through the Emergency Surgery Fund.',
                    'The next camp is scheduled for later this quarter. Volunteer physicians and final-year medical students are welcome to join.',
                ]),
                'category' => 'field_report',
                'author' => 'Medical Wing',
                'status' => Post::STATUS_PUBLISHED,
                'featured' => false,
                'published_at' => now()->subMonths(3),
                'views' => 402,
            ],
            [
                'title' => 'How to Record a Donation on the New Website',
                'slug' => 'how-to-record-a-donation-on-the-new-website',
                'excerpt' => 'A short walkthrough of the donation flow, from choosing a campaign to downloading your receipt.',
                'content' => $p([
                    'Our new website lets you record a donation yourself and track its verification, instead of emailing us a screenshot and hoping for a reply.',
                    '<strong>Step one.</strong> Transfer your contribution using bank transfer, bKash, Nagad or Rocket. Keep the transaction ID.',
                    '<strong>Step two.</strong> Create an account, choose the campaign you supported, and fill in the donation form with the amount, the method and the transaction reference.',
                    '<strong>Step three.</strong> Your record appears in your dashboard as "pending". A member of the team matches it against our statement, usually within two working days.',
                    '<strong>Step four.</strong> Once approved, the amount is added to the campaign total and a printable receipt becomes available in your donation history.',
                    'If you prefer to donate in cash at our office, tell us and we will record it for you.',
                ]),
                'category' => 'announcement',
                'author' => 'KUET TRY Web Team',
                'status' => Post::STATUS_PUBLISHED,
                'featured' => false,
                'published_at' => now()->subDays(40),
                'views' => 1104,
            ],
            [
                'title' => 'Meet the Volunteers Behind the Night Distributions',
                'slug' => 'meet-the-volunteers-behind-the-night-distributions',
                'excerpt' => 'Three students explain why they spend January nights on the back of a pickup truck instead of in a warm hall.',
                'content' => $p([
                    'Every winter, a small group of volunteers gives up eleven nights of sleep to load, drive and distribute blankets between 9 PM and 3 AM.',
                    'Tanvir, a third-year mechanical engineering student, has done four winters. "The first year I went because a friend asked. I kept going because you cannot un-see someone sleeping under a sack in December."',
                    'Sadia coordinates the packing shifts. "People think the hard part is the cold. The hard part is counting. If the list says 180 and the truck has 174, somebody is going home empty-handed and you have to decide who."',
                    'Rakib drives one of the two pickups. "We stop, we hand it over, we do not photograph faces without asking. That rule matters more than the photographs."',
                    'Volunteer applications for the coming season are open on the Get Involved page.',
                ]),
                'category' => 'story',
                'author' => 'Communications Team',
                'status' => Post::STATUS_PUBLISHED,
                'featured' => false,
                'published_at' => now()->subMonths(11),
                'views' => 356,
            ],
            [
                'title' => 'Annual Report 2025 — Draft in Preparation',
                'slug' => 'annual-report-2025-draft-in-preparation',
                'excerpt' => 'Our full annual report is being compiled and will be published here once the accounts are reviewed.',
                'content' => $p([
                    'The annual report covering the past financial year is being compiled. It will include audited expenditure by category, a full campaign-by-campaign breakdown and our volunteer hours.',
                    'This post is a placeholder and will be replaced with the report itself.',
                ]),
                'category' => 'announcement',
                'author' => 'KUET TRY Executive Committee',
                'status' => Post::STATUS_DRAFT,
                'featured' => false,
                'published_at' => null,
                'views' => 0,
            ],
        ];
    }
}
