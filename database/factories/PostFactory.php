<?php

namespace Database\Factories;

use App\Models\Post;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Post>
 */
class PostFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = Str::title(fake()->words(6, true));

        return [
            'title' => $title,
            'slug' => Str::slug($title).'-'.fake()->unique()->numberBetween(1, 99999),
            'excerpt' => fake()->sentence(20),
            'content' => collect(fake()->paragraphs(6))->map(fn ($p) => "<p>{$p}</p>")->implode("\n"),
            'category' => fake()->randomElement(array_keys(config('site.post_categories'))),
            'author' => fake()->name(),
            'status' => Post::STATUS_PUBLISHED,
            'featured' => false,
            'published_at' => fake()->dateTimeBetween('-1 year', 'now'),
            'views' => fake()->numberBetween(0, 900),
        ];
    }

    public function draft(): static
    {
        return $this->state(fn () => ['status' => Post::STATUS_DRAFT, 'published_at' => null]);
    }

    public function featured(): static
    {
        return $this->state(fn () => ['featured' => true]);
    }
}
