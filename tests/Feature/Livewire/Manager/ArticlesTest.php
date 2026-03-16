<?php

namespace Tests\Feature\Livewire\Manager;

use App\Enums\TranslationStatusEnum;
use App\Livewire\Manager\Articles;
use App\Models\Article;
use App\Models\ArticleTranslation;
use Tests\Feature\LivewireTestCase;

class ArticlesTest extends LivewireTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs($this->createManager(), 'manager');
    }

    public function test_articles_page_can_be_rendered()
    {
        $component = $this->livewire(Articles::class);
        $component->assertSuccessful();
    }

    public function test_articles_list_displays_articles()
    {
        $article = Article::factory()->create();

        $component = $this->livewire(Articles::class);

        $articles = $component->get('articles');
        $articleIds = $articles->pluck('id')->toArray();
        $this->assertContains($article->id, $articleIds);
    }

    public function test_can_search_articles_by_title()
    {
        $article1 = Article::factory()->create();
        $article2 = Article::factory()->create();

        ArticleTranslation::factory()->create([
            'article_id' => $article1->id,
            'title' => '测试文章1',
        ]);
        ArticleTranslation::factory()->create([
            'article_id' => $article2->id,
            'title' => '其他文章',
        ]);

        $component = $this->livewire(Articles::class)
            ->set('search', '测试')
            ->assertSet('search', '测试');

        $articles = $component->get('articles');
        $articleIds = $articles->pluck('id')->toArray();
        $this->assertContains($article1->id, $articleIds);
        $this->assertNotContains($article2->id, $articleIds);
    }

    public function test_can_search_articles_by_summary()
    {
        $article1 = Article::factory()->create();
        $article2 = Article::factory()->create();

        ArticleTranslation::factory()->create([
            'article_id' => $article1->id,
            'title' => '文章1',
            'summary' => '这是测试摘要',
        ]);
        ArticleTranslation::factory()->create([
            'article_id' => $article2->id,
            'title' => '文章2',
            'summary' => '其他摘要',
        ]);

        $component = $this->livewire(Articles::class)
            ->set('search', '测试摘要');

        $articles = $component->get('articles');
        $articleIds = $articles->pluck('id')->toArray();
        $this->assertContains($article1->id, $articleIds);
        $this->assertNotContains($article2->id, $articleIds);
    }

    public function test_can_filter_articles_by_publish_status()
    {
        $publishedArticle = Article::factory()->create(['is_published' => true]);
        $unpublishedArticle = Article::factory()->create(['is_published' => false]);

        $component = $this->livewire(Articles::class)
            ->set('filterIsPublished', '1');

        $articles = $component->get('articles');
        $articleIds = $articles->pluck('id')->toArray();
        $this->assertContains($publishedArticle->id, $articleIds);
        $this->assertNotContains($unpublishedArticle->id, $articleIds);
    }

    public function test_can_filter_articles_by_translation_status()
    {
        $completeArticle = Article::factory()->create([
            'translation_status' => TranslationStatusEnum::Translated,
        ]);
        $incompleteArticle = Article::factory()->create([
            'translation_status' => TranslationStatusEnum::NotTranslated,
        ]);

        $component = $this->livewire(Articles::class)
            ->set('filterTranslationStatus', [TranslationStatusEnum::Translated->value]);

        $articles = $component->get('articles');
        $articleIds = $articles->pluck('id')->toArray();
        $this->assertContains($completeArticle->id, $articleIds);
        $this->assertNotContains($incompleteArticle->id, $articleIds);
    }

    public function test_can_delete_article()
    {
        $article = Article::factory()->create();

        $component = $this->livewire(Articles::class)
            ->call('deleteArticle', $article->id);

        $this->assertDatabaseMissing('articles', ['id' => $article->id]);
    }

    public function test_can_toggle_publish_status()
    {
        $article = Article::factory()->create(['is_published' => false]);

        $component = $this->livewire(Articles::class)
            ->call('togglePublish', $article->id);

        $article->refresh();
        $this->assertTrue($article->is_published);
    }

    public function test_toggle_publish_switches_status()
    {
        $article = Article::factory()->create(['is_published' => true]);

        $component = $this->livewire(Articles::class)
            ->call('togglePublish', $article->id);

        $article->refresh();
        $this->assertFalse($article->is_published);
    }

    public function test_reset_filters_clears_all_filters()
    {
        $component = $this->livewire(Articles::class)
            ->set('search', 'test')
            ->set('filterIsPublished', '1')
            ->set('filterTranslationStatus', [TranslationStatusEnum::Translated->value])
            ->call('resetFilters')
            ->assertSet('search', '')
            ->assertSet('filterIsPublished', '')
            ->assertSet('filterTranslationStatus', []);
    }

    public function test_updating_search_resets_page()
    {
        $component = $this->livewire(Articles::class)
            ->set('search', 'test');

        $component->assertSet('search', 'test');
    }
}
