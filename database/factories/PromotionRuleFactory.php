<?php

namespace Database\Factories;

use App\Enums\PromotionConditionTypeEnum;
use App\Enums\PromotionDiscountTypeEnum;
use App\Models\Promotion;
use App\Models\PromotionRule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PromotionRule>
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
            'promotion_id' => Promotion::factory(),
            'condition_type' => PromotionConditionTypeEnum::OrderTotalMin,
            'condition_value' => 100.0,
            'discount_type' => PromotionDiscountTypeEnum::Fixed,
            'discount_value' => 10.0,
        ];
    }
}
