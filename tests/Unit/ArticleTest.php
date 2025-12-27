<?php

namespace Tests\Unit;

use App\Models\Article;
use App\Models\ArticleTranslation;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArticleTest extends TestCase
{
    use RefreshDatabase; // 确保每次测试后数据库状态干净

    /**
     * Test if an Article instance can be created using the factory.
     */
    public function test_article_can_be_created_using_factory()
    {
        $article = Article::factory()->create();

        $this->assertNotNull($article);
        $this->assertInstanceOf(Article::class, $article);
        $this->assertIsString($article->slug);
        $this->assertIsBool($article->is_published);
        $this->assertNotNull($article->user_id);
    }

    /**
     * Test the 'is_published' attribute casting.
     */
    public function test_is_published_attribute_casting()
    {
        $article = Article::factory()->create([
            'is_published' => true,
        ]);

        $this->assertIsBool($article->is_published);
        $this->assertTrue($article->is_published);

        $article->is_published = false;
        $this->assertFalse($article->is_published);
    }

    /**
     * Test the 'user' relationship.
     */
    public function test_user_relationship()
    {
        $article = new Article;
        $relation = $article->user();

        $this->assertInstanceOf(BelongsTo::class, $relation);
        $this->assertEquals('user_id', $relation->getForeignKeyName());
        $this->assertEquals('id', $relation->getOwnerKeyName());
    }

    /**
     * Test the 'articleTranslations' relationship.
     */
    public function test_article_translations_relationship()
    {
        $article = new Article;
        $relation = $article->articleTranslations();

        $this->assertInstanceOf(HasMany::class, $relation);
        $this->assertEquals('article_id', $relation->getForeignKeyName());
    }

    /**
     * Test the toSearchableArray method.
     */
    public function test_to_searchable_array()
    {
        // 创建一个文章实例
        $article = Article::factory()->create(['slug' => 'test-article']);

        // 创建并关联翻译
        $translation1 = new ArticleTranslation(['title' => 'Article Title 1', 'content' => 'Article Content 1']);
        $translation2 = new ArticleTranslation(['title' => 'Article Title 2', 'content' => 'Article Content 2']);

        // 模拟 articleTranslations 关系
        $article->setRelation('articleTranslations', Collection::make([$translation1, $translation2]));

        $searchableArray = $article->toSearchableArray();

        $this->assertIsArray($searchableArray);
        $this->assertArrayHasKey('slug', $searchableArray);
        $this->assertArrayHasKey('content', $searchableArray);
        $this->assertEquals('test-article', $searchableArray['slug']);
        $this->assertStringContainsString('Article Title 1', $searchableArray['content']);
        $this->assertStringContainsString('Article Content 1', $searchableArray['content']);
        $this->assertStringContainsString('Article Title 2', $searchableArray['content']);
        $this->assertStringContainsString('Article Content 2', $searchableArray['content']);
    }
}
