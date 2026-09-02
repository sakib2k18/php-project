<?php

namespace Database\Factories;

use App\Models\Campaign;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Campaign>
 */
class CampaignFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = Str::title(fake()->words(4, true));
        $start = fake()->dateTimeBetween('-8 months', 'now');

        return [
            'title' => $title,
            'slug' => Str::slug($title).'-'.fake()->unique()->numberBetween(1, 99999),
            'short_description' => fake()->sentence(18),
            'description' => collect(fake()->paragraphs(4))->map(fn ($p) => "<p>{$p}</p>")->implode("\n"),
            'category' => fake()->randomElement(array_keys(config('site.campaign_categories'))),
            'target_amount' => fake()->numberBetween(50, 900) * 1000,
            'raised_amount' => 0,
            'start_date' => $start,
            'end_date' => fake()->dateTimeBetween($start, '+6 months'),
            'location' => fake()->randomElement(['Khulna', 'Satkhira', 'Bagerhat', 'Jashore', 'Cox\'s Bazar']).', Bangladesh',
            'latitude' => fake()->latitude(21.5, 25.5),
            'longitude' => fake()->longitude(88.5, 92.5),
            'status' => Campaign::STATUS_ACTIVE,
            'featured' => false,
            'is_emergency' => false,
            'beneficiaries_count' => fake()->numberBetween(50, 4000),
        ];
    }

    public function active(): static
    {
        return $this->state(fn () => ['status' => Campaign::STATUS_ACTIVE]);
    }

    public function draft(): static
    {
        return $this->state(fn () => ['status' => Campaign::STATUS_DRAFT]);
    }

    public function completed(): static
    {
        return $this->state(fn () => ['status' => Campaign::STATUS_COMPLETED]);
    }

    public function featured(): static
    {
        return $this->state(fn () => ['featured' => true]);
    }

    public function emergency(): static
    {
        return $this->state(fn () => ['is_emergency' => true, 'status' => Campaign::STATUS_ACTIVE]);
    }
}
