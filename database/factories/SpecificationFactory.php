<?php

namespace Database\Factories;

use App\Enums\TranslationStatusEnum;
use App\Models\Specification;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Specification>
 */
class SpecificationFactory extends Factory
{
    protected $model = Specification::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'translation_status' => TranslationStatusEnum::NotTranslated,
        ];
    }

    /**
     * Indicate that the specification has complete translations.
     */
    public function complete(): static
    {
        return $this->state(fn (array $attributes) => [
            'translation_status' => TranslationStatusEnum::Translated,
        ]);
    }
}
