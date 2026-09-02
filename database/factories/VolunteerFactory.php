<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Volunteer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Volunteer>
 */
class VolunteerFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => '+8801'.fake()->numerify('#########'),
            'student_id' => fake()->numerify('19######'),
            'institution' => 'Khulna University of Engineering & Technology',
            'address' => fake()->streetAddress().', Khulna',
            'skills' => fake()->randomElement([
                'Event management, photography, first aid',
                'Teaching, curriculum design, mentoring',
                'Logistics, driving, warehouse handling',
                'Fundraising, social media, content writing',
            ]),
            'availability' => fake()->randomElement(array_keys(config('site.volunteer_availability'))),
            'preferred_activity' => fake()->randomElement([
                'Relief distribution', 'Fundraising', 'Teaching & tutoring',
                'Medical camp support', 'Event management',
            ]),
            'motivation' => fake()->paragraph(4),
            'status' => Volunteer::STATUS_PENDING,
        ];
    }

    public function approved(): static
    {
        return $this->state(fn () => ['status' => Volunteer::STATUS_APPROVED, 'reviewed_at' => now()]);
    }

    public function rejected(): static
    {
        return $this->state(fn () => ['status' => Volunteer::STATUS_REJECTED, 'reviewed_at' => now()]);
    }
}
