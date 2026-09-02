<?php

namespace Database\Factories;

use App\Models\SuccessStory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<SuccessStory>
 */
class SuccessStoryFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = Str::title(fake()->words(5, true));

        return [
            'title' => $title,
            'slug' => Str::slug($title).'-'.fake()->unique()->numberBetween(1, 99999),
            'beneficiary_name' => fake()->name(),
            'beneficiary_description' => fake()->sentence(14),
            'story' => collect(fake()->paragraphs(5))->map(fn ($p) => "<p>{$p}</p>")->implode("\n"),
            'location' => fake()->city().', Bangladesh',
            'story_date' => fake()->dateTimeBetween('-2 years', 'now'),
            'is_published' => true,
            'featured' => false,
        ];
    }

    public function featured(): static
    {
        return $this->state(fn () => ['featured' => true]);
    }
}
