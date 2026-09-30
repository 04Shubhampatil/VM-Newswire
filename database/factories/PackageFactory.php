<?php

namespace Database\Factories;

use App\Models\Package;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Package>
 */
class PackageFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);

        return [
            'name' => ucwords($name),
            'brand' => 'AccessWire',
            'short_description' => fake()->sentence(10),
            'full_content' => fake()->paragraph(),
            'distribution_summary' => 'Newswire plus the digital publication network',
            'features' => ['Major media distribution', 'Digital publication network', 'Professional reporting', 'Sample report'],
            'price' => fake()->randomElement([149, 249, 399]),
            'currency' => 'USD',
            'is_active' => true,
            'is_highlighted' => false,
            'display_order' => fake()->numberBetween(1, 50),
        ];
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }
}
