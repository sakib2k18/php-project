<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, mixed>
     */
    protected function validPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Nusrat Jahan',
            'email' => 'nusrat@example.com',
            'phone' => '+880 1711-223344',
            'student_id' => '1807042',
            'password' => 'Password@123',
            'password_confirmation' => 'Password@123',
            'terms' => 'on',
        ], $overrides);
    }

    public function test_the_registration_screen_can_be_rendered(): void
    {
        $this->get('/register')->assertOk()->assertSee('Create your account');
    }

    public function test_a_supporter_can_register(): void
    {
        $response = $this->post('/register', $this->validPayload());

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();

        $this->assertDatabaseHas('users', [
            'email' => 'nusrat@example.com',
            'role' => User::ROLE_USER,
            'is_active' => true,
        ]);
    }

    public function test_the_password_is_hashed_and_never_stored_in_plain_text(): void
    {
        $this->post('/register', $this->validPayload());

        $user = User::query()->where('email', 'nusrat@example.com')->firstOrFail();

        $this->assertNotSame('Password@123', $user->password);
        $this->assertTrue(Hash::check('Password@123', $user->password));
    }

    /**
     * The core privilege-escalation guard: no request payload can create an
     * administrator, because the controller assigns the role explicitly.
     */
    public function test_the_registration_form_can_never_create_an_administrator(): void
    {
        $this->post('/register', $this->validPayload([
            'role' => 'admin',
            'is_active' => false,
        ]));

        $user = User::query()->where('email', 'nusrat@example.com')->firstOrFail();

        $this->assertSame(User::ROLE_USER, $user->role);
        $this->assertTrue($user->is_active);
        $this->assertFalse($user->isAdmin());
    }

    public function test_mass_assignment_cannot_set_the_role_on_the_model(): void
    {
        $user = User::create([
            'name' => 'Attempted Admin',
            'email' => 'attempt@example.com',
            'password' => 'Password@123',
            'role' => User::ROLE_ADMIN,
        ]);

        $this->assertSame(User::ROLE_USER, $user->fresh()->role);
    }

    public function test_registration_requires_a_unique_email(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);

        $this->post('/register', $this->validPayload(['email' => 'taken@example.com']))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_registration_requires_a_confirmed_password_of_at_least_eight_characters(): void
    {
        $this->post('/register', $this->validPayload([
            'password' => 'short1',
            'password_confirmation' => 'short1',
        ]))->assertSessionHasErrors('password');

        $this->post('/register', $this->validPayload([
            'password' => 'Password@123',
            'password_confirmation' => 'Different@123',
        ]))->assertSessionHasErrors('password');

        $this->assertGuest();
        $this->assertDatabaseCount('users', 0);
    }

    public function test_registration_requires_the_terms_to_be_accepted(): void
    {
        $this->post('/register', $this->validPayload(['terms' => null]))
            ->assertSessionHasErrors('terms');

        $this->assertGuest();
    }

    public function test_there_is_no_public_route_for_creating_an_administrator(): void
    {
        foreach (['/admin/register', '/register/admin', '/admin/users/create'] as $url) {
            $this->assertNotSame(200, $this->get($url)->getStatusCode(), "{$url} should not be reachable");
        }
    }
}
