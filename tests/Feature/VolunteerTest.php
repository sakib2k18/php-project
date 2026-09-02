<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Volunteer;
use App\Notifications\VolunteerReviewed;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class VolunteerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->admin = User::factory()->admin()->create();
    }

    /**
     * @return array<string, mixed>
     */
    protected function payload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Tanvir Ahmed',
            'email' => 'tanvir@example.com',
            'phone' => '+880 1711-556677',
            'student_id' => '1807042',
            'institution' => 'Khulna University of Engineering & Technology',
            'address' => 'Hall Road, Khulna',
            'skills' => 'First aid, driving, photography',
            'availability' => 'weekends',
            'preferred_activity' => 'Relief distribution',
            'motivation' => 'I went on one winter distribution run and I have not been able to stop thinking about it.',
        ], $overrides);
    }

    public function test_a_supporter_can_apply_to_volunteer(): void
    {
        $this->actingAs($this->user)->post('/volunteer', $this->payload())->assertRedirect();

        $volunteer = Volunteer::query()->firstOrFail();

        $this->assertSame($this->user->id, $volunteer->user_id);
        $this->assertSame(Volunteer::STATUS_PENDING, $volunteer->status);
    }

    public function test_an_application_is_always_pending_regardless_of_the_payload(): void
    {
        $this->actingAs($this->user)->post('/volunteer', $this->payload([
            'status' => Volunteer::STATUS_APPROVED,
            'reviewed_by' => $this->user->id,
        ]));

        $volunteer = Volunteer::query()->firstOrFail();

        $this->assertSame(Volunteer::STATUS_PENDING, $volunteer->status);
        $this->assertNull($volunteer->reviewed_by);
    }

    public function test_a_supporter_can_only_apply_once(): void
    {
        $this->actingAs($this->user)->post('/volunteer', $this->payload());
        $this->actingAs($this->user)->post('/volunteer', $this->payload())->assertForbidden();

        $this->assertSame(1, Volunteer::query()->count());
    }

    public function test_volunteer_validation_rejects_bad_input(): void
    {
        $cases = [
            ['name' => '', 'field' => 'name'],
            ['email' => 'not-an-email', 'field' => 'email'],
            ['phone' => '', 'field' => 'phone'],
            ['availability' => 'whenever', 'field' => 'availability'],
            ['motivation' => 'Too short.', 'field' => 'motivation'],
            ['skills' => '', 'field' => 'skills'],
        ];

        foreach ($cases as $case) {
            $field = $case['field'];
            unset($case['field']);

            $this->actingAs($this->user)
                ->post('/volunteer', $this->payload($case))
                ->assertSessionHasErrors($field);
        }

        $this->assertDatabaseCount('volunteers', 0);
    }

    public function test_a_guest_cannot_apply(): void
    {
        $this->post('/volunteer', $this->payload())->assertRedirect(route('login'));

        $this->assertDatabaseCount('volunteers', 0);
    }

    public function test_a_supporter_can_correct_a_pending_application(): void
    {
        $volunteer = Volunteer::factory()->create(['user_id' => $this->user->id]);

        $this->actingAs($this->user)
            ->put("/volunteer/{$volunteer->id}", $this->payload(['preferred_activity' => 'Fundraising']))
            ->assertRedirect();

        $this->assertSame('Fundraising', $volunteer->fresh()->preferred_activity);
    }

    public function test_a_supporter_cannot_edit_an_application_once_it_is_reviewed(): void
    {
        $volunteer = Volunteer::factory()->approved()->create(['user_id' => $this->user->id]);

        $this->actingAs($this->user)
            ->put("/volunteer/{$volunteer->id}", $this->payload(['preferred_activity' => 'Fundraising']))
            ->assertForbidden();
    }

    public function test_a_supporter_cannot_edit_someone_elses_application(): void
    {
        $volunteer = Volunteer::factory()->create(['user_id' => User::factory()->create()->id]);

        $this->actingAs($this->user)
            ->put("/volunteer/{$volunteer->id}", $this->payload())
            ->assertForbidden();
    }

    public function test_an_administrator_can_approve_an_application_and_the_applicant_is_notified(): void
    {
        Notification::fake();

        $volunteer = Volunteer::factory()->create(['user_id' => $this->user->id]);

        $this->actingAs($this->admin)
            ->post("/admin/volunteers/{$volunteer->id}/review", ['decision' => 'approve'])
            ->assertRedirect();

        $volunteer->refresh();

        $this->assertSame(Volunteer::STATUS_APPROVED, $volunteer->status);
        $this->assertSame($this->admin->id, $volunteer->reviewed_by);

        Notification::assertSentTo($this->user, VolunteerReviewed::class);
    }

    public function test_a_normal_user_cannot_review_an_application(): void
    {
        $volunteer = Volunteer::factory()->create();

        $this->actingAs($this->user)
            ->post("/admin/volunteers/{$volunteer->id}/review", ['decision' => 'approve'])
            ->assertForbidden();

        $this->assertSame(Volunteer::STATUS_PENDING, $volunteer->fresh()->status);
    }
}
