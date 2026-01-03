<?php

namespace Database\Factories;

use App\Models\ArticleTranslation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ArticleTranslation>
 */
class ArticleTranslationFactory extends Factory
{
    protected $model = ArticleTranslation::class;

    public function definition(): array
    {
        return [
            'article_id' => \App\Models\Article::factory(),
            'language_id' => \App\Models\Language::factory(),
            'title' => $this->faker->sentence(),
            'summary' => $this->faker->sentence(),
            'content' => '<p>'.implode('</p><p>', $this->faker->paragraphs(3)).'</p>',
        ];
    }
}
