<?php

namespace Database\Factories;

use App\Models\Article;
use App\Models\ArticleTranslation;
use App\Models\Language;
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
            'article_id' => Article::factory(),
            'language_id' => Language::factory(),
            'title' => $this->faker->sentence(),
            'summary' => $this->faker->sentence(),
            'content' => '<p>'.implode('</p><p>', $this->faker->paragraphs(3)).'</p>',
        ];
    }
}
