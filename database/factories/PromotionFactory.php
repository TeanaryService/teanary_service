<?php

namespace Database\Factories;

use App\Enums\PromotionTypeEnum;
use App\Models\Promotion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Promotion>
 */
class PromotionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => PromotionTypeEnum::Automatic,
            'active' => true,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDays(30),
        ];
    }
}
