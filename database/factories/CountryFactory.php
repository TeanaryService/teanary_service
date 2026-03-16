<?php

namespace Database\Factories;

use App\Models\Country;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Country>
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
