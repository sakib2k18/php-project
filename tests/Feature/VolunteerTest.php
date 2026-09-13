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
            'preferred_activity' => ['Relief distribution'],
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
            ['preferred_activity' => [], 'field' => 'preferred_activity'],
            ['preferred_activity' => ['Not a real activity'], 'field' => 'preferred_activity.0'],
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
            ->put("/volunteer/{$volunteer->id}", $this->payload(['preferred_activity' => ['Fundraising']]))
            ->assertRedirect();

        $this->assertSame(['Fundraising'], $volunteer->fresh()->preferred_activity);
    }

    public function test_skills_and_motivation_are_optional(): void
    {
        $this->actingAs($this->user)
            ->post('/volunteer', $this->payload([
                'skills' => null,
                'motivation' => null,
            ]))
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $volunteer = Volunteer::query()->firstOrFail();

        $this->assertNull($volunteer->skills);
        $this->assertNull($volunteer->motivation);
    }

    public function test_a_supporter_may_pick_several_activities(): void
    {
        $activities = ['Fundraising', 'Teaching & tutoring', 'Event management'];

        $this->actingAs($this->user)
            ->post('/volunteer', $this->payload(['preferred_activity' => $activities]))
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $volunteer = Volunteer::query()->firstOrFail();

        $this->assertSame($activities, $volunteer->preferred_activity);
        $this->assertSame('Fundraising, Teaching & tutoring, Event management', $volunteer->preferred_activity_label);
    }

    public function test_the_activity_field_is_a_checkbox_dropdown_over_a_real_select(): void
    {
        $this->actingAs($this->user)
            ->get('/volunteer')
            ->assertOk()
            // The native control posts the value, so the field still works
            // without JavaScript.
            ->assertSee('name="preferred_activity[]"', false)
            ->assertSee('data-multiselect-native', false)
            // …and the dropdown that takes over once JavaScript runs.
            ->assertSee('data-multiselect-toggle', false)
            ->assertSee('data-multiselect-panel', false)
            ->assertSee('Choose activities…');
    }

    public function test_the_dropdown_pre_selects_every_chosen_activity(): void
    {
        Volunteer::factory()->create([
            'user_id' => $this->user->id,
            'preferred_activity' => ['Fundraising', 'Event management'],
        ]);

        $this->actingAs($this->user)
            ->get('/volunteer')
            ->assertOk()
            // The option the form actually posts…
            ->assertSee('value="Fundraising" selected>', false)
            ->assertSee('value="Event management" selected>', false)
            // …its checkbox…
            ->assertSee('value="Fundraising" class="checkbox" checked>', false)
            // …the closed button's summary, and the value `required` reads.
            ->assertSee('Fundraising, Event management')
            ->assertSee('value="Fundraising,Event management"', false)
            // Nothing else is ticked.
            ->assertSee('value="Administration" class="checkbox" >', false);
    }

    public function test_the_read_only_view_renders_a_volunteer_whose_optional_fields_are_empty(): void
    {
        Volunteer::factory()->create([
            'user_id' => $this->user->id,
            'skills' => null,
            'motivation' => null,
            'preferred_activity' => ['Fundraising'],
        ]);

        $this->actingAs($this->user)->get('/volunteer')->assertOk()->assertSee('Fundraising');
    }

    /** Every admin surface that prints an activity, against a multi-value row. */
    public function test_the_admin_views_render_a_volunteer_with_several_activities(): void
    {
        $volunteer = Volunteer::factory()->create([
            'preferred_activity' => ['Fundraising', 'Event management'],
            'skills' => null,
            'motivation' => null,
        ]);

        $this->actingAs($this->admin)
            ->get("/admin/volunteers/{$volunteer->id}")
            ->assertOk()
            ->assertSee('Fundraising, Event management');

        $this->actingAs($this->admin)->get('/admin/volunteers')->assertOk();
        $this->actingAs($this->admin)->get('/admin')->assertOk();
        $this->actingAs($this->admin)->get("/admin/users/{$volunteer->user_id}")->assertOk();
    }

    public function test_a_supporter_cannot_edit_an_application_once_it_is_reviewed(): void
    {
        $volunteer = Volunteer::factory()->approved()->create(['user_id' => $this->user->id]);

        $this->actingAs($this->user)
            ->put("/volunteer/{$volunteer->id}", $this->payload(['preferred_activity' => ['Fundraising']]))
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
