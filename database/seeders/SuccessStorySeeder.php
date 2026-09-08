<?php

namespace Database\Seeders;

use App\Models\Campaign;
use App\Models\SuccessStory;
use Illuminate\Database\Seeder;

class SuccessStorySeeder extends Seeder
{
    public function run(): void
    {
        $campaigns = Campaign::query()->pluck('id', 'slug');

        foreach ($this->stories() as $data) {
            $campaignSlug = $data['campaign_slug'] ?? null;
            unset($data['campaign_slug']);

            $data['campaign_id'] = $campaignSlug ? ($campaigns[$campaignSlug] ?? null) : null;
            $data['image'] = $this->images()[$data['slug']] ?? null;

            SuccessStory::updateOrCreate(['slug' => $data['slug']], $data);
        }
    }

    /**
     * Photographs bundled with the site (public/images/stories),
     * keyed by story slug.
     *
     * @return array<string, string>
     */
    protected function images(): array
    {
        return [
            'rehana-rebuilt-her-home-and-her-tailoring-business' => '/images/stories/story-01.jpg',
            'sabbir-sat-his-exams-after-all' => '/images/stories/story-02.jpg',
            'a-surgery-that-could-not-wait' => '/images/stories/story-03.jpg',
            'clean-water-four-kilometres-closer' => '/images/stories/story-04.jpg',
            'two-hundred-kitchens-every-month' => '/images/stories/story-05.jpg',
            'a-blanket-on-the-coldest-night-of-the-year' => '/images/stories/story-06.jpg',
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function stories(): array
    {
        $p = fn (array $paragraphs) => collect($paragraphs)->map(fn ($t) => "<p>{$t}</p>")->implode("\n");

        return [
            [
                'title' => 'Rehana Rebuilt Her Home — and Her Tailoring Business',
                'slug' => 'rehana-rebuilt-her-home-and-her-tailoring-business',
                'beneficiary_name' => 'Rehana Begum',
                'beneficiary_description' => 'Widow and mother of three from Koyra, Khulna.',
                'story' => $p([
                    'When the embankment broke, Rehana lost the tin roof over her head and the sewing machine that fed her family. "The water came in the night," she told our field team. "By morning there was nothing left but the frame of the house."',
                    'Our first delivery reached her within four days: dry food, a tarpaulin and water purification tablets. That kept the family going while the water receded.',
                    'In the second phase we supplied corrugated sheets and bamboo for the roof, and replaced her sewing machine. Six months later she is taking orders again from three neighbouring villages and has trained her eldest daughter to help.',
                    '"I do not want charity every year," she said. "I wanted my machine back. Now I am working."',
                ]),
                'location' => 'Koyra, Khulna',
                'story_date' => now()->subMonths(4)->toDateString(),
                'is_published' => true,
                'featured' => true,
                'campaign_slug' => 'cyclone-response-fund-coastal-khulna',
            ],
            [
                'title' => 'Sabbir Sat His Exams After All',
                'slug' => 'sabbir-sat-his-exams-after-all',
                'beneficiary_name' => 'Sabbir Hossain',
                'beneficiary_description' => 'Class ten student, son of a day labourer.',
                'story' => $p([
                    'Sabbir had stopped attending school two months before his SSC registration. His father, a day labourer, could not cover the exam fee, the coaching that everyone else was taking, or the books.',
                    'His head teacher flagged the case to our education team. We covered the registration fee, six months of evening coaching, and a full set of books and stationery.',
                    'He sat the examination and passed with GPA 4.61. He is now in college and volunteers with us at weekend distributions.',
                    '"I thought that was the end of school for me," he said. "One form and one phone call changed it."',
                ]),
                'location' => 'Rupsha, Khulna',
                'story_date' => now()->subMonths(7)->toDateString(),
                'is_published' => true,
                'featured' => true,
                'campaign_slug' => 'school-bags-and-books-for-500-children',
            ],
            [
                'title' => 'A Surgery That Could Not Wait',
                'slug' => 'a-surgery-that-could-not-wait',
                'beneficiary_name' => 'Abdul Malek',
                'beneficiary_description' => 'Van puller, 52, from Dumuria.',
                'story' => $p([
                    'Abdul Malek arrived at Khulna Medical College with a strangulated hernia and a bill he had no way of paying. Surgery was needed the same day.',
                    'Our medical volunteers verified the case with the treating physician within two hours and the fund settled the hospital bill of Tk 34,000 directly.',
                    'He was discharged after five days and returned to work three weeks later. "I have four people at home who eat because I pedal," he said. "If I had waited to arrange money, I do not know what would have happened."',
                ]),
                'location' => 'Dumuria, Khulna',
                'story_date' => now()->subMonths(2)->toDateString(),
                'is_published' => true,
                'featured' => false,
                'campaign_slug' => 'emergency-surgery-fund',
            ],
            [
                'title' => 'Clean Water, Four Kilometres Closer',
                'slug' => 'clean-water-four-kilometres-closer',
                'beneficiary_name' => 'Momtaz Khatun',
                'beneficiary_description' => 'Mother of two from Gabura union, Shyamnagar.',
                'story' => $p([
                    'For eleven years Momtaz walked eight kilometres a day, twice, to fetch drinking water. The groundwater near her home has been too saline to drink since the last major surge.',
                    'A 2,000-litre rainwater harvesting tank was installed at her house in the monsoon. It carried the family through the dry season without a single trip.',
                    '"My daughters used to miss school on the days I was ill and they had to walk instead," she said. "That has not happened once this year."',
                ]),
                'location' => 'Gabura, Shyamnagar',
                'story_date' => now()->subMonths(6)->toDateString(),
                'is_published' => true,
                'featured' => false,
                'campaign_slug' => 'safe-water-for-sundarban-villages',
            ],
            [
                'title' => 'Two Hundred Kitchens, Every Month',
                'slug' => 'two-hundred-kitchens-every-month',
                'beneficiary_name' => 'Jahanara Begum',
                'beneficiary_description' => 'Elderly resident living alone in Khalishpur.',
                'story' => $p([
                    'Jahanara is 71 and has no pension and no surviving family nearby. Before she joined our monthly list she was eating one meal a day, mostly rice with salt.',
                    'The monthly parcel covers her staples for four weeks. Volunteers who deliver it also check on her health and have twice arranged a doctor\'s visit.',
                    '"It is not only the rice," she said. "Somebody knocks on my door on the first Friday. I know they will come."',
                ]),
                'location' => 'Khalishpur, Khulna',
                'story_date' => now()->subMonths(1)->toDateString(),
                'is_published' => true,
                'featured' => false,
                'campaign_slug' => 'monthly-food-parcels-for-200-families',
            ],
            [
                'title' => 'A Blanket on the Coldest Night of the Year',
                'slug' => 'a-blanket-on-the-coldest-night-of-the-year',
                'beneficiary_name' => 'Amjad Ali',
                'beneficiary_description' => 'Rickshaw puller, sleeps at the Kurigram bus stand.',
                'story' => $p([
                    'Our night distribution team found Amjad at the bus stand at 11 PM in January, wrapped in a jute sack. The temperature that night reached 6°C.',
                    'He received a blanket and a sweater. On the follow-up run two weeks later he was still using both, and pointed the team towards four other men sleeping nearby who had been missed.',
                    'That is how most of our list grows: the people we reach tell us who else needs reaching.',
                ]),
                'location' => 'Kurigram',
                'story_date' => now()->subMonths(12)->toDateString(),
                'is_published' => true,
                'featured' => false,
                'campaign_slug' => 'winter-warmth-2026-blankets-for-the-north',
            ],
        ];
    }
}
