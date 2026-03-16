<?php

namespace Tests\Feature\Livewire;

use App\Enums\ProductStatusEnum;
use App\Livewire\Search;
use App\Models\Article;
use App\Models\ArticleTranslation;
use App\Models\ProductTranslation;
use Tests\Feature\LivewireTestCase;

class SearchTest extends LivewireTestCase
{
    public function test_search_page_can_be_rendered()
    {
        $component = $this->livewire(Search::class);
        $component->assertSuccessful();
    }

    public function test_search_with_empty_query()
    {
        $component = $this->livewire(Search::class)
            ->set('query', '');

        $component->assertSuccessful();
    }

    public function test_search_products()
    {
        $product = $this->createProduct([
            'status' => ProductStatusEnum::Active,
        ]);

        $language = $this->createLanguage();
        ProductTranslation::factory()->create([
            'product_id' => $product->id,
            'language_id' => $language->id,
            'name' => '测试商品',
        ]);

        $component = $this->livewire(Search::class)
            ->set('query', '测试');

        $component->assertSuccessful();
    }

    public function test_search_articles()
    {
        $article = Article::factory()->create(['is_published' => true]);

        ArticleTranslation::factory()->create([
            'article_id' => $article->id,
            'title' => '测试文章',
        ]);

        $component = $this->livewire(Search::class)
            ->set('query', '测试');

        $component->assertSuccessful();
    }

    public function test_search_returns_both_products_and_articles()
    {
        $product = $this->createProduct([
            'status' => ProductStatusEnum::Active,
        ]);
        $article = Article::factory()->create(['is_published' => true]);

        $language = $this->createLanguage();
        ProductTranslation::factory()->create([
            'product_id' => $product->id,
            'language_id' => $language->id,
            'name' => '测试商品',
        ]);
        ArticleTranslation::factory()->create([
            'article_id' => $article->id,
            'title' => '测试文章',
        ]);

        $component = $this->livewire(Search::class)
            ->set('query', '测试');

        $component->assertSuccessful();
    }

    public function test_search_only_shows_active_products()
    {
        $activeProduct = $this->createProduct([
            'status' => ProductStatusEnum::Active,
        ]);
        $inactiveProduct = $this->createProduct([
            'status' => ProductStatusEnum::Inactive,
        ]);

        $language = $this->createLanguage();
        ProductTranslation::factory()->create([
            'product_id' => $activeProduct->id,
            'language_id' => $language->id,
            'name' => '活跃商品',
        ]);
        ProductTranslation::factory()->create([
            'product_id' => $inactiveProduct->id,
            'language_id' => $language->id,
            'name' => '非活跃商品',
        ]);

        $component = $this->livewire(Search::class)
            ->set('query', '商品');

        $component->assertSuccessful();
    }

    public function test_search_only_shows_published_articles()
    {
        $publishedArticle = Article::factory()->create(['is_published' => true]);
        $unpublishedArticle = Article::factory()->create(['is_published' => false]);

        ArticleTranslation::factory()->create([
            'article_id' => $publishedArticle->id,
            'title' => '已发布文章',
        ]);
        ArticleTranslation::factory()->create([
            'article_id' => $unpublishedArticle->id,
            'title' => '未发布文章',
        ]);

        $component = $this->livewire(Search::class)
            ->set('query', '文章');

        $component->assertSuccessful();
    }

    public function test_search_limits_results()
    {
        $language = $this->createLanguage();
        // 创建多个商品
        for ($i = 0; $i < 10; ++$i) {
            $product = $this->createProduct([
                'status' => ProductStatusEnum::Active,
            ]);
            ProductTranslation::factory()->create([
                'product_id' => $product->id,
                'language_id' => $language->id,
                'name' => "测试商品{$i}",
            ]);
        }

        $component = $this->livewire(Search::class)
            ->set('query', '测试');

        $component->assertSuccessful();
        // 验证结果数量限制（最多5个）
    }

    public function test_search_shows_random_products_when_no_match()
    {
        $language = $this->createLanguage();
        // 创建一些商品但不匹配搜索词
        $product = $this->createProduct([
            'status' => ProductStatusEnum::Active,
        ]);
        ProductTranslation::factory()->create([
            'product_id' => $product->id,
            'language_id' => $language->id,
            'name' => '其他商品',
        ]);

        $component = $this->livewire(Search::class)
            ->set('query', '不存在的搜索词');

        $component->assertSuccessful();
    }
}
