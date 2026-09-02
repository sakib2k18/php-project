<?php

namespace Tests\Feature;

use App\Models\Campaign;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\MakesTestImages;
use Tests\TestCase;

class CampaignManagementTest extends TestCase
{
    use MakesTestImages, RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
    }

    /**
     * @return array<string, mixed>
     */
    protected function payload(array $overrides = []): array
    {
        return array_merge([
            'title' => 'Emergency Water Supply for Coastal Villages',
            'short_description' => 'Clean drinking water for families whose tube wells have gone saline after the surge.',
            'description' => str_repeat('Detailed description of what this appeal buys and who it reaches. ', 4),
            'category' => 'emergency_relief',
            'target_amount' => 250000,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addMonths(3)->toDateString(),
            'location' => 'Shyamnagar, Satkhira',
            'latitude' => 22.3256,
            'longitude' => 89.1097,
            'status' => Campaign::STATUS_ACTIVE,
            'beneficiaries_count' => 800,
        ], $overrides);
    }

    // ---------------- Create ----------------

    public function test_an_administrator_can_create_a_campaign(): void
    {
        $this->actingAs($this->admin)->post('/admin/campaigns', $this->payload())->assertRedirect();

        $campaign = Campaign::query()->firstOrFail();

        $this->assertSame('Emergency Water Supply for Coastal Villages', $campaign->title);
        $this->assertSame('emergency-water-supply-for-coastal-villages', $campaign->slug);
        $this->assertSame($this->admin->id, $campaign->created_by);
        $this->assertSame(0.0, (float) $campaign->raised_amount);
    }

    public function test_creating_a_campaign_writes_an_activity_log_entry(): void
    {
        $this->actingAs($this->admin)->post('/admin/campaigns', $this->payload());

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $this->admin->id,
            'action' => 'campaign.created',
        ]);
    }

    public function test_campaign_validation_rejects_bad_input(): void
    {
        $cases = [
            ['title' => '', 'field' => 'title'],
            ['target_amount' => 10, 'field' => 'target_amount'],
            ['target_amount' => 'lots', 'field' => 'target_amount'],
            ['category' => 'unknown-category', 'field' => 'category'],
            ['status' => 'nonsense', 'field' => 'status'],
            ['end_date' => now()->subYear()->toDateString(), 'field' => 'end_date'],
            ['latitude' => 22.3, 'longitude' => null, 'field' => 'longitude'],
            ['short_description' => 'tiny', 'field' => 'short_description'],
        ];

        foreach ($cases as $case) {
            $field = $case['field'];
            unset($case['field']);

            $this->actingAs($this->admin)
                ->post('/admin/campaigns', $this->payload($case))
                ->assertSessionHasErrors($field);
        }

        $this->assertDatabaseCount('campaigns', 0);
    }

    public function test_slugs_are_unique(): void
    {
        $this->actingAs($this->admin)->post('/admin/campaigns', $this->payload());
        $this->actingAs($this->admin)->post('/admin/campaigns', $this->payload());

        $slugs = Campaign::query()->pluck('slug');

        $this->assertCount(2, $slugs);
        $this->assertSame($slugs->count(), $slugs->unique()->count());
    }

    // ---------------- Read ----------------

    public function test_only_active_and_completed_campaigns_appear_publicly(): void
    {
        $active = Campaign::factory()->active()->create(['title' => 'Active Appeal Alpha']);
        $completed = Campaign::factory()->completed()->create(['title' => 'Completed Appeal Beta']);
        $draft = Campaign::factory()->draft()->create(['title' => 'Draft Appeal Gamma']);

        $this->get('/campaigns')
            ->assertOk()
            ->assertSee($active->title)
            ->assertSee($completed->title)
            ->assertDontSee($draft->title);

        $this->get("/campaigns/{$draft->slug}")->assertNotFound();
    }

    public function test_an_administrator_can_preview_a_draft_campaign(): void
    {
        $draft = Campaign::factory()->draft()->create();

        $this->actingAs($this->admin)->get("/campaigns/{$draft->slug}")->assertOk();
    }

    public function test_the_public_index_can_be_searched_and_filtered(): void
    {
        Campaign::factory()->active()->create(['title' => 'Winter Blankets Drive', 'category' => 'winter_support']);
        Campaign::factory()->active()->create(['title' => 'School Books Appeal', 'category' => 'education']);

        $this->get('/campaigns?q=Blankets')
            ->assertOk()
            ->assertSee('Winter Blankets Drive')
            ->assertDontSee('School Books Appeal');

        $this->get('/campaigns?category=education')
            ->assertOk()
            ->assertSee('School Books Appeal')
            ->assertDontSee('Winter Blankets Drive');
    }

    // ---------------- Update ----------------

    public function test_an_administrator_can_update_a_campaign(): void
    {
        $campaign = Campaign::factory()->create();

        $this->actingAs($this->admin)
            ->put("/admin/campaigns/{$campaign->slug}", $this->payload([
                'title' => 'Renamed Appeal',
                'slug' => $campaign->slug,
            ]))
            ->assertRedirect();

        $this->assertSame('Renamed Appeal', $campaign->fresh()->title);
    }

    /**
     * raised_amount is not fillable, so it cannot be moved by a crafted form
     * post — only DonationService may change it.
     */
    public function test_the_raised_amount_cannot_be_set_through_the_form(): void
    {
        $campaign = Campaign::factory()->create();
        $campaign->forceFill(['raised_amount' => 1000])->save();

        $this->actingAs($this->admin)->put("/admin/campaigns/{$campaign->slug}", $this->payload([
            'slug' => $campaign->slug,
            'raised_amount' => 999999,
        ]));

        $this->assertSame(1000.0, (float) $campaign->fresh()->raised_amount);
    }

    public function test_an_administrator_can_toggle_the_featured_flag(): void
    {
        $campaign = Campaign::factory()->create(['featured' => false]);

        $this->actingAs($this->admin)->post("/admin/campaigns/{$campaign->slug}/featured");

        $this->assertTrue($campaign->fresh()->featured);
    }

    // ---------------- Delete / restore ----------------

    public function test_deleting_a_campaign_is_a_soft_delete_and_can_be_restored(): void
    {
        $campaign = Campaign::factory()->create();

        $this->actingAs($this->admin)->delete("/admin/campaigns/{$campaign->slug}")->assertRedirect();

        $this->assertSoftDeleted('campaigns', ['id' => $campaign->id]);
        $this->get("/campaigns/{$campaign->slug}")->assertNotFound();

        $this->actingAs($this->admin)->post("/admin/campaigns/{$campaign->id}/restore")->assertRedirect();

        $this->assertNull($campaign->fresh()->deleted_at);
    }

    // ---------------- Uploads ----------------

    public function test_a_valid_image_upload_is_stored_under_a_generated_name(): void
    {
        Storage::fake('public');

        $this->actingAs($this->admin)->post('/admin/campaigns', $this->payload([
            'cover_image' => $this->pngUpload('photo.png'),
        ]));

        $campaign = Campaign::query()->firstOrFail();

        $this->assertNotNull($campaign->cover_image);
        $this->assertStringStartsWith('campaigns/', $campaign->cover_image);
        // The original filename is never trusted.
        $this->assertStringNotContainsString('photo', $campaign->cover_image);
        Storage::disk('public')->assertExists($campaign->cover_image);
    }

    /**
     * A file named "*.jpg" whose bytes are PHP must not be accepted: the rules
     * check the detected MIME type, not the extension the client sent.
     */
    public function test_a_disguised_php_file_is_rejected_even_with_an_image_extension(): void
    {
        Storage::fake('public');

        $disguised = $this->uploadFromBytes('<?php echo "pwned";', 'invoice.jpg', 'image/jpeg');

        $this->actingAs($this->admin)->post('/admin/campaigns', $this->payload([
            'cover_image' => $disguised,
        ]))->assertSessionHasErrors('cover_image');

        $this->assertDatabaseCount('campaigns', 0);
    }

    public function test_a_non_image_upload_is_rejected(): void
    {
        Storage::fake('public');

        $this->actingAs($this->admin)->post('/admin/campaigns', $this->payload([
            'cover_image' => UploadedFile::fake()->create('payload.php', 40, 'application/x-php'),
        ]))->assertSessionHasErrors('cover_image');

        $this->assertDatabaseCount('campaigns', 0);
    }

    public function test_an_oversized_image_is_rejected(): void
    {
        Storage::fake('public');

        $maxKb = (int) config('site.uploads.max_kb');

        $this->actingAs($this->admin)->post('/admin/campaigns', $this->payload([
            'cover_image' => UploadedFile::fake()->create('huge.jpg', $maxKb + 500, 'image/jpeg'),
        ]))->assertSessionHasErrors('cover_image');

        $this->assertDatabaseCount('campaigns', 0);
    }

    public function test_an_image_with_excessive_dimensions_is_rejected(): void
    {
        // Generating an over-sized image needs GD, which a stock XAMPP CLI
        // does not enable. The `dimensions` rule itself is exercised in
        // production by getimagesize(), which does not need GD.
        if (! function_exists('imagecreatetruecolor')) {
            $this->markTestSkipped('The GD extension is required to generate an oversized test image.');
        }

        Storage::fake('public');

        $max = (int) config('site.uploads.max_dimension');

        $this->actingAs($this->admin)->post('/admin/campaigns', $this->payload([
            'cover_image' => UploadedFile::fake()->image('massive.jpg', $max + 500, 600),
        ]))->assertSessionHasErrors('cover_image');
    }

    public function test_a_campaign_can_be_created_without_an_image(): void
    {
        Storage::fake('public');

        $this->actingAs($this->admin)->post('/admin/campaigns', $this->payload())->assertRedirect();

        $this->assertNull(Campaign::query()->firstOrFail()->cover_image);
    }
}
