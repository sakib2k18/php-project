<?php

namespace Database\Factories;

use App\Models\GalleryItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GalleryItem>
 */
class GalleryItemFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(4),
            'caption' => fake()->sentence(12),
            'category' => fake()->randomElement(array_keys(config('site.gallery_categories'))),
            'taken_on' => fake()->dateTimeBetween('-2 years', 'now'),
            'sort_order' => fake()->numberBetween(0, 50),
            'is_published' => true,
        ];
    }
}
