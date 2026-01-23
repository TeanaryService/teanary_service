<?php

namespace Database\Factories;

use App\Models\Attribute;
use App\Models\AttributeTranslation;
use App\Models\Language;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AttributeTranslation>
 */
class AttributeTranslationFactory extends Factory
{
    protected $model = AttributeTranslation::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'attribute_id' => Attribute::factory(),
            'language_id' => Language::factory(),
            'name' => fake()->words(2, true),
        ];
    }
}
