<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Language>
 */
class LanguageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => fake()->unique()->randomElement(['en', 'zh', 'fr', 'de', 'es', 'ja', 'ko']),
            'name' => fake()->randomElement(['English', '中文', 'Français', 'Deutsch', 'Español', '日本語', '한국어']),
            'default' => false,
        ];
    }
}
