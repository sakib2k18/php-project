<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Creates the single KUET TRY administrator.
 *
 * There is no admin registration page anywhere in the application: this seeder
 * is the only way an account with role='admin' comes into existence.
 */
class AdminSeeder extends Seeder
{
    public const EMAIL = 'admin@kuettry.org';

    public const PASSWORD = 'Admin@12345';

    public function run(): void
    {
        $admin = User::withoutEvents(fn () => User::updateOrCreate(
            ['email' => self::EMAIL],
            [
                'name' => 'KUET TRY Administrator',
                // Hashed with bcrypt via the Hash facade — never stored in plain text.
                'password' => Hash::make(self::PASSWORD),
                'role' => User::ROLE_ADMIN,
                'is_active' => true,
                'email_verified_at' => now(),
                'phone' => '+880 1700-000000',
                'address' => 'KUET Campus, Fulbarigate, Khulna 9203',
                'bio' => 'Manages campaigns, verifies donations and keeps the KUET TRY website up to date.',
            ]
        ));

        // Enforce the "exactly one administrator" rule: demote anything else.
        User::query()
            ->where('role', User::ROLE_ADMIN)
            ->where('id', '!=', $admin->id)
            ->update(['role' => User::ROLE_USER]);

        $this->command?->info('Administrator ready: '.self::EMAIL.' / '.self::PASSWORD);
    }
}
