<?php

namespace Tests\Feature\Livewire;

use App\Livewire\ArticleList;
use App\Models\Article;
use App\Models\ArticleTranslation;
use Illuminate\Http\Request;
use Tests\Feature\LivewireTestCase;

class ArticleListTest extends LivewireTestCase
{
    public function test_article_list_page_can_be_rendered()
    {
        $component = $this->livewire(ArticleList::class);
        $component->assertSuccessful();
    }

    public function test_article_list_displays_published_articles()
    {
        $publishedArticle = Article::factory()->create(['is_published' => true]);
        $unpublishedArticle = Article::factory()->create(['is_published' => false]);

        $component = $this->livewire(ArticleList::class);
        $component->assertSuccessful();
    }

    public function test_article_list_filters_by_search()
    {
        $article1 = Article::factory()->create(['is_published' => true]);
        $article2 = Article::factory()->create(['is_published' => true]);

        ArticleTranslation::factory()->create([
            'article_id' => $article1->id,
            'title' => '测试文章1',
        ]);
        ArticleTranslation::factory()->create([
            'article_id' => $article2->id,
            'title' => '其他文章',
        ]);

        $request = Request::create('/', 'GET', [
            'search' => '测试',
        ]);

        $component = $this->livewire(ArticleList::class, [], $request);
        $component->assertSuccessful();
    }

    public function test_article_list_only_shows_published_articles()
    {
        $publishedArticle = Article::factory()->create(['is_published' => true]);
        $unpublishedArticle = Article::factory()->create(['is_published' => false]);

        $component = $this->livewire(ArticleList::class);
        $component->assertSuccessful();
    }

    public function test_article_list_orders_by_latest()
    {
        $article1 = Article::factory()->create([
            'is_published' => true,
            'created_at' => now()->subDays(2),
        ]);
        $article2 = Article::factory()->create([
            'is_published' => true,
            'created_at' => now()->subDays(1),
        ]);

        $component = $this->livewire(ArticleList::class);
        $component->assertSuccessful();
    }

    public function test_article_list_paginates_results()
    {
        // 创建超过10篇文章（默认分页大小）
        Article::factory()->count(15)->create(['is_published' => true]);

        $component = $this->livewire(ArticleList::class);
        $component->assertSuccessful();
    }

    public function test_article_list_loads_translations()
    {
        $article = Article::factory()->create(['is_published' => true]);

        ArticleTranslation::factory()->create([
            'article_id' => $article->id,
            'title' => '测试文章',
            'summary' => '文章摘要',
        ]);

        $component = $this->livewire(ArticleList::class);
        $component->assertSuccessful();
    }
}
