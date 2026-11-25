<?php

namespace Database\Factories;

use App\Models\Media;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Media>
 */
class MediaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = fake()->randomElement(['Anime', 'Manga', 'Light Novel', 'Drama']);

        return [
            'title' => fake()->unique()->words(3, true),
            'type' => $type,
            'genre' => fake()->randomElement(['Action', 'Drama, Romance', 'Fantasy', 'Sports', 'Sci-Fi']),
            'description' => fake()->sentence(),
            'episodes' => $type === 'Anime' || $type === 'Drama' ? fake()->numberBetween(1, 50) : null,
            'format' => $type === 'Manga' || $type === 'Light Novel' ? 'Digital' : null,
        ];
    }
}
