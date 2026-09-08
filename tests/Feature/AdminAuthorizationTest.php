<?php

namespace Tests\Feature;

use App\Models\Campaign;
use App\Models\Donation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The admin area is protected server-side by the `admin` middleware and the
 * policies — hiding links in Blade is not a control, so these tests hit the
 * routes directly.
 */
class AdminAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<int, string>
     */
    protected function adminUrls(): array
    {
        return [
            '/admin',
            '/admin/campaigns',
            '/admin/campaigns/create',
            '/admin/donations',
            '/admin/donations/reports',
            '/admin/projects',
            '/admin/events',
            '/admin/stories',
            '/admin/announcements',
            '/admin/gallery',
            '/admin/team',
            '/admin/volunteers',
            '/admin/users',
            '/admin/messages',
            '/admin/activity',
            '/admin/settings',
        ];
    }

    public function test_a_guest_is_redirected_to_login_from_every_admin_page(): void
    {
        foreach ($this->adminUrls() as $url) {
            $this->get($url)->assertRedirect(route('login'));
        }
    }

    public function test_a_normal_user_is_forbidden_from_every_admin_page(): void
    {
        $user = User::factory()->create();

        foreach ($this->adminUrls() as $url) {
            $this->actingAs($user)->get($url)->assertForbidden();
        }
    }

    public function test_an_administrator_can_reach_the_admin_pages(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get('/admin')->assertOk();
        $this->actingAs($admin)->get('/admin/campaigns')->assertOk();
    }

    public function test_a_normal_user_cannot_create_or_change_campaigns(): void
    {
        $user = User::factory()->create();
        $campaign = Campaign::factory()->create();

        $this->actingAs($user)->post('/admin/campaigns', ['title' => 'Hijacked'])->assertForbidden();
        $this->actingAs($user)->put("/admin/campaigns/{$campaign->slug}", ['title' => 'Hijacked'])->assertForbidden();
        $this->actingAs($user)->delete("/admin/campaigns/{$campaign->slug}")->assertForbidden();

        $this->assertDatabaseHas('campaigns', ['id' => $campaign->id, 'title' => $campaign->title]);
        $this->assertNull($campaign->fresh()->deleted_at);
    }

    public function test_a_normal_user_cannot_approve_a_donation(): void
    {
        $user = User::factory()->create();
        $campaign = Campaign::factory()->create();
        $donation = Donation::factory()->pending()->create([
            'campaign_id' => $campaign->id,
            'amount' => 5000,
        ]);

        $this->actingAs($user)
            ->post("/admin/donations/{$donation->id}/review", ['decision' => 'approve'])
            ->assertForbidden();

        $this->assertSame(Donation::STATUS_PENDING, $donation->fresh()->status);
        $this->assertSame(0.0, (float) $campaign->fresh()->raised_amount);
    }

    public function test_a_deactivated_administrator_is_signed_out_of_the_admin_area(): void
    {
        $admin = User::factory()->admin()->inactive()->create();

        $this->actingAs($admin)->get('/admin')->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_a_deactivated_user_is_signed_out_of_the_supporter_area(): void
    {
        $user = User::factory()->inactive()->create();

        $this->actingAs($user)->get('/dashboard')->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_the_only_administrator_cannot_be_deleted_or_deactivated(): void
    {
        $admin = User::factory()->admin()->create();

        // Not even by itself.
        $this->actingAs($admin)->delete("/admin/users/{$admin->id}")->assertForbidden();
        $this->actingAs($admin)->post("/admin/users/{$admin->id}/toggle-active")->assertForbidden();

        $this->assertDatabaseHas('users', ['id' => $admin->id, 'role' => User::ROLE_ADMIN, 'is_active' => true]);
        $this->assertSame(1, User::query()->admins()->count());
    }

    public function test_an_administrator_can_deactivate_and_delete_a_supporter(): void
    {
        $admin = User::factory()->admin()->create();
        $member = User::factory()->create();

        $this->actingAs($admin)->post("/admin/users/{$member->id}/toggle-active")->assertRedirect();
        $this->assertFalse($member->fresh()->is_active);

        $this->actingAs($admin)->delete("/admin/users/{$member->id}")->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseMissing('users', ['id' => $member->id]);
    }

    public function test_deleting_a_supporter_keeps_their_donation_records(): void
    {
        $admin = User::factory()->admin()->create();
        $member = User::factory()->create();
        $donation = Donation::factory()->approved()->create(['user_id' => $member->id]);

        $this->actingAs($admin)->delete("/admin/users/{$member->id}");

        $this->assertDatabaseHas('donations', ['id' => $donation->id]);
        $this->assertNull($donation->fresh()->user_id);
    }
}
