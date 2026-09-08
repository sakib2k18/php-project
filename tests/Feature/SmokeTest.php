<?php

namespace Tests\Feature;

use App\Models\Announcement;
use App\Models\Campaign;
use App\Models\ContactMessage;
use App\Models\Donation;
use App\Models\Event;
use App\Models\GalleryItem;
use App\Models\Project;
use App\Models\SuccessStory;
use App\Models\TeamMember;
use App\Models\User;
use App\Models\Volunteer;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Renders every screen in the application against the seeded database, so a
 * Blade or query error anywhere surfaces in one run.
 */
class SmokeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
    }

    public function test_every_public_page_renders(): void
    {
        $campaign = Campaign::query()->published()->firstOrFail();
        $project = Project::query()->published()->firstOrFail();
        $event = Event::query()->published()->firstOrFail();
        $story = SuccessStory::query()->published()->firstOrFail();

        $urls = [
            '/',
            '/about',
            '/team',
            '/get-involved',
            '/search',
            '/search?q=flood',
            '/sitemap.xml',
            '/campaigns',
            '/campaigns?category=education&status=active&sort=progress',
            "/campaigns/{$campaign->slug}",
            '/projects',
            "/projects/{$project->slug}",
            '/events',
            "/events/{$event->slug}",
            '/stories',
            "/stories/{$story->slug}",
            '/gallery',
            '/gallery?category=relief',
            '/contact',
            '/login',
            '/register',
            '/forgot-password',
            '/api/weather',
        ];

        foreach ($urls as $url) {
            $this->assertSame(200, $this->get($url)->getStatusCode(), "Failed on {$url}");
        }
    }

    public function test_every_supporter_page_renders(): void
    {
        $user = User::query()->where('email', 'supporter@kuettry.org')->firstOrFail();
        $donation = Donation::query()->where('user_id', $user->id)->approved()->first()
            ?? Donation::factory()->approved()->create(['user_id' => $user->id]);

        $urls = [
            '/dashboard',
            '/profile',
            '/dashboard/donations',
            '/dashboard/donations?status=approved',
            '/donate',
            "/dashboard/donations/{$donation->id}",
            "/dashboard/donations/{$donation->id}/receipt",
            '/volunteer',
            '/notifications',
        ];

        foreach ($urls as $url) {
            $this->assertSame(200, $this->actingAs($user)->get($url)->getStatusCode(), "Failed on {$url}");
        }
    }

    public function test_every_admin_page_renders(): void
    {
        $admin = User::query()->admins()->firstOrFail();

        $campaign = Campaign::query()->firstOrFail();
        $donation = Donation::query()->firstOrFail();
        $project = Project::query()->firstOrFail();
        $event = Event::query()->firstOrFail();
        $story = SuccessStory::query()->firstOrFail();
        $announcement = Announcement::query()->firstOrFail();
        $gallery = GalleryItem::query()->firstOrFail();
        $member = TeamMember::query()->firstOrFail();
        $volunteer = Volunteer::query()->firstOrFail();
        $message = ContactMessage::query()->firstOrFail();
        $user = User::query()->members()->firstOrFail();

        $urls = [
            '/admin',

            '/admin/campaigns',
            '/admin/campaigns?trashed=1',
            '/admin/campaigns/create',
            "/admin/campaigns/{$campaign->slug}",
            "/admin/campaigns/{$campaign->slug}/edit",

            '/admin/donations',
            '/admin/donations?status=pending',
            '/admin/donations/reports',
            '/admin/donations/reports?months=12',
            "/admin/donations/{$donation->id}",

            '/admin/projects',
            '/admin/projects/create',
            "/admin/projects/{$project->slug}/edit",

            '/admin/events',
            '/admin/events/create',
            "/admin/events/{$event->slug}/edit",

            '/admin/stories',
            '/admin/stories/create',
            "/admin/stories/{$story->slug}/edit",

            '/admin/announcements',
            '/admin/announcements/create',
            "/admin/announcements/{$announcement->id}/edit",

            '/admin/gallery',
            '/admin/gallery/create',
            "/admin/gallery/{$gallery->id}/edit",

            '/admin/team',
            '/admin/team/create',
            "/admin/team/{$member->id}/edit",

            '/admin/volunteers',
            "/admin/volunteers/{$volunteer->id}",

            '/admin/users',
            "/admin/users/{$user->id}",

            '/admin/messages',
            "/admin/messages/{$message->id}",

            '/admin/activity',
            '/admin/settings',
            '/admin/profile',
            '/admin/password',
        ];

        foreach ($urls as $url) {
            $this->assertSame(200, $this->actingAs($admin)->get($url)->getStatusCode(), "Failed on {$url}");
        }
    }
}
