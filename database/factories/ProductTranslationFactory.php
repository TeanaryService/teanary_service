<?php

namespace Database\Factories;

use App\Models\Language;
use App\Models\Product;
use App\Models\ProductTranslation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProductTranslation>
 */
class ProductTranslationFactory extends Factory
{
    protected $model = ProductTranslation::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'language_id' => Language::factory(),
            'name' => fake()->words(3, true),
            'short_description' => fake()->sentence(),
            'description' => fake()->paragraph(),
        ];
    }
}
