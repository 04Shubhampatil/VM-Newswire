<?php

namespace Database\Factories;

use App\Models\MediaOutlet;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MediaOutlet>
 */
class MediaOutletFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => 'Outlet '.fake()->unique()->numerify('#####'),
            'website_url' => fake()->url(),
            'category' => fake()->randomElement(config('vmnewswire.media_categories')),
            'is_active' => true,
            'is_highlighted' => false,
            'display_order' => 0,
        ];
    }
}
