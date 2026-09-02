<?php

namespace Tests\Feature;

use App\Models\ContactMessage;
use App\Models\User;
use App\Services\WeatherService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ContactAndApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, mixed>
     */
    protected function messagePayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Kamrul Islam',
            'email' => 'kamrul@example.com',
            'phone' => '+880 1712-556677',
            'subject' => 'Corporate sponsorship',
            'message' => 'We have a CSR budget for winter relief and would like to discuss sponsoring part of it.',
        ], $overrides);
    }

    // ---------------- Contact form ----------------

    public function test_a_visitor_can_send_a_contact_message(): void
    {
        $this->post('/contact', $this->messagePayload())
            ->assertRedirect(route('contact.create'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('contact_messages', [
            'email' => 'kamrul@example.com',
            'subject' => 'Corporate sponsorship',
            'is_read' => false,
        ]);
    }

    public function test_contact_validation_rejects_bad_input(): void
    {
        $cases = [
            ['name' => '', 'field' => 'name'],
            ['email' => 'nope', 'field' => 'email'],
            ['subject' => '', 'field' => 'subject'],
            ['message' => 'too short', 'field' => 'message'],
        ];

        foreach ($cases as $case) {
            $field = $case['field'];
            unset($case['field']);

            $this->post('/contact', $this->messagePayload($case))->assertSessionHasErrors($field);
        }

        $this->assertDatabaseCount('contact_messages', 0);
    }

    public function test_the_honeypot_field_blocks_automated_submissions(): void
    {
        $this->post('/contact', $this->messagePayload(['website' => 'http://spam.example']))
            ->assertSessionHasErrors('website');

        $this->assertDatabaseCount('contact_messages', 0);
    }

    public function test_contact_messages_are_never_shown_publicly(): void
    {
        $message = ContactMessage::factory()->create(['subject' => 'Strictly Private Subject']);

        $this->get('/')->assertDontSee($message->subject);
        $this->get('/contact')->assertDontSee($message->subject);
        $this->get('/admin/messages')->assertRedirect(route('login'));

        $this->actingAs(User::factory()->create())->get('/admin/messages')->assertForbidden();
    }

    public function test_an_administrator_can_read_and_manage_messages(): void
    {
        $admin = User::factory()->admin()->create();
        $message = ContactMessage::factory()->create();

        $this->actingAs($admin)->get("/admin/messages/{$message->id}")->assertOk();

        // Opening it marks it read.
        $this->assertTrue($message->fresh()->is_read);

        $this->actingAs($admin)->post("/admin/messages/{$message->id}/toggle-read");
        $this->assertFalse($message->fresh()->is_read);

        $this->actingAs($admin)->delete("/admin/messages/{$message->id}")->assertRedirect();
        $this->assertDatabaseMissing('contact_messages', ['id' => $message->id]);
    }

    // ---------------- Weather API ----------------

    public function test_the_weather_service_returns_a_transformed_payload(): void
    {
        config(['apis.weather.enabled' => true]);
        Cache::flush();

        Http::fake([
            '*' => Http::response([
                'current' => [
                    'temperature_2m' => 29.4,
                    'relative_humidity_2m' => 71,
                    'apparent_temperature' => 33.1,
                    'precipitation' => 0,
                    'weather_code' => 3,
                    'wind_speed_10m' => 12.6,
                ],
                'daily' => [
                    'time' => ['2026-09-01', '2026-09-02', '2026-09-03'],
                    'weather_code' => [3, 61, 0],
                    'temperature_2m_max' => [31, 30, 33],
                    'temperature_2m_min' => [25, 24, 26],
                ],
            ]),
        ]);

        $weather = app(WeatherService::class)->current();

        $this->assertSame(29.0, $weather['temperature']);
        $this->assertSame('Overcast', $weather['condition']);
        $this->assertSame('cloud', $weather['icon']);
        $this->assertSame(71, $weather['humidity']);
        $this->assertCount(2, $weather['daily']);
    }

    public function test_the_weather_response_is_cached_so_page_views_do_not_hammer_the_provider(): void
    {
        config(['apis.weather.enabled' => true]);
        Cache::flush();

        Http::fake(['*' => Http::response(['current' => ['temperature_2m' => 25, 'weather_code' => 0]])]);

        $service = app(WeatherService::class);
        $service->current();
        $service->current();
        $service->current();

        Http::assertSentCount(1);
    }

    public function test_a_failing_weather_provider_degrades_gracefully(): void
    {
        config(['apis.weather.enabled' => true]);
        Cache::flush();

        Http::fake(['*' => Http::response('Service Unavailable', 503)]);

        $this->assertNull(app(WeatherService::class)->current());

        // The homepage must still render, with the fallback panel.
        $this->get('/')
            ->assertOk()
            ->assertSee('Weather data temporarily unavailable');
    }

    public function test_a_weather_connection_exception_never_reaches_the_visitor(): void
    {
        config(['apis.weather.enabled' => true]);
        Cache::flush();

        Http::fake(fn () => throw new ConnectionException('cURL error 28: timeout'));

        $this->assertNull(app(WeatherService::class)->current());

        $response = $this->get('/');
        $response->assertOk();
        $response->assertDontSee('cURL error');
    }

    public function test_the_weather_endpoint_returns_a_safe_json_envelope(): void
    {
        config(['apis.weather.enabled' => true]);
        Cache::flush();

        Http::fake(['*' => Http::response('nope', 500)]);

        $this->getJson('/api/weather')
            ->assertOk()
            ->assertJson(['ok' => false])
            ->assertJsonMissingPath('data');
    }
}
