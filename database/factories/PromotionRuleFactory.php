<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PromotionRule>
 */
class PromotionRuleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'promotion_id' => \App\Models\Promotion::factory(),
            'condition_type' => \App\Enums\PromotionConditionTypeEnum::OrderTotalMin,
            'condition_value' => 100.0,
            'discount_type' => \App\Enums\PromotionDiscountTypeEnum::Fixed,
            'discount_value' => 10.0,
        ];
    }
}
