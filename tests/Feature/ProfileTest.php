<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\MakesTestImages;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use MakesTestImages, RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'name' => 'Nusrat Jahan',
            'password' => Hash::make('Password@123'),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function payload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Nusrat J. Rahman',
            'email' => 'nusrat.updated@example.com',
            'phone' => '+880 1711-999888',
            'student_id' => '1807042',
            'address' => 'Hall Road, Khulna 9100',
            'bio' => 'Regular donor and weekend volunteer.',
        ], $overrides);
    }

    public function test_a_user_can_update_their_own_profile(): void
    {
        $this->actingAs($this->user)->patch('/profile', $this->payload())->assertRedirect(route('profile.edit'));

        $user = $this->user->fresh();

        $this->assertSame('Nusrat J. Rahman', $user->name);
        $this->assertSame('nusrat.updated@example.com', $user->email);
    }

    public function test_changing_the_email_clears_the_verification_timestamp(): void
    {
        $this->assertNotNull($this->user->email_verified_at);

        $this->actingAs($this->user)->patch('/profile', $this->payload());

        $this->assertNull($this->user->fresh()->email_verified_at);
    }

    /**
     * `role` and `is_active` are absent from ProfileUpdateRequest and from the
     * model's $fillable, so a crafted post cannot escalate privileges.
     */
    public function test_a_user_cannot_promote_themselves_through_the_profile_form(): void
    {
        $this->actingAs($this->user)->patch('/profile', $this->payload([
            'role' => User::ROLE_ADMIN,
            'is_active' => false,
        ]));

        $user = $this->user->fresh();

        $this->assertSame(User::ROLE_USER, $user->role);
        $this->assertTrue($user->is_active);
    }

    public function test_the_email_must_stay_unique(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);

        $this->actingAs($this->user)
            ->patch('/profile', $this->payload(['email' => 'taken@example.com']))
            ->assertSessionHasErrors('email');
    }

    public function test_a_user_can_keep_their_own_email_unchanged(): void
    {
        $this->actingAs($this->user)
            ->patch('/profile', $this->payload(['email' => $this->user->email]))
            ->assertSessionHasNoErrors();
    }

    public function test_a_user_can_upload_and_remove_a_profile_photo(): void
    {
        Storage::fake('public');

        $this->actingAs($this->user)->patch('/profile', $this->payload([
            'avatar' => $this->pngUpload('me.png'),
        ]));

        $path = $this->user->fresh()->avatar;

        $this->assertNotNull($path);
        $this->assertStringStartsWith('avatars/', $path);
        Storage::disk('public')->assertExists($path);

        $this->actingAs($this->user)->delete('/profile/avatar')->assertRedirect();

        $this->assertNull($this->user->fresh()->avatar);
        Storage::disk('public')->assertMissing($path);
    }

    // ---------------- Password ----------------

    public function test_a_user_can_change_their_password(): void
    {
        $this->actingAs($this->user)->put('/password', [
            'current_password' => 'Password@123',
            'password' => 'NewPassword@456',
            'password_confirmation' => 'NewPassword@456',
        ])->assertRedirect(route('profile.edit'));

        $this->assertTrue(Hash::check('NewPassword@456', $this->user->fresh()->password));
    }

    public function test_changing_the_password_requires_the_current_one(): void
    {
        $this->actingAs($this->user)->put('/password', [
            'current_password' => 'WrongPassword',
            'password' => 'NewPassword@456',
            'password_confirmation' => 'NewPassword@456',
        ])->assertSessionHasErrors('current_password');

        $this->assertTrue(Hash::check('Password@123', $this->user->fresh()->password));
    }

    public function test_the_new_password_must_be_confirmed_and_long_enough(): void
    {
        $this->actingAs($this->user)->put('/password', [
            'current_password' => 'Password@123',
            'password' => 'short',
            'password_confirmation' => 'short',
        ])->assertSessionHasErrors('password');

        $this->actingAs($this->user)->put('/password', [
            'current_password' => 'Password@123',
            'password' => 'NewPassword@456',
            'password_confirmation' => 'Mismatch@456',
        ])->assertSessionHasErrors('password');

        $this->assertTrue(Hash::check('Password@123', $this->user->fresh()->password));
    }

    // ---------------- Account closure ----------------

    public function test_a_user_can_close_their_own_account(): void
    {
        $this->actingAs($this->user)->delete('/profile', ['password' => 'Password@123'])
            ->assertRedirect(route('home'));

        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['id' => $this->user->id]);
    }

    public function test_closing_an_account_requires_the_password(): void
    {
        $this->actingAs($this->user)->delete('/profile', ['password' => 'WrongPassword'])
            ->assertSessionHasErrors('password');

        $this->assertDatabaseHas('users', ['id' => $this->user->id]);
    }

    public function test_the_administrator_account_cannot_be_self_deleted(): void
    {
        $admin = User::factory()->admin()->create(['password' => Hash::make('Password@123')]);

        $this->actingAs($admin)->delete('/profile', ['password' => 'Password@123']);

        $this->assertDatabaseHas('users', ['id' => $admin->id]);
        $this->assertSame(1, User::query()->admins()->count());
    }
}
