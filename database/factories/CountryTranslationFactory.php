<?php

namespace Database\Factories;

use App\Models\Country;
use App\Models\CountryTranslation;
use App\Models\Language;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CountryTranslation>
 */
class CountryTranslationFactory extends Factory
{
    protected $model = CountryTranslation::class;

    public function definition(): array
    {
        return [
            'country_id' => Country::factory(),
            'language_id' => Language::factory(),
            'name' => $this->faker->country(),
        ];
    }
}
