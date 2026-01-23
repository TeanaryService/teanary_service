<?php

namespace Database\Factories;

use App\Models\Language;
use App\Models\Specification;
use App\Models\SpecificationTranslation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SpecificationTranslation>
 */
class SpecificationTranslationFactory extends Factory
{
    protected $model = SpecificationTranslation::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'specification_id' => Specification::factory(),
            'language_id' => Language::factory(),
            'name' => fake()->words(2, true),
        ];
    }
}
