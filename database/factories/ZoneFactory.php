<?php

namespace Database\Factories;

use App\Models\Country;
use App\Models\Zone;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Zone>
 */
class ZoneFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'country_id' => Country::factory(),
            'code' => fake()->unique()->regexify('[A-Z]{2,3}'),
            'active' => true,
        ];
    }
}
