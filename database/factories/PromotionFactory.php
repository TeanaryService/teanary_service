<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Promotion>
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
            'type' => \App\Enums\PromotionTypeEnum::Automatic,
            'active' => true,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDays(30),
        ];
    }
}
