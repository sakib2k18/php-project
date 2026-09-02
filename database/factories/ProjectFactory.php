<?php

namespace Database\Factories;

use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Project>
 */
class ProjectFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = Str::title(fake()->words(4, true));
        $start = fake()->dateTimeBetween('-3 years', '-1 month');

        return [
            'title' => $title,
            'slug' => Str::slug($title).'-'.fake()->unique()->numberBetween(1, 99999),
            'summary' => fake()->sentence(18),
            'description' => collect(fake()->paragraphs(4))->map(fn ($p) => "<p>{$p}</p>")->implode("\n"),
            'category' => fake()->randomElement(array_keys(config('site.campaign_categories'))),
            'location' => fake()->city().', Bangladesh',
            'latitude' => fake()->latitude(21.5, 25.5),
            'longitude' => fake()->longitude(88.5, 92.5),
            'start_date' => $start,
            'end_date' => fake()->dateTimeBetween($start, 'now'),
            'status' => Project::STATUS_COMPLETED,
            'featured' => false,
            'is_published' => true,
            'beneficiaries_count' => fake()->numberBetween(80, 6000),
        ];
    }

    public function ongoing(): static
    {
        return $this->state(fn () => ['status' => Project::STATUS_ONGOING, 'end_date' => null]);
    }

    public function unpublished(): static
    {
        return $this->state(fn () => ['is_published' => false]);
    }
}
