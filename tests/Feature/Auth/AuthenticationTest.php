<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_login_screen_can_be_rendered(): void
    {
        $this->get('/login')->assertOk()->assertSee('Welcome back');
    }

    public function test_a_user_can_sign_in_with_valid_credentials(): void
    {
        $user = User::factory()->create(['password' => Hash::make('Password@123')]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'Password@123',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('dashboard'));
    }

    public function test_an_administrator_lands_on_the_admin_dashboard(): void
    {
        $admin = User::factory()->admin()->create(['password' => Hash::make('Password@123')]);

        $this->post('/login', ['email' => $admin->email, 'password' => 'Password@123'])
            ->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($admin);
    }

    public function test_a_user_cannot_sign_in_with_a_wrong_password(): void
    {
        $user = User::factory()->create(['password' => Hash::make('Password@123')]);

        $this->from('/login')
            ->post('/login', ['email' => $user->email, 'password' => 'wrong-password'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_a_deactivated_account_cannot_sign_in(): void
    {
        $user = User::factory()->inactive()->create(['password' => Hash::make('Password@123')]);

        $this->post('/login', ['email' => $user->email, 'password' => 'Password@123'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    /**
     * After five failed attempts the account is locked out, so even the correct
     * password no longer signs the user in.
     *
     * Two layers can block the request — the failure counter in LoginRequest
     * (a validation error) and the per-IP flood guard on the route (HTTP 429).
     * The security property is that authentication does not happen, so that is
     * what is asserted rather than one particular mechanism.
     */
    public function test_login_is_locked_out_after_five_failed_attempts(): void
    {
        $user = User::factory()->create(['password' => Hash::make('Password@123')]);

        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', ['email' => $user->email, 'password' => 'wrong-password']);
        }

        $response = $this->post('/login', ['email' => $user->email, 'password' => 'Password@123']);

        // A locked-out account must not be authenticated even by a correct password.
        $this->assertGuest();

        $blockedByThrottleKey = $response->status() === 429;
        $blockedByValidation = $response->getSession()->hasOldInput() || $response->getSession()->has('errors');

        $this->assertTrue(
            $blockedByThrottleKey || $blockedByValidation,
            'The sixth attempt should have been rejected, but the response looked successful.'
        );
    }

    public function test_the_last_login_timestamp_is_recorded(): void
    {
        $user = User::factory()->create(['password' => Hash::make('Password@123'), 'last_login_at' => null]);

        $this->post('/login', ['email' => $user->email, 'password' => 'Password@123']);

        $this->assertNotNull($user->fresh()->last_login_at);
    }

    public function test_a_user_can_sign_out(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/logout')->assertRedirect(route('home'));

        $this->assertGuest();
    }

    public function test_guests_are_redirected_to_login_from_protected_pages(): void
    {
        $this->get('/dashboard')->assertRedirect(route('login'));
        $this->get('/profile')->assertRedirect(route('login'));
        $this->get('/dashboard/donations')->assertRedirect(route('login'));
    }

    public function test_a_signed_in_user_is_redirected_away_from_guest_pages(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/login')->assertRedirect('/dashboard');
        $this->actingAs($user)->get('/register')->assertRedirect('/dashboard');
    }
}
