<?php

namespace Database\Factories;

use App\Models\Announcement;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Announcement>
 */
class AnnouncementFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(6),
            'content' => fake()->paragraph(3),
            'priority' => fake()->randomElement(array_keys(config('site.announcement_priorities'))),
            'status' => Announcement::STATUS_PUBLISHED,
            'published_at' => now()->subDays(fake()->numberBetween(0, 30)),
        ];
    }

    public function urgent(): static
    {
        return $this->state(fn () => ['priority' => 'urgent']);
    }
}
