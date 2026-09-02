<?php

namespace Tests\Feature;

use App\Models\Campaign;
use App\Models\ContactMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SecurityTest extends TestCase
{
    use RefreshDatabase;

    // ---------------- CSRF ----------------

    /**
     * Laravel's ValidateCsrfToken middleware short-circuits while the app
     * environment is "testing", so this test switches the environment for one
     * request to prove the protection genuinely fires.
     */
    public function test_a_post_without_a_csrf_token_is_rejected(): void
    {
        $this->app['env'] = 'local';

        $this->post('/contact', [
            'name' => 'Attacker',
            'email' => 'attacker@example.com',
            'subject' => 'Cross-site request',
            'message' => 'This should never be stored without a valid token.',
        ])->assertStatus(419);

        $this->assertDatabaseCount('contact_messages', 0);
    }

    public function test_a_post_with_a_valid_csrf_token_is_accepted(): void
    {
        $this->app['env'] = 'local';

        $this->withSession(['_token' => 'a-known-test-token'])->post('/contact', [
            '_token' => 'a-known-test-token',
            'name' => 'Genuine Visitor',
            'email' => 'visitor@example.com',
            'subject' => 'A real enquiry',
            'message' => 'This one carries a valid token and should be stored.',
        ])->assertRedirect();

        $this->assertDatabaseCount('contact_messages', 1);
    }

    public function test_every_public_form_includes_a_csrf_token(): void
    {
        foreach (['/contact', '/login', '/register'] as $url) {
            $this->get($url)->assertOk()->assertSee('name="_token"', false);
        }
    }

    public function test_every_state_changing_route_requires_csrf_protection(): void
    {
        $unprotected = collect(Route::getRoutes())
            ->filter(fn ($route) => array_intersect(['POST', 'PUT', 'PATCH', 'DELETE'], $route->methods()))
            ->reject(fn ($route) => in_array('web', $route->gatherMiddleware(), true))
            ->map(fn ($route) => $route->uri());

        $this->assertTrue(
            $unprotected->isEmpty(),
            'These state-changing routes are outside the web middleware group: '.$unprotected->implode(', ')
        );
    }

    // ---------------- XSS ----------------

    public function test_html_in_stored_content_is_escaped_when_rendered(): void
    {
        $payload = '<script>alert("xss")</script>';

        $message = ContactMessage::factory()->create([
            'name' => $payload,
            'subject' => 'Script test',
        ]);

        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get("/admin/messages/{$message->id}");

        $response->assertOk();
        $response->assertDontSee('<script>alert("xss")</script>', false);
        $response->assertSee('&lt;script&gt;', false);
    }

    public function test_a_users_own_name_cannot_inject_markup_into_a_page(): void
    {
        $user = User::factory()->create(['name' => '<img src=x onerror=alert(1)>']);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertDontSee('<img src=x onerror=alert(1)>', false);
    }

    public function test_a_flash_message_containing_markup_is_not_rendered_as_html(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->withSession(['success' => '<script>alert(1)</script>'])
            ->get('/dashboard');

        $response->assertOk();
        $response->assertDontSee('<script>alert(1)</script>', false);
    }

    // ---------------- SQL injection ----------------

    public function test_search_input_cannot_inject_sql(): void
    {
        Campaign::factory()->active()->count(3)->create();

        $injections = [
            "'; DROP TABLE campaigns; --",
            "' OR '1'='1",
            '1; DELETE FROM users WHERE 1=1; --',
            "%' UNION SELECT password FROM users --",
        ];

        foreach ($injections as $injection) {
            $this->get('/campaigns?q='.urlencode($injection))->assertOk();
            $this->get('/search?q='.urlencode($injection))->assertOk();
        }

        // Everything is still standing.
        $this->assertSame(3, Campaign::query()->count());
        $this->assertTrue(Schema::hasTable('campaigns'));
        $this->assertTrue(Schema::hasTable('users'));
    }

    public function test_filter_parameters_are_validated_against_an_allow_list(): void
    {
        // An unexpected value is rejected by validation rather than reaching the query.
        $this->get('/campaigns?category=;DROP+TABLE+users')->assertSessionHasErrors('category');
        $this->get('/campaigns?status=nonsense')->assertSessionHasErrors('status');
        $this->get('/campaigns?sort=1;--')->assertSessionHasErrors('sort');
    }

    // ---------------- Sensitive data ----------------

    public function test_password_hashes_are_never_serialised(): void
    {
        $user = User::factory()->create();

        $array = $user->toArray();

        $this->assertArrayNotHasKey('password', $array);
        $this->assertArrayNotHasKey('remember_token', $array);
    }

    public function test_the_env_file_is_not_reachable_over_http(): void
    {
        foreach (['/.env', '/.env.example', '/storage/logs/laravel.log', '/composer.json'] as $path) {
            $this->assertNotSame(200, $this->get($path)->getStatusCode(), "{$path} should not be served");
        }
    }

    public function test_admin_and_dashboard_routes_are_excluded_from_search_engines(): void
    {
        $robots = file_get_contents(public_path('robots.txt'));

        $this->assertStringContainsString('Disallow: /admin', $robots);
        $this->assertStringContainsString('Disallow: /dashboard', $robots);
    }

    // ---------------- Session ----------------

    public function test_the_session_id_is_regenerated_on_sign_in(): void
    {
        $user = User::factory()->create(['password' => Hash::make('Password@123')]);

        $this->get('/login');
        $before = session()->getId();

        $this->post('/login', ['email' => $user->email, 'password' => 'Password@123']);

        $this->assertNotSame($before, session()->getId());
    }

    public function test_the_session_is_invalidated_on_sign_out(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/dashboard');
        $before = session()->getId();

        $this->post('/logout');

        $this->assertNotSame($before, session()->getId());
        $this->assertGuest();
    }
}
