<?php

namespace Database\Factories;

use App\Models\Language;
use App\Models\SpecificationValue;
use App\Models\SpecificationValueTranslation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SpecificationValueTranslation>
 */
class SpecificationValueTranslationFactory extends Factory
{
    protected $model = SpecificationValueTranslation::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'specification_value_id' => SpecificationValue::factory(),
            'language_id' => Language::factory(),
            'name' => fake()->word(),
        ];
    }
}
