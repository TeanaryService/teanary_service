<?php

namespace Database\Factories;

use App\Enums\TranslationStatusEnum;
use App\Models\Attribute;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Attribute>
 */
class AttributeFactory extends Factory
{
    protected $model = Attribute::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'is_filterable' => false,
            'translation_status' => TranslationStatusEnum::NotTranslated,
        ];
    }

    /**
     * Indicate that the attribute is filterable.
     */
    public function filterable(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_filterable' => true,
        ]);
    }

    /**
     * Indicate that the attribute has complete translations.
     */
    public function complete(): static
    {
        return $this->state(fn (array $attributes) => [
            'translation_status' => TranslationStatusEnum::Translated,
        ]);
    }
}
