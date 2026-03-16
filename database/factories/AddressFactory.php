<?php

namespace Database\Factories;

use App\Models\Address;
use App\Models\Country;
use App\Models\User;
use App\Models\Zone;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Address>
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
            'user_id' => User::factory(),
            'firstname' => fake()->firstName(),
            'lastname' => fake()->lastName(),
            'email' => fake()->safeEmail(),
            'telephone' => fake()->phoneNumber(),
            'company' => fake()->optional()->company(),
            'address_1' => fake()->streetAddress(),
            'address_2' => fake()->optional()->streetAddress(),
            'city' => fake()->city(),
            'postcode' => fake()->postcode(),
            'country_id' => Country::factory(),
            'zone_id' => Zone::factory(),
        ];
    }
}
