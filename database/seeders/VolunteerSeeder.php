<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Volunteer;
use Illuminate\Database\Seeder;

class VolunteerSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::query()->admins()->first();
        $members = User::query()->members()->orderBy('id')->take(9)->get();

        $profiles = [
            ['skills' => 'Event management, photography, basic first aid', 'activity' => 'Event management', 'availability' => 'weekends', 'status' => Volunteer::STATUS_APPROVED],
            ['skills' => 'Teaching mathematics and physics, curriculum planning', 'activity' => 'Teaching & tutoring', 'availability' => 'evenings', 'status' => Volunteer::STATUS_APPROVED],
            ['skills' => 'Logistics, driving (light vehicle licence), warehouse handling', 'activity' => 'Logistics & transport', 'availability' => 'flexible', 'status' => Volunteer::STATUS_APPROVED],
            ['skills' => 'Social media, content writing, graphic design', 'activity' => 'Fundraising', 'availability' => 'weekdays', 'status' => Volunteer::STATUS_APPROVED],
            ['skills' => 'Nursing student, first aid, patient triage', 'activity' => 'Medical camp support', 'availability' => 'weekends', 'status' => Volunteer::STATUS_APPROVED],
            ['skills' => 'Accounting, spreadsheet reconciliation, record keeping', 'activity' => 'Administration', 'availability' => 'weekdays', 'status' => Volunteer::STATUS_APPROVED],
            ['skills' => 'Photography and videography, drone operation', 'activity' => 'Photography & media', 'availability' => 'flexible', 'status' => Volunteer::STATUS_PENDING],
            ['skills' => 'General help, willing to learn anything needed', 'activity' => 'Relief distribution', 'availability' => 'weekends', 'status' => Volunteer::STATUS_PENDING],
            ['skills' => 'Public speaking', 'activity' => 'Fundraising', 'availability' => 'weekdays', 'status' => Volunteer::STATUS_REJECTED],
        ];

        foreach ($members as $index => $member) {
            $profile = $profiles[$index] ?? $profiles[0];

            $volunteer = Volunteer::updateOrCreate(
                ['user_id' => $member->id],
                [
                    'name' => $member->name,
                    'email' => $member->email,
                    'phone' => $member->phone ?: '+880 1700-000000',
                    'student_id' => $member->student_id,
                    'institution' => 'Khulna University of Engineering & Technology',
                    'address' => $member->address ?: 'Khulna, Bangladesh',
                    'skills' => $profile['skills'],
                    'availability' => $profile['availability'],
                    'preferred_activity' => $profile['activity'],
                    'motivation' => $this->motivation($index),
                ]
            );

            $volunteer->forceFill([
                'status' => $profile['status'],
                'reviewed_by' => $profile['status'] === Volunteer::STATUS_PENDING ? null : $admin?->id,
                'reviewed_at' => $profile['status'] === Volunteer::STATUS_PENDING ? null : now()->subDays(($index + 1) * 4),
                'admin_note' => $profile['status'] === Volunteer::STATUS_REJECTED
                    ? 'Availability did not match the roles open this term. Encouraged to reapply next season.'
                    : null,
            ])->save();
        }
    }

    protected function motivation(int $index): string
    {
        $lines = [
            'I joined a winter distribution run in my first year because a friend asked me to. I have not missed one since — you cannot un-see someone sleeping under a sack in December.',
            'I teach two evenings a week already and would like to do it where it actually changes whether a child stays in school.',
            'I have a light vehicle licence and free weekends. Somebody has to drive the truck and I would rather it were me than nobody.',
            'I want to help with the reports and the social media. Good work that nobody hears about does not raise the next campaign.',
            'I am a nursing student and the medical camps are the best practical experience I could ask for, but mostly I want to be useful.',
            'I am good with spreadsheets and I know the treasurer is doing all the reconciliation alone. I would like to take some of that off him.',
            'I photograph events for the department and I would like to document the field work properly, with consent taken the right way.',
            'I have no particular skill to offer yet. I can carry things, count things and show up on time, and I will learn the rest.',
            'I would like to speak at fundraising events and help bring in corporate sponsors from my family network.',
        ];

        return $lines[$index] ?? $lines[0];
    }
}
