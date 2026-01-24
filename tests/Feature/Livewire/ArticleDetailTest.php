<?php

namespace Tests\Feature\Livewire;

use App\Models\Article;
use App\Models\ArticleTranslation;
use Tests\Feature\LivewireTestCase;

class ArticleDetailTest extends LivewireTestCase
{
    public function test_article_detail_page_can_be_rendered()
    {
        $article = Article::factory()->create([
            'slug' => 'test-article',
            'is_published' => true,
        ]);

        $component = $this->livewire(\App\Livewire\ArticleDetail::class, ['slug' => 'test-article']);
        $component->assertSuccessful();
    }

    public function test_article_detail_displays_article_information()
    {
        $article = Article::factory()->create([
            'slug' => 'test-article',
            'is_published' => true,
        ]);

        ArticleTranslation::factory()->create([
            'article_id' => $article->id,
            'title' => '测试文章',
            'content' => '文章内容',
        ]);

        $component = $this->livewire(\App\Livewire\ArticleDetail::class, ['slug' => 'test-article']);
        $component->assertSuccessful();
        $this->assertEquals($article->id, $component->get('article')->id);
    }

    public function test_article_detail_only_shows_published_articles()
    {
        $publishedArticle = Article::factory()->create([
            'slug' => 'published-article',
            'is_published' => true,
        ]);
        $unpublishedArticle = Article::factory()->create([
            'slug' => 'unpublished-article',
            'is_published' => false,
        ]);

        $component = $this->livewire(\App\Livewire\ArticleDetail::class, ['slug' => 'published-article']);
        $component->assertSuccessful();

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
        $this->livewire(\App\Livewire\ArticleDetail::class, ['slug' => 'unpublished-article']);
    }

    public function test_article_detail_handles_invalid_slug()
    {
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
        $this->livewire(\App\Livewire\ArticleDetail::class, ['slug' => 'non-existent-article']);
    }

    public function test_article_detail_loads_translations()
    {
        $article = Article::factory()->create([
            'slug' => 'test-article',
            'is_published' => true,
        ]);

        ArticleTranslation::factory()->create([
            'article_id' => $article->id,
            'title' => '测试文章',
            'summary' => '文章摘要',
            'content' => '文章内容',
        ]);

        $component = $this->livewire(\App\Livewire\ArticleDetail::class, ['slug' => 'test-article']);
        $component->assertSuccessful();

        $article = $component->get('article');
        $this->assertTrue($article->articleTranslations->isNotEmpty());
    }

    public function test_article_detail_loads_media()
    {
        $article = Article::factory()->create([
            'slug' => 'test-article',
            'is_published' => true,
        ]);

        $component = $this->livewire(\App\Livewire\ArticleDetail::class, ['slug' => 'test-article']);
        $component->assertSuccessful();
    }
}
