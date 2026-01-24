<?php

namespace Database\Factories;

use App\Enums\TranslationStatusEnum;
use App\Models\Attribute;
use App\Models\AttributeValue;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AttributeValue>
 */
class AttributeValueFactory extends Factory
{
    protected $model = AttributeValue::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'attribute_id' => Attribute::factory(),
            'translation_status' => TranslationStatusEnum::NotTranslated,
        ];
    }

    /**
     * Indicate that the attribute value has complete translations.
     */
    public function complete(): static
    {
        return $this->state(fn (array $attributes) => [
            'translation_status' => TranslationStatusEnum::Translated,
        ]);
    }
}
