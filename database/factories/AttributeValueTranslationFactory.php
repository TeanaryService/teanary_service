<?php

namespace Database\Factories;

use App\Models\AttributeValue;
use App\Models\AttributeValueTranslation;
use App\Models\Language;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AttributeValueTranslation>
 */
class AttributeValueTranslationFactory extends Factory
{
    protected $model = AttributeValueTranslation::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'attribute_value_id' => AttributeValue::factory(),
            'language_id' => Language::factory(),
            'name' => fake()->word(),
        ];
    }
}
