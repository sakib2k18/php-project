<?php

namespace Database\Factories;

use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Event>
 */
class EventFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = Str::title(fake()->words(4, true));

        return [
            'title' => $title,
            'slug' => Str::slug($title).'-'.fake()->unique()->numberBetween(1, 99999),
            'description' => collect(fake()->paragraphs(3))->map(fn ($p) => "<p>{$p}</p>")->implode("\n"),
            'event_date' => fake()->dateTimeBetween('now', '+3 months'),
            'start_time' => '09:00',
            'end_time' => '16:00',
            'location' => fake()->streetAddress().', Khulna',
            'latitude' => fake()->latitude(21.5, 25.5),
            'longitude' => fake()->longitude(88.5, 92.5),
            'organizer' => 'KUET TRY',
            'status' => Event::STATUS_PUBLISHED,
            'capacity' => fake()->numberBetween(30, 400),
        ];
    }

    public function past(): static
    {
        return $this->state(fn () => ['event_date' => fake()->dateTimeBetween('-1 year', '-1 week')]);
    }

    public function draft(): static
    {
        return $this->state(fn () => ['status' => Event::STATUS_DRAFT]);
    }
}
