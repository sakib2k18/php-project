<?php

namespace Tests\Feature;

use App\Models\Campaign;
use App\Models\Donation;
use App\Models\User;
use App\Notifications\DonationReviewed;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class DonationTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected User $admin;

    protected Campaign $campaign;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->admin = User::factory()->admin()->create();
        $this->campaign = Campaign::factory()->active()->create([
            'target_amount' => 100000,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function payload(array $overrides = []): array
    {
        return array_merge([
            'campaign_id' => $this->campaign->id,
            'donor_name' => 'Nusrat Jahan',
            'donor_email' => 'nusrat@example.com',
            'donor_phone' => '+880 1711-223344',
            'amount' => 5000,
            'method' => 'bank_transfer',
            'transaction_reference' => 'ABC123XYZ',
            'donated_on' => now()->toDateString(),
            'message' => 'Please use it where it is needed most.',
        ], $overrides);
    }

    // -----------------------------------------------------------------
    // Creating
    // -----------------------------------------------------------------

    public function test_a_signed_in_supporter_can_record_a_donation(): void
    {
        $this->actingAs($this->user)->post('/donate', $this->payload())->assertRedirect();

        $donation = Donation::query()->firstOrFail();

        $this->assertSame($this->user->id, $donation->user_id);
        $this->assertSame(5000.0, (float) $donation->amount);
        $this->assertStringStartsWith('KT-', $donation->reference);
    }

    public function test_a_new_donation_is_always_pending_regardless_of_the_payload(): void
    {
        $this->actingAs($this->user)->post('/donate', $this->payload([
            'status' => Donation::STATUS_APPROVED,
            'counted_in_campaign' => true,
            'reviewed_by' => $this->user->id,
        ]));

        $donation = Donation::query()->firstOrFail();

        $this->assertSame(Donation::STATUS_PENDING, $donation->status);
        $this->assertFalse($donation->counted_in_campaign);
        $this->assertNull($donation->reviewed_by);
    }

    public function test_a_pending_donation_does_not_change_the_campaign_total(): void
    {
        $this->actingAs($this->user)->post('/donate', $this->payload());

        $this->assertSame(0.0, (float) $this->campaign->fresh()->raised_amount);
    }

    public function test_a_guest_cannot_record_a_donation(): void
    {
        $this->post('/donate', $this->payload())->assertRedirect(route('login'));

        $this->assertDatabaseCount('donations', 0);
    }

    public function test_a_donation_cannot_be_recorded_against_a_draft_campaign(): void
    {
        $draft = Campaign::factory()->draft()->create();

        $this->actingAs($this->user)
            ->post('/donate', $this->payload(['campaign_id' => $draft->id]))
            ->assertSessionHasErrors('campaign_id');

        $this->assertDatabaseCount('donations', 0);
    }

    public function test_donation_validation_rejects_bad_input(): void
    {
        $cases = [
            ['amount' => 10, 'field' => 'amount'],                         // below the minimum
            ['amount' => 'not-a-number', 'field' => 'amount'],
            ['donor_email' => 'not-an-email', 'field' => 'donor_email'],
            ['method' => 'crypto', 'field' => 'method'],
            ['donated_on' => now()->addWeek()->toDateString(), 'field' => 'donated_on'],
            ['donor_name' => 'A', 'field' => 'donor_name'],
        ];

        foreach ($cases as $case) {
            $field = $case['field'];
            unset($case['field']);

            $this->actingAs($this->user)
                ->post('/donate', $this->payload($case))
                ->assertSessionHasErrors($field);
        }

        $this->assertDatabaseCount('donations', 0);
    }

    // -----------------------------------------------------------------
    // Approval workflow
    // -----------------------------------------------------------------

    public function test_approving_a_donation_adds_it_to_the_campaign_total(): void
    {
        Notification::fake();

        $donation = Donation::factory()->pending()->create([
            'user_id' => $this->user->id,
            'campaign_id' => $this->campaign->id,
            'amount' => 7500,
        ]);

        $this->actingAs($this->admin)
            ->post("/admin/donations/{$donation->id}/review", ['decision' => 'approve'])
            ->assertRedirect();

        $donation->refresh();

        $this->assertSame(Donation::STATUS_APPROVED, $donation->status);
        $this->assertTrue($donation->counted_in_campaign);
        $this->assertSame($this->admin->id, $donation->reviewed_by);
        $this->assertSame(7500.0, (float) $this->campaign->fresh()->raised_amount);

        Notification::assertSentTo($this->user, DonationReviewed::class);
    }

    public function test_approving_the_same_donation_twice_does_not_double_count_it(): void
    {
        Notification::fake();

        $donation = Donation::factory()->pending()->create([
            'campaign_id' => $this->campaign->id,
            'amount' => 3000,
        ]);

        $this->actingAs($this->admin)->post("/admin/donations/{$donation->id}/review", ['decision' => 'approve']);
        $this->actingAs($this->admin)->post("/admin/donations/{$donation->id}/review", ['decision' => 'approve']);

        $this->assertSame(3000.0, (float) $this->campaign->fresh()->raised_amount);
    }

    public function test_rejecting_an_approved_donation_reverses_the_campaign_credit(): void
    {
        Notification::fake();

        $donation = Donation::factory()->pending()->create([
            'campaign_id' => $this->campaign->id,
            'amount' => 4000,
        ]);

        $this->actingAs($this->admin)->post("/admin/donations/{$donation->id}/review", ['decision' => 'approve']);
        $this->assertSame(4000.0, (float) $this->campaign->fresh()->raised_amount);

        $this->actingAs($this->admin)->post("/admin/donations/{$donation->id}/review", [
            'decision' => 'reject',
            'admin_note' => 'Reference could not be matched.',
        ]);

        $donation->refresh();

        $this->assertSame(Donation::STATUS_REJECTED, $donation->status);
        $this->assertFalse($donation->counted_in_campaign);
        $this->assertSame(0.0, (float) $this->campaign->fresh()->raised_amount);
    }

    public function test_deleting_an_approved_donation_reverses_the_campaign_credit(): void
    {
        Notification::fake();

        $donation = Donation::factory()->pending()->create([
            'campaign_id' => $this->campaign->id,
            'amount' => 2500,
        ]);

        $this->actingAs($this->admin)->post("/admin/donations/{$donation->id}/review", ['decision' => 'approve']);
        $this->actingAs($this->admin)->delete("/admin/donations/{$donation->id}");

        $this->assertDatabaseMissing('donations', ['id' => $donation->id]);
        $this->assertSame(0.0, (float) $this->campaign->fresh()->raised_amount);
    }

    public function test_a_fully_funded_campaign_is_marked_completed_automatically(): void
    {
        Notification::fake();

        $donation = Donation::factory()->pending()->create([
            'campaign_id' => $this->campaign->id,
            'amount' => 100000,
        ]);

        $this->actingAs($this->admin)->post("/admin/donations/{$donation->id}/review", ['decision' => 'approve']);

        $this->assertSame(Campaign::STATUS_COMPLETED, $this->campaign->fresh()->status);
    }

    public function test_the_progress_percentage_never_exceeds_one_hundred(): void
    {
        Notification::fake();

        $donation = Donation::factory()->pending()->create([
            'campaign_id' => $this->campaign->id,
            'amount' => 250000, // 250% of the target
        ]);

        $this->actingAs($this->admin)->post("/admin/donations/{$donation->id}/review", ['decision' => 'approve']);

        $campaign = $this->campaign->fresh();

        $this->assertSame(100.0, $campaign->progress_percent);
        $this->assertSame(250.0, $campaign->raw_progress_percent);
    }

    public function test_recalculating_totals_rebuilds_them_from_the_approved_records(): void
    {
        Donation::factory()->approved()->count(3)->create([
            'campaign_id' => $this->campaign->id,
            'amount' => 1000,
        ]);
        Donation::factory()->pending()->create(['campaign_id' => $this->campaign->id, 'amount' => 9999]);

        // Simulate drift.
        $this->campaign->forceFill(['raised_amount' => 123456])->save();

        $this->actingAs($this->admin)->post('/admin/donations/recalculate')->assertRedirect();

        $this->assertSame(3000.0, (float) $this->campaign->fresh()->raised_amount);
    }

    // -----------------------------------------------------------------
    // Privacy
    // -----------------------------------------------------------------

    public function test_a_supporter_only_sees_their_own_donations_in_the_list(): void
    {
        $mine = Donation::factory()->approved()->create([
            'user_id' => $this->user->id,
            'reference' => 'KT-2026-MINE01',
        ]);

        $theirs = Donation::factory()->approved()->create([
            'user_id' => User::factory()->create()->id,
            'reference' => 'KT-2026-THEIR1',
        ]);

        $this->actingAs($this->user)
            ->get('/dashboard/donations')
            ->assertOk()
            ->assertSee($mine->reference)
            ->assertDontSee($theirs->reference);
    }

    public function test_a_supporter_cannot_open_another_users_donation(): void
    {
        $theirs = Donation::factory()->approved()->create([
            'user_id' => User::factory()->create()->id,
        ]);

        $this->actingAs($this->user)->get("/dashboard/donations/{$theirs->id}")->assertForbidden();
        $this->actingAs($this->user)->get("/dashboard/donations/{$theirs->id}/receipt")->assertForbidden();
    }

    public function test_a_receipt_is_only_available_once_a_donation_is_approved(): void
    {
        $pending = Donation::factory()->pending()->create(['user_id' => $this->user->id]);
        $approved = Donation::factory()->approved()->create(['user_id' => $this->user->id]);

        $this->actingAs($this->user)->get("/dashboard/donations/{$pending->id}/receipt")->assertForbidden();

        $this->actingAs($this->user)
            ->get("/dashboard/donations/{$approved->id}/receipt")
            ->assertOk()
            ->assertSee($approved->reference)
            ->assertSee('Donation receipt');
    }

    public function test_an_anonymous_donor_is_not_named_on_the_public_campaign_page(): void
    {
        $donation = Donation::factory()->approved()->create([
            'campaign_id' => $this->campaign->id,
            'donor_name' => 'Very Private Person',
            'is_anonymous' => true,
        ]);

        $this->get("/campaigns/{$this->campaign->slug}")
            ->assertOk()
            ->assertDontSee('Very Private Person')
            ->assertSee('Anonymous Donor');
    }
}
