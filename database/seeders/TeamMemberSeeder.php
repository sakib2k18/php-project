<?php

namespace Database\Seeders;

use App\Models\TeamMember;
use Illuminate\Database\Seeder;

class TeamMemberSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->members() as $index => $data) {
            TeamMember::updateOrCreate(
                ['name' => $data['name']],
                $data + ['sort_order' => $index + 1, 'is_active' => true]
            );
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function members(): array
    {
        return [
            [
                'name' => 'Dr. Mahfuzur Rahman',
                'position' => 'Chief Adviser',
                'biography' => 'Professor of Civil Engineering at KUET and the founding adviser of KUET TRY. His research on coastal embankment failure shapes how the organisation plans its cyclone response.',
                'email' => 'adviser@kuettry.org',
                'facebook_url' => 'https://facebook.com/kuettry',
                'linkedin_url' => 'https://linkedin.com/',
            ],
            [
                'name' => 'Shahriar Kabir',
                'position' => 'President',
                'biography' => 'Final-year Electrical & Electronic Engineering student. Joined as a first-year volunteer on a winter distribution run and has led the executive committee for two terms.',
                'email' => 'president@kuettry.org',
                'facebook_url' => 'https://facebook.com/kuettry',
            ],
            [
                'name' => 'Ayesha Siddika',
                'position' => 'General Secretary',
                'biography' => 'Coordinates campaign planning and keeps the beneficiary verification process on track. Studies Urban & Regional Planning.',
                'email' => 'secretary@kuettry.org',
                'linkedin_url' => 'https://linkedin.com/',
            ],
            [
                'name' => 'Rifat Hossain',
                'position' => 'Treasurer',
                'biography' => 'Responsible for reconciling every recorded donation against the bank and mobile banking statements before it is approved. Studies Industrial Engineering & Management.',
                'email' => 'finance@kuettry.org',
            ],
            [
                'name' => 'Dr. Farzana Yasmin',
                'position' => 'Medical Wing Coordinator',
                'biography' => 'Physician at Khulna Medical College Hospital. Organises the quarterly medical camps and verifies every case referred to the Emergency Surgery Fund.',
                'email' => 'medical@kuettry.org',
            ],
            [
                'name' => 'Tanvir Alam',
                'position' => 'Field Operations Lead',
                'biography' => 'Leads the response teams that deploy within 48 hours of a disaster. Four winters on the night distribution runs and counting.',
                'email' => 'field@kuettry.org',
                'facebook_url' => 'https://facebook.com/kuettry',
            ],
            [
                'name' => 'Sadia Noor',
                'position' => 'Volunteer Coordinator',
                'biography' => 'Runs the orientation programme and matches new volunteers to the teams that fit their skills and availability.',
                'email' => 'volunteers@kuettry.org',
            ],
            [
                'name' => 'Imran Chowdhury',
                'position' => 'Communications & Media',
                'biography' => 'Writes the field reports and manages the photography consent process. Believes an unreadable report is the same as no report.',
                'email' => 'media@kuettry.org',
                'twitter_url' => 'https://twitter.com/',
            ],
        ];
    }
}
