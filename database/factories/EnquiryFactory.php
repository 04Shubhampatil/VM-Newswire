<?php

namespace Database\Factories;

use App\Enums\EnquiryStatus;
use App\Models\Enquiry;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Enquiry>
 */
class EnquiryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->e164PhoneNumber(),
            'company' => fake()->company(),
            'country' => 'United States',
            'release_count' => '1',
            'message' => fake()->paragraph(),
            'source_page' => '/contact',
            'status' => EnquiryStatus::New,
        ];
    }
}
