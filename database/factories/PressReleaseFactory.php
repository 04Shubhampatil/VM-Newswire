<?php

namespace Database\Factories;

use App\Models\PressRelease;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PressRelease>
 */
class PressReleaseFactory extends Factory
{
    public function definition(): array
    {
        $title = rtrim($this->faker->unique()->sentence(8), '.');

        return [
            'title' => $title,
            'category' => $this->faker->randomElement(PressRelease::CATEGORIES),
            'author' => $this->faker->name(),
            'excerpt' => $this->faker->sentence(18),
            'body' => "## ".$this->faker->sentence(6)."\n\n".implode("\n\n", $this->faker->paragraphs(4)),
            'image_path' => null,
            'is_published' => true,
            'published_at' => $this->faker->dateTimeBetween('-60 days', 'now'),
        ];
    }

    public function unpublished(): static
    {
        return $this->state(fn () => ['is_published' => false]);
    }

    public function scheduled(): static
    {
        return $this->state(fn () => ['is_published' => true, 'published_at' => now()->addDays(3)]);
    }
}
