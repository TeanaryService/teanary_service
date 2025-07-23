<?php

namespace Database\Factories;

use App\Models\ArticleTranslation;
use App\Services\LocaleCurrencyService;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Article>
 */
class ArticleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            //
            'slug' => Str::random(10),
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function ($article) {
            //封面
            $image = generateRandomImage();

            // 添加到媒体库（collection 名要与你注册的一致）
            $article->addMedia($image)
                ->preservingOriginal()
                ->toMediaCollection('image');

            // 多语言翻译
            $languages = app(LocaleCurrencyService::class)->getLanguages();
            foreach ($languages as $locale) {
                ArticleTranslation::factory()->create([
                    'article_id' => $article->id,
                    'language_id' => $locale->id,
                ]);
            }
        });
    }
}
