<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public const DEMO_PASSWORD = 'Password@123';

    public function run(): void
    {
        // A predictable demo supporter for marking the project.
        User::updateOrCreate(
            ['email' => 'supporter@kuettry.org'],
            [
                'name' => 'Nusrat Jahan',
                'password' => Hash::make(self::DEMO_PASSWORD),
                'role' => User::ROLE_USER,
                'is_active' => true,
                'email_verified_at' => now(),
                'phone' => '+880 1711-223344',
                'student_id' => '1807042',
                'address' => 'Hall Road, Khulna 9100',
                'bio' => 'Regular donor and weekend volunteer with KUET TRY.',
            ]
        );

        $names = [
            'Tanvir Ahmed', 'Sadia Islam', 'Rakibul Hasan', 'Mehjabin Chowdhury',
            'Arif Mahmud', 'Farhana Akter', 'Shakil Rahman', 'Ishrat Binte Kabir',
            'Nayeem Uddin', 'Jarin Tasnim', 'Mahmudul Karim', 'Sumaiya Haque',
            'Rezaul Karim', 'Tahmina Sultana',
        ];

        foreach ($names as $index => $name) {
            $slug = str($name)->lower()->replace(' ', '.')->toString();

            User::updateOrCreate(
                ['email' => "{$slug}@example.com"],
                [
                    'name' => $name,
                    'password' => Hash::make(self::DEMO_PASSWORD),
                    'role' => User::ROLE_USER,
                    // One deactivated account so the admin screen has something to show.
                    'is_active' => $index !== 13,
                    'email_verified_at' => now()->subDays($index * 3),
                    'phone' => '+8801'.str_pad((string) (700000000 + $index * 137), 9, '0', STR_PAD_LEFT),
                    'student_id' => $index % 2 === 0 ? '18070'.str_pad((string) (40 + $index), 2, '0', STR_PAD_LEFT) : null,
                    'address' => 'Khulna, Bangladesh',
                    'created_at' => now()->subMonths(6)->addDays($index * 12),
                ]
            );
        }
    }
}
