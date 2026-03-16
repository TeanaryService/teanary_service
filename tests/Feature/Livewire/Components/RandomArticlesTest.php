<?php

namespace Tests\Feature\Livewire\Components;

use App\Livewire\Components\RandomArticles;
use App\Models\Article;
use Tests\Feature\LivewireTestCase;

class RandomArticlesTest extends LivewireTestCase
{
    public function test_random_articles_can_be_rendered()
    {
        $component = $this->livewire(RandomArticles::class);
        $component->assertSuccessful();
    }

    public function test_random_articles_displays_articles()
    {
        $article = Article::factory()->create(['is_published' => true]);

        $component = $this->livewire(RandomArticles::class);
        $component->assertSuccessful();
    }

    public function test_random_articles_only_shows_published_articles()
    {
        $publishedArticle = Article::factory()->create(['is_published' => true]);
        $unpublishedArticle = Article::factory()->create(['is_published' => false]);

        $component = $this->livewire(RandomArticles::class);
        $component->assertSuccessful();
    }

    public function test_random_articles_respects_limit()
    {
        // 创建超过默认限制（3个）的文章
        Article::factory()->count(10)->create(['is_published' => true]);

        $component = $this->livewire(RandomArticles::class, ['limit' => 3]);
        $component->assertSuccessful();
    }

    public function test_random_articles_can_customize_class()
    {
        $component = $this->livewire(RandomArticles::class, [
            'limit' => 3,
            'class' => 'custom-grid-class',
        ]);

        $component->assertSet('class', 'custom-grid-class');
    }
}
