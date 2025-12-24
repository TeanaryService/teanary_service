<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Country>
 */
class CountryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'iso_code_2' => fake()->unique()->countryCode(),
            'iso_code_3' => fake()->unique()->countryISOAlpha3(),
            'postcode_required' => false,
            'active' => true,
        ];
    }
}
