<?php

namespace Database\Seeders;

use App\Models\OrganizationSetting;
use App\Services\SiteSettings;
use Illuminate\Database\Seeder;

class OrganizationSettingSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->settings() as $setting) {
            OrganizationSetting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }

        app(SiteSettings::class)->flush();
    }

    /**
     * @return array<int, array<string, string|null>>
     */
    protected function settings(): array
    {
        return [
            // ---- Identity -------------------------------------------------
            ['key' => 'org_name', 'label' => 'Organisation name', 'group' => 'identity', 'type' => 'string',
                'value' => 'KUET TRY'],
            ['key' => 'tagline', 'label' => 'Tagline', 'group' => 'identity', 'type' => 'string',
                'value' => 'Together, We Can Make a Difference.'],
            ['key' => 'about', 'label' => 'About the organisation', 'group' => 'identity', 'type' => 'text',
                'value' => 'KUET TRY is a humanitarian organisation founded by students, alumni and teachers of Khulna University of Engineering & Technology. What began as a small winter-clothing drive on campus has grown into a year-round effort that reaches families across the south-west of Bangladesh. We raise funds transparently, deliver aid ourselves, and publish where every taka goes. Our work spans emergency relief after cyclones and floods, food distribution for families living below the poverty line, medical assistance for patients who cannot afford treatment, and education support that keeps children in school.'],
            ['key' => 'mission', 'label' => 'Mission', 'group' => 'identity', 'type' => 'text',
                'value' => 'To stand beside families in crisis with practical, dignified and accountable help — reaching them quickly when disaster strikes and staying with them long enough to rebuild.'],
            ['key' => 'vision', 'label' => 'Vision', 'group' => 'identity', 'type' => 'text',
                'value' => 'A Bangladesh where no family is left to face hunger, illness or disaster alone, and where young people see service to their community as a lifelong habit rather than a one-off act.'],
            ['key' => 'logo', 'label' => 'Logo', 'group' => 'identity', 'type' => 'image', 'value' => null],
            ['key' => 'favicon', 'label' => 'Favicon', 'group' => 'identity', 'type' => 'image', 'value' => null],

            // ---- Contact --------------------------------------------------
            ['key' => 'email', 'label' => 'Email address', 'group' => 'contact', 'type' => 'email',
                'value' => 'info@kuettry.org'],
            ['key' => 'phone', 'label' => 'Phone', 'group' => 'contact', 'type' => 'string',
                'value' => '+880 1700-000000'],
            ['key' => 'emergency_contact', 'label' => 'Emergency hotline', 'group' => 'contact', 'type' => 'string',
                'value' => '+880 1800-000000'],
            ['key' => 'address', 'label' => 'Address', 'group' => 'contact', 'type' => 'string',
                'value' => 'Khulna University of Engineering & Technology, Fulbarigate, Khulna 9203, Bangladesh'],
            ['key' => 'office_hours', 'label' => 'Office hours', 'group' => 'contact', 'type' => 'string',
                'value' => 'Sunday – Thursday, 9:00 AM – 5:00 PM'],
            ['key' => 'latitude', 'label' => 'Latitude', 'group' => 'contact', 'type' => 'string',
                'value' => '22.899310'],
            ['key' => 'longitude', 'label' => 'Longitude', 'group' => 'contact', 'type' => 'string',
                'value' => '89.502289'],

            // ---- Social ---------------------------------------------------
            ['key' => 'facebook_url', 'label' => 'Facebook URL', 'group' => 'social', 'type' => 'url',
                'value' => 'https://facebook.com/kuettry'],
            ['key' => 'instagram_url', 'label' => 'Instagram URL', 'group' => 'social', 'type' => 'url',
                'value' => 'https://instagram.com/kuettry'],
            ['key' => 'youtube_url', 'label' => 'YouTube URL', 'group' => 'social', 'type' => 'url',
                'value' => 'https://youtube.com/@kuettry'],
            ['key' => 'linkedin_url', 'label' => 'LinkedIn URL', 'group' => 'social', 'type' => 'url',
                'value' => null],

            // ---- Donation instructions ------------------------------------
            ['key' => 'bank_details', 'label' => 'Bank transfer details', 'group' => 'donation', 'type' => 'text',
                'value' => "Account name: KUET TRY\nAccount number: 0000 1122 3344\nBank: Sonali Bank PLC, KUET Branch, Khulna\nRouting number: 200470000"],
            ['key' => 'mobile_banking_details', 'label' => 'Mobile banking details', 'group' => 'donation', 'type' => 'text',
                'value' => "bKash (Merchant): 01700-000000\nNagad: 01700-000000\nRocket: 01700-000000-4\n\nPlease use the \"Send Money\" option and keep the transaction ID — you will need it when recording your donation."],
        ];
    }
}
