<?php

namespace Database\Factories;

use App\Models\Specification;
use App\Models\SpecificationValue;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SpecificationValue>
 */
class SpecificationValueFactory extends Factory
{
    protected $model = SpecificationValue::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'specification_id' => Specification::factory(),
            'translation_status' => \App\Enums\TranslationStatusEnum::NotTranslated,
        ];
    }

    /**
     * Indicate that the specification value has complete translations.
     */
    public function complete(): static
    {
        return $this->state(fn (array $attributes) => [
            'translation_status' => \App\Enums\TranslationStatusEnum::Translated,
        ]);
    }
}
