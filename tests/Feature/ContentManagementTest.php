<?php

namespace Tests\Feature;

use App\Models\Announcement;
use App\Models\Event;
use App\Models\GalleryItem;
use App\Models\Project;
use App\Models\SuccessStory;
use App\Models\TeamMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\MakesTestImages;
use Tests\TestCase;

/**
 * CRUD coverage for the remaining editorial resources.
 */
class ContentManagementTest extends TestCase
{
    use MakesTestImages, RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
        Storage::fake('public');
    }

    // ---------------- Projects ----------------

    public function test_an_administrator_can_manage_projects(): void
    {
        $payload = [
            'title' => 'Safe Water Initiative 2026',
            'summary' => 'Rainwater harvesting tanks for households in the salinity-affected coastal belt.',
            'description' => str_repeat('What was installed, where, and for how many households. ', 4),
            'category' => 'emergency_relief',
            'location' => 'Shyamnagar, Satkhira',
            'start_date' => now()->subMonth()->toDateString(),
            'status' => Project::STATUS_ONGOING,
            'is_published' => true,
            'beneficiaries_count' => 1200,
        ];

        $this->actingAs($this->admin)->post('/admin/projects', $payload)->assertRedirect();
        $project = Project::query()->firstOrFail();
        $this->assertSame('safe-water-initiative-2026', $project->slug);

        $this->actingAs($this->admin)
            ->put("/admin/projects/{$project->slug}", array_merge($payload, ['title' => 'Safe Water Initiative — Phase 2', 'slug' => $project->slug]))
            ->assertRedirect();
        $this->assertSame('Safe Water Initiative — Phase 2', $project->fresh()->title);

        $this->actingAs($this->admin)->post("/admin/projects/{$project->slug}/publish");
        $this->assertFalse($project->fresh()->is_published);

        $this->actingAs($this->admin)->delete("/admin/projects/{$project->slug}")->assertRedirect();
        $this->assertSoftDeleted('projects', ['id' => $project->id]);

        $this->actingAs($this->admin)->post("/admin/projects/{$project->id}/restore");
        $this->assertNull($project->fresh()->deleted_at);
    }

    public function test_an_unpublished_project_is_hidden_from_the_public_site(): void
    {
        $project = Project::factory()->unpublished()->create(['title' => 'Hidden Project Zeta']);

        $this->get('/projects')->assertOk()->assertDontSee('Hidden Project Zeta');
        $this->get("/projects/{$project->slug}")->assertNotFound();
    }

    // ---------------- Events ----------------

    public function test_an_administrator_can_manage_events(): void
    {
        $payload = [
            'title' => 'Volunteer Orientation — Spring Intake',
            'description' => str_repeat('What the orientation covers and what to bring along. ', 4),
            'event_date' => now()->addWeeks(2)->toDateString(),
            'start_time' => '09:30',
            'end_time' => '14:00',
            'location' => 'Seminar Room, KUET',
            'organizer' => 'KUET TRY Volunteer Desk',
            'status' => Event::STATUS_PUBLISHED,
            'capacity' => 120,
        ];

        $this->actingAs($this->admin)->post('/admin/events', $payload)->assertRedirect();
        $event = Event::query()->firstOrFail();

        $this->get('/events')->assertOk()->assertSee('Volunteer Orientation — Spring Intake', false);

        $this->actingAs($this->admin)
            ->put("/admin/events/{$event->slug}", array_merge($payload, ['slug' => $event->slug, 'capacity' => 200]))
            ->assertRedirect();
        $this->assertSame(200, $event->fresh()->capacity);

        $this->actingAs($this->admin)->delete("/admin/events/{$event->slug}");
        $this->assertSoftDeleted('events', ['id' => $event->id]);
    }

    public function test_an_event_end_time_must_be_after_the_start_time(): void
    {
        $this->actingAs($this->admin)->post('/admin/events', [
            'title' => 'Badly Timed Event',
            'description' => str_repeat('Description long enough to pass validation. ', 3),
            'event_date' => now()->addWeek()->toDateString(),
            'start_time' => '15:00',
            'end_time' => '09:00',
            'status' => Event::STATUS_PUBLISHED,
        ])->assertSessionHasErrors('end_time');
    }

    // ---------------- Success stories ----------------

    public function test_an_administrator_can_manage_success_stories(): void
    {
        $payload = [
            'title' => 'A Sewing Machine and a Second Start',
            'beneficiary_name' => 'Rehana Begum',
            'beneficiary_description' => 'Widow and mother of three from Koyra.',
            'story' => str_repeat('<p>How the support changed things for the family.</p>', 4),
            'location' => 'Koyra, Khulna',
            'story_date' => now()->subMonth()->toDateString(),
            'is_published' => true,
        ];

        $this->actingAs($this->admin)->post('/admin/stories', $payload)->assertRedirect();

        $story = SuccessStory::query()->firstOrFail();
        $this->get('/stories')->assertOk()->assertSee('A Sewing Machine and a Second Start');

        $this->actingAs($this->admin)->delete("/admin/stories/{$story->slug}");
        $this->assertSoftDeleted('success_stories', ['id' => $story->id]);
    }

    public function test_a_story_cannot_be_dated_in_the_future(): void
    {
        $this->actingAs($this->admin)->post('/admin/stories', [
            'title' => 'A Story From Next Year',
            'story' => str_repeat('<p>Content that is long enough to pass validation.</p>', 3),
            'story_date' => now()->addYear()->toDateString(),
        ])->assertSessionHasErrors('story_date');
    }

    // ---------------- Announcements ----------------

    public function test_an_administrator_can_manage_announcements(): void
    {
        $payload = [
            'title' => 'Emergency appeal is now live',
            'content' => 'Our coastal response teams are deployed and the appeal needs support.',
            'priority' => 'urgent',
            'status' => 'published',
            'published_at' => now()->format('Y-m-d\TH:i'),
        ];

        $this->actingAs($this->admin)->post('/admin/announcements', $payload)->assertRedirect();

        $announcement = Announcement::query()->firstOrFail();
        $this->assertTrue($announcement->is_urgent);

        $this->get('/')->assertOk()->assertSee('Emergency appeal is now live');

        $this->actingAs($this->admin)->delete("/admin/announcements/{$announcement->id}");
        $this->assertDatabaseMissing('announcements', ['id' => $announcement->id]);
    }

    public function test_an_expired_announcement_is_not_shown(): void
    {
        Announcement::factory()->create([
            'title' => 'Expired Notice Delta',
            'status' => Announcement::STATUS_PUBLISHED,
            'published_at' => now()->subMonths(2),
            'expires_at' => now()->subMonth(),
        ]);

        $this->get('/')->assertOk()->assertDontSee('Expired Notice Delta');
    }

    // ---------------- Gallery ----------------

    public function test_an_administrator_can_manage_gallery_images(): void
    {
        $this->actingAs($this->admin)->post('/admin/gallery', [
            'title' => 'Family kits reaching Maheshwaripur',
            'caption' => 'The first convoy unloading dry food and tarpaulins.',
            'category' => 'relief',
            'taken_on' => now()->subWeek()->toDateString(),
            'is_published' => true,
            'image' => $this->pngUpload('convoy.png'),
        ])->assertRedirect();

        $item = GalleryItem::query()->firstOrFail();

        $this->assertStringStartsWith('gallery/', $item->image);
        Storage::disk('public')->assertExists($item->image);

        // Deleting removes the stored file too.
        $path = $item->image;
        $this->actingAs($this->admin)->delete("/admin/gallery/{$item->id}");

        $this->assertDatabaseMissing('gallery_items', ['id' => $item->id]);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_a_gallery_image_is_required_when_creating(): void
    {
        $this->actingAs($this->admin)->post('/admin/gallery', [
            'title' => 'No image attached',
            'category' => 'relief',
        ])->assertSessionHasErrors('image');
    }

    // ---------------- Team ----------------

    public function test_an_administrator_can_manage_team_members(): void
    {
        $this->actingAs($this->admin)->post('/admin/team', [
            'name' => 'Sadia Noor',
            'position' => 'Volunteer Coordinator',
            'biography' => 'Runs the orientation programme.',
            'email' => 'volunteers@kuettry.org',
            'sort_order' => 3,
            'is_active' => true,
        ])->assertRedirect();

        $member = TeamMember::query()->firstOrFail();

        $this->get('/team')->assertOk()->assertSee('Sadia Noor');

        $this->actingAs($this->admin)->put("/admin/team/{$member->id}", [
            'name' => 'Sadia Noor',
            'position' => 'Head of Volunteering',
            'is_active' => false,
        ])->assertRedirect();

        $this->assertSame('Head of Volunteering', $member->fresh()->position);
        $this->get('/team')->assertDontSee('Head of Volunteering');
    }

    public function test_a_team_member_social_link_must_be_a_valid_url(): void
    {
        $this->actingAs($this->admin)->post('/admin/team', [
            'name' => 'Test Person',
            'position' => 'Tester',
            'facebook_url' => 'not-a-url',
        ])->assertSessionHasErrors('facebook_url');
    }
}
