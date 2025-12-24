<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Address>
 */
class AddressFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'firstname' => fake()->firstName(),
            'lastname' => fake()->lastName(),
            'email' => fake()->safeEmail(),
            'telephone' => fake()->phoneNumber(),
            'company' => fake()->optional()->company(),
            'address_1' => fake()->streetAddress(),
            'address_2' => fake()->optional()->streetAddress(),
            'city' => fake()->city(),
            'postcode' => fake()->postcode(),
            'country_id' => \App\Models\Country::factory(),
            'zone_id' => \App\Models\Zone::factory(),
        ];
    }
}
