<?php

namespace Database\Factories;

use App\Models\Language;
use App\Models\Promotion;
use App\Models\PromotionTranslation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PromotionTranslation>
 */
class PromotionTranslationFactory extends Factory
{
    protected $model = PromotionTranslation::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'promotion_id' => Promotion::factory(),
            'language_id' => Language::factory(),
            'name' => fake()->words(3, true),
            'description' => fake()->paragraph(),
        ];
    }
}
