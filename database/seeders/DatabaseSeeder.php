<?php

namespace Database\Seeders;

use App\Services\SiteSettings;
use App\Services\StatisticsService;
use Illuminate\Database\Seeder;

/**
 * `php artisan migrate:fresh --seed` produces a complete, populated website:
 * one administrator, fifteen supporters, nine campaigns with donation history,
 * projects, events, stories, gallery, team and messages.
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            OrganizationSettingSeeder::class,
            AdminSeeder::class,
            UserSeeder::class,
            CampaignSeeder::class,
            ProjectSeeder::class,
            EventSeeder::class,
            SuccessStorySeeder::class,
            AnnouncementSeeder::class,
            TeamMemberSeeder::class,
            GallerySeeder::class,
            DonationSeeder::class,
            VolunteerSeeder::class,
            ContactMessageSeeder::class,
            ActivityLogSeeder::class,
        ]);

        app(SiteSettings::class)->flush();
        app(StatisticsService::class)->flushPublicCache();

        $this->command?->newLine();
        $this->command?->info('KUET TRY sample data ready.');
        $this->command?->line('  Admin      '.AdminSeeder::EMAIL.'  /  '.AdminSeeder::PASSWORD);
        $this->command?->line('  Supporter  supporter@kuettry.org  /  '.UserSeeder::DEMO_PASSWORD);
    }
}
